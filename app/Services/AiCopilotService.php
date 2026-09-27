<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class AiCopilotService
{
    /**
     * Generate RPP Lesson Plan (Markdown format).
     */
    public function generateLessonPlan(array $params, ?string $apiKey = null, ?string $provider = 'local_fallback', ?string $customEndpoint = null): string
    {
        $subject = $params['subject'] ?? 'Mata Pelajaran';
        $topic = $params['topic'] ?? 'Topik Pembelajaran';
        $gradeLevel = $params['grade_level'] ?? 'X';
        $competencies = $params['competencies'] ?? 'Kompetensi Dasar';

        // Check if we should use fallback
        if (empty($apiKey) || $provider === 'local_fallback') {
            return $this->generateLocalFallbackRpp($subject, $topic, $gradeLevel, $competencies);
        }

        try {
            $prompt = "Buatlah RPP (Rencana Pelaksanaan Pembelajaran) Kurikulum Merdeka lengkap dengan bahasa Indonesia yang formal, detail, dan rapi untuk:
- Sekolah: SMA Islam Sultan Agung 1 Semarang
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

                if ($response->successful()) {
                    return $response->json('choices.0.message.content');
                }
            } elseif ($provider === 'gemini') {
                $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    return $response->json('candidates.0.content.parts.0.text');
                }
            }

            // Fallback if API response is not successful
            return $this->generateLocalFallbackRpp($subject, $topic, $gradeLevel, $competencies)."\n\n*(Catatan: Terjadi kendala saat menghubungi API AI, dialihkan ke model template lokal)*";
        } catch (Exception $e) {
            return $this->generateLocalFallbackRpp($subject, $topic, $gradeLevel, $competencies)."\n\n*(Catatan: Gagal memicu AI ({$e->getMessage()}), dialihkan ke model template lokal)*";
        }
    }

    /**
     * Generate Exam Questions (CBT).
     */
    public function generateExamQuestions(array $params, ?string $apiKey = null, ?string $provider = 'local_fallback', ?string $customEndpoint = null): array
    {
        $materialText = $params['material_text'] ?? 'Materi Pembelajaran';
        $type = $params['type'] ?? 'multiple_choice';
        $count = (int) ($params['count'] ?? 5);
        $difficulty = $params['difficulty'] ?? 'medium';

        if (empty($apiKey) || $provider === 'local_fallback') {
            return $this->generateLocalFallbackCbt($materialText, $type, $count, $difficulty);
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

                    return $decoded['questions'] ?? $decoded;
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

                    return json_decode(trim($cleaned), true) ?: [];
                }
            }

            return $this->generateLocalFallbackCbt($materialText, $type, $count, $difficulty);
        } catch (Exception $e) {
            return $this->generateLocalFallbackCbt($materialText, $type, $count, $difficulty);
        }
    }

    /**
     * Local fallback template system for RPP (Offline).
     */
    private function generateLocalFallbackRpp(string $subject, string $topic, string $gradeLevel, string $competencies): string
    {
        $compList = array_map('trim', explode(',', $competencies));
        $compBullets = '';
        foreach ($compList as $comp) {
            $compBullets .= "- Siswa mampu menelaah dan menguasai tentang: {$comp}\n";
        }

        return "# RENCANA PELAKSANAAN PEMBELAJARAN (RPP) KURIKULUM MERDEKA

## 1. IDENTITAS MODUL
- **Nama Sekolah**: SMA Islam Sultan Agung 1 Semarang
- **Mata Pelajaran**: {$subject}
- **Kelas / Fase**: Kelas {$gradeLevel}
- **Topik Utama**: {$topic}
- **Alokasi Waktu**: 2 Jam Pelajaran (2 x 45 Menit)

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
### A. Kegiatan Pendahuluan (15 Menit)
1. Guru membuka kelas dengan salam islami, tadarus pagi singkat, dan berdoa bersama.
2. Guru memeriksa kehadiran siswa dan kesiapan ruang kelas.
3. **Apersepsi**: Guru memberikan pertanyaan pemantik terkait tema **{$topic}** dalam kehidupan sehari-hari.
4. Guru menyampaikan tujuan pembelajaran yang ingin dicapai hari ini.

### B. Kegiatan Inti (60 Menit)
1. **Stimulus**: Guru menyajikan data/kasus singkat yang relevan dengan **{$topic}**.
2. **Identifikasi Masalah**: Siswa secara individu mengidentifikasi tantangan utama dari stimulus yang diberikan.
3. **Pengumpulan Data**: Siswa berdiskusi dalam kelompok kecil untuk menelaah aspek kompetensi: *\"{$competencies}\"*.
4. **Pembuktian**: Kelompok merancang model pemecahan masalah sesuai panduan buku ajar.
5. **Menarik Kesimpulan**: Beberapa kelompok mempresentasikan hasil diskusi di depan kelas, ditanggapi kelompok lain.

### C. Kegiatan Penutup (15 Menit)
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
- Slide Presentasi Proyektor SMA Islam Sultan Agung 1.
- Lembar Kerja Kelompok (LKK) Siswa.";
    }

    /**
     * Local fallback template system for CBT questions (Offline).
     */
    private function generateLocalFallbackCbt(string $materialText, string $type, int $count, string $difficulty): array
    {
        // Extract some terms/keywords from material text for customization
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $materialText);
        $words = array_filter(explode(' ', $cleaned), fn ($w) => strlen($w) > 4);
        $keywords = array_values(array_unique(array_slice($words, 0, 5)));

        if (empty($keywords)) {
            $keywords = ['Konsep Utama', 'Studi Kasus', 'Teori Dasar', 'Analisis Data'];
        }

        $questions = [];

        for ($i = 0; $i < $count; $i++) {
            $keyword = $keywords[$i % count($keywords)];
            $questionNum = $i + 1;

            if ($type === 'essay') {
                $questions[] = [
                    'question_text' => "Berdasarkan materi ajar, uraikan analisis komprehensif Anda mengenai keterkaitan antara {$keyword} dengan fenomena kontekstual di masyarakat!",
                    'type' => 'essay',
                    'options' => null,
                    'correct_answer' => "Kunci analisis: Harus menjelaskan konsep {$keyword} dengan contoh konkrit.",
                    'explanation' => "Menilai kedalaman pemahaman konseptual siswa mengenai {$keyword}.",
                    'difficulty' => $difficulty,
                ];
            } else {
                $correctOption = collect(['A', 'B', 'C', 'D', 'E'])->random();
                $options = [
                    'A' => 'Proses identifikasi dini dari '.$keyword.' secara struktural.',
                    'B' => 'Implementasi taktis tanpa adanya batasan ruang lingkup.',
                    'C' => 'Metodologi evaluasi berkala yang dijalankan secara independen.',
                    'D' => 'Konsep teoretis murni yang tidak memiliki aplikasi praktis.',
                    'E' => 'Sinergi multi-sektoral untuk meningkatkan kualitas output.',
                ];

                // Make the correct option description positive/ideal
                $options[$correctOption] = 'Jawaban Tepat: Alternatif solusi optimal dalam merumuskan penyelesaian '.$keyword.'.';

                $questions[] = [
                    'question_text' => "Manakah di bawah ini pernyataan yang paling tepat menggambarkan implikasi dari penerapan {$keyword} menurut teks materi?",
                    'type' => 'multiple_choice',
                    'options' => $options,
                    'correct_answer' => $correctOption,
                    'explanation' => "Berdasarkan bahan ajar, opsi {$correctOption} mewakili penjelasan logis dari {$keyword}.",
                    'difficulty' => $difficulty,
                ];
            }
        }

        return $questions;
    }
}
