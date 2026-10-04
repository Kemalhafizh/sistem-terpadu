<?php

namespace App\Services;

use App\Exceptions\AiUnavailableException;
use Exception;
use Illuminate\Support\Facades\Http;

class AiCopilotService
{
    /**
     * Generate an RPP lesson plan (Markdown).
     *
     * 'local_fallback' is an explicit choice: a blank RPP template for the
     * teacher to fill in, labelled as such. Any other provider must produce an
     * AI result or fail with a message. It used to swap in the template
     * silently when the key was missing or the AI failed (pasting the raw
     * exception, which for Gemini contains the API key), so the template
     * looked like an AI answer.
     *
     * @throws AiUnavailableException
     */
    public function generateLessonPlan(array $params, ?string $apiKey = null, ?string $provider = 'local_fallback', ?string $customEndpoint = null): string
    {
        $subject = $params['subject'] ?? 'Mata Pelajaran';
        $topic = $params['topic'] ?? 'Topik Pembelajaran';
        $gradeLevel = $params['grade_level'] ?? 'X';
        $competencies = $params['competencies'] ?? 'Kompetensi Dasar';

        if ($provider === 'local_fallback') {
            return $this->generateLocalFallbackRpp($subject, $topic, $gradeLevel, $competencies);
        }

        if (empty($apiKey) || ! in_array($provider, ['openai', 'gemini'], true)) {
            throw new AiUnavailableException('Pembuatan RPP dengan AI membutuhkan OpenAI atau Gemini beserta API key-nya. Pilih "Templat RPP" untuk kerangka kosong tanpa AI.');
        }

        $school = school_name();

        try {
            $prompt = "Buatlah RPP (Rencana Pelaksanaan Pembelajaran) Kurikulum Merdeka lengkap dengan bahasa Indonesia yang formal, detail, dan rapi untuk:
- Sekolah: {$school}
- Mata Pelajaran: {$subject}
- Kelas/Fase: {$gradeLevel}
- Topik/Materi: {$topic}
- Kompetensi/Tujuan Dasar: {$competencies}

Harus memuat bagian:
1. Identitas RPP
2. Tujuan Pembelajaran
3. Profil Pelajar Pancasila
4. Langkah Pembelajaran (Pendahuluan, Inti, Penutup)
5. Asesmen/Penilaian
6. Sumber Belajar

Keluarkan output dalam format Markdown yang rapi tanpa menyertakan kode pembungkus markdown tambahan.";

            $text = null;
            if ($provider === 'openai') {
                $endpoint = $customEndpoint ?: 'https://api.openai.com/v1/chat/completions';
                $response = Http::withToken($apiKey)->timeout(30)->post($endpoint, [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda adalah asisten AI guru profesional di Indonesia.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ]);
                $text = $response->successful() ? $response->json('choices.0.message.content') : null;
            } else {
                $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ]);
                $text = $response->successful() ? $response->json('candidates.0.content.parts.0.text') : null;
            }
        } catch (Exception $e) {
            // Not $e->getMessage(): for Gemini it can contain the request URL, and the API key is in it.
            throw new AiUnavailableException('Gagal menghubungi layanan AI. Periksa koneksi dan API key lalu coba lagi.', 0, $e);
        }

        if (! $response->successful()) {
            throw new AiUnavailableException("Layanan AI menolak permintaan (HTTP {$response->status()}). Periksa API key lalu coba lagi.");
        }
        if (! is_string($text) || trim($text) === '') {
            throw new AiUnavailableException('Jawaban AI kosong. Coba lagi.');
        }

        return $text;
    }

    /**
     * Generate exam questions (CBT) with an AI provider.
     *
     * There is no offline mode: without a provider there are no questions. The
     * old "local fallback" filled a template with keywords and picked the
     * answer key at random, and those questions could be saved to the question
     * bank and graded as if they were real.
     *
     * @return list<array<string, mixed>>
     *
     * @throws AiUnavailableException
     */
    public function generateExamQuestions(array $params, ?string $apiKey = null, ?string $provider = 'local_fallback', ?string $customEndpoint = null): array
    {
        $materialText = $params['material_text'] ?? 'Materi Pembelajaran';
        $type = $params['type'] ?? 'multiple_choice';
        $count = (int) ($params['count'] ?? 5);
        $difficulty = $params['difficulty'] ?? 'medium';

        if (empty($apiKey) || ! in_array($provider, ['openai', 'gemini'], true)) {
            throw new AiUnavailableException('Pembuatan soal membutuhkan layanan AI. Pilih OpenAI atau Gemini dan isi API key-nya.');
        }

        try {
            $prompt = "Berdasarkan materi ajar berikut:\n\"{$materialText}\"\n\nBuatlah {$count} butir soal ujian dengan tipe '{$type}' (pilihan ganda/essay) dan tingkat kesulitan '{$difficulty}' dalam bahasa Indonesia.
Keluarkan output dalam format JSON array murni tanpa pembungkus tag markdown, dengan struktur skema JSON sebagai berikut:
[
  {
    \"question_text\": \"Pertanyaan soal...\",
    \"type\": \"{$type}\",
    \"options\": {
      \"A\": \"Pilihan A\",
      \"B\": \"Pilihan B\",
      \"C\": \"Pilihan C\",
      \"D\": \"Pilihan D\",
      \"E\": \"Pilihan E\"
    },
    \"correct_answer\": \"Kunci jawaban (misal A jika pilihan ganda, atau kalimat kunci jika essay)\",
    \"explanation\": \"Penjelasan mengapa jawaban tersebut benar...\",
    \"difficulty\": \"{$difficulty}\"
  }
]";

            if ($provider === 'openai') {
                $endpoint = $customEndpoint ?: 'https://api.openai.com/v1/chat/completions';
                $response = Http::withToken($apiKey)->timeout(30)->post($endpoint, [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda adalah asisten CBT pembuat soal ujian dalam format JSON murni.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.5,
                ]);

                if ($response->successful()) {
                    $jsonText = $response->json('choices.0.message.content');
                    // In some cases, response_format json return wrapper object or list
                    $decoded = json_decode($jsonText, true);

                    return $this->validQuestions($decoded['questions'] ?? $decoded);
                }
            } elseif ($provider === 'gemini') {
                $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        'parts' => [
                            ['text' => $prompt."\n\nHarus berformat JSON murni."],
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');
                    // clean markdown markers if gemini returned it
                    $cleaned = preg_replace('/```json|```/', '', $text);

                    return $this->validQuestions(json_decode(trim($cleaned), true));
                }
            }

            throw new AiUnavailableException("Layanan AI menolak permintaan (HTTP {$response->status()}). Periksa API key lalu coba lagi.");
        } catch (AiUnavailableException $e) {
            throw $e;
        } catch (Exception $e) {
            // Not $e->getMessage(): for Gemini it can contain the request URL, and the API key is in it.
            throw new AiUnavailableException('Gagal menghubungi layanan AI. Periksa koneksi dan API key lalu coba lagi.', 0, $e);
        }
    }

    /**
     * Keep only well-formed questions; an answer with none is an error, not an empty success.
     *
     * @return list<array<string, mixed>>
     */
    private function validQuestions(mixed $decoded): array
    {
        $questions = array_values(array_filter(
            is_array($decoded) ? $decoded : [],
            fn ($q) => is_array($q) && filled($q['question_text'] ?? null) && filled($q['correct_answer'] ?? null),
        ));

        if ($questions === []) {
            throw new AiUnavailableException('Jawaban AI tidak berisi soal yang bisa dibaca. Coba lagi.');
        }

        return $questions;
    }

    /**
     * Blank RPP template (no AI), chosen explicitly as 'local_fallback'.
     */
    private function generateLocalFallbackRpp(string $subject, string $topic, string $gradeLevel, string $competencies): string
    {
        $compList = array_map('trim', explode(',', $competencies));
        $compBullets = '';
        foreach ($compList as $comp) {
            $compBullets .= "- Siswa mampu menelaah dan menguasai tentang: {$comp}\n";
        }

        $school = school_name();

        return "# RENCANA PELAKSANAAN PEMBELAJARAN (RPP) KURIKULUM MERDEKA

> **Templat, bukan hasil AI.** Kerangka ini diisi dari topik dan kompetensi yang Anda masukkan. Sesuaikan isinya sebelum disimpan.

## 1. IDENTITAS MODUL
- **Nama Sekolah**: {$school}
- **Mata Pelajaran**: {$subject}
- **Kelas / Fase**: Kelas {$gradeLevel}
- **Topik Utama**: {$topic}
- **Alokasi Waktu**: _(isi sesuai jadwal)_

---

## 2. TUJUAN PEMBELAJARAN
{$compBullets}- Siswa dapat mengidentifikasi masalah kontekstual yang berkaitan dengan {$topic}.
- Siswa dapat mempresentasikan kesimpulan secara mandiri dan bernalar kritis.

---

## 3. PROFIL PELAJAR PANCASILA
- **Beriman & Bertaqwa**: Berdoa sebelum dan sesudah proses pembelajaran secara islami.
- **Bernalar Kritis**: Menganalisis argumen dan memproses informasi secara objektif.
- **Gotong Royong**: Mampu berkolaborasi aktif dalam diskusi kelompok pemecahan masalah.

---

## 4. LANGKAH-LANGKAH PEMBELAJARAN
### A. Kegiatan Pendahuluan
1. Guru membuka kelas dengan salam islami, tadarus pagi singkat, dan berdoa bersama.
2. Guru memeriksa kehadiran siswa dan kesiapan ruang kelas.
3. **Apersepsi**: Guru memberikan pertanyaan pemantik terkait tema **{$topic}** dalam kehidupan sehari-hari.
4. Guru menyampaikan tujuan pembelajaran yang ingin dicapai hari ini.

### B. Kegiatan Inti
1. **Stimulus**: Guru menyajikan data/kasus singkat yang relevan dengan **{$topic}**.
2. **Identifikasi Masalah**: Siswa secara individu mengidentifikasi tantangan utama dari stimulus yang diberikan.
3. **Pengumpulan Data**: Siswa berdiskusi dalam kelompok kecil untuk menelaah aspek kompetensi: *\"{$competencies}\"*.
4. **Pembuktian**: Kelompok merancang model pemecahan masalah sesuai panduan buku ajar.
5. **Menarik Kesimpulan**: Beberapa kelompok mempresentasikan hasil diskusi di depan kelas, ditanggapi kelompok lain.

### C. Kegiatan Penutup
1. Siswa bersama guru melakukan refleksi atas pembelajaran yang telah berlangsung.
2. Guru memberikan apresiasi atas partisipasi aktif siswa selama proses diskusi kelompok.
3. Guru menyimpulkan poin-poin penting dari materi pembelajaran **{$topic}**.
4. Kelas ditutup dengan hamdalah dan doa majelis bersama.

---

## 5. ASESMEN / PENILAIAN
- **Sikap**: Observasi keaktifan diskusi, sopan santun, dan kemandirian.
- **Pengetahuan**: Uji tulis singkat / kuis interaktif di akhir materi.
- **Keterampilan**: Penilaian lembar kerja kelompok (LKK) dan presentasi.

---

## 6. MEDIA & SUMBER BELAJAR
- Buku Paket Guru & Siswa Kurikulum Merdeka Kemendikbudristek.
- Slide presentasi guru.
- Lembar Kerja Kelompok (LKK) Siswa.";
    }
}
