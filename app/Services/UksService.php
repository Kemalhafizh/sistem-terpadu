<?php

namespace App\Services;

use App\Models\UksMedicine;
use App\Models\UksVisit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UksService
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Mencatat kunjungan UKS baru
     */
    public function recordVisit(array $visitData, array $medicines = []): UksVisit
    {
        return DB::transaction(function () use ($visitData, $medicines) {
            // 1. Simpan data kunjungan
            $visitData['handled_by'] = auth()->id();
            $filteredData = collect($visitData)->except(['medicines', 'blood_type', 'allergies'])->toArray();
            $visit = UksVisit::create($filteredData);

            // 2. Hubungkan obat dan kurangi stok
            foreach ($medicines as $med) {
                if (empty($med['id']) || empty($med['quantity'])) {
                    continue;
                }

                $medicine = UksMedicine::findOrFail($med['id']);

                // Amankan dari pengurangan stok negatif jika tidak ada proteksi form
                $qty = min($med['quantity'], $medicine->stock);

                if ($qty > 0) {
                    $visit->medicines()->attach($medicine->id, ['quantity' => $qty]);
                    $medicine->decrement('stock', $qty);
                }
            }

            // 3. Picu notifikasi WhatsApp jika darurat (berat / pulang / rujuk)
            $this->checkAndNotifyParent($visit);

            return $visit;
        });
    }

    /**
     * Memperbarui data kunjungan UKS + Penyesuaian stok obat secara transaksional
     */
    public function updateVisit(UksVisit $visit, array $visitData, array $medicines = []): UksVisit
    {
        return DB::transaction(function () use ($visit, $visitData, $medicines) {
            // 1. Kembalikan stok obat lama terlebih dahulu
            foreach ($visit->medicines as $oldMed) {
                $oldMed->increment('stock', $oldMed->pivot->quantity);
            }
            $visit->medicines()->detach();

            // 2. Update data kunjungan utama
            $filteredData = collect($visitData)->except(['medicines', 'blood_type', 'allergies'])->toArray();
            $visit->update($filteredData);

            // 3. Hubungkan obat baru dan kurangi stok baru
            foreach ($medicines as $med) {
                if (empty($med['id']) || empty($med['quantity'])) {
                    continue;
                }

                $medicine = UksMedicine::findOrFail($med['id']);
                $qty = min($med['quantity'], $medicine->stock);

                if ($qty > 0) {
                    $visit->medicines()->attach($medicine->id, ['quantity' => $qty]);
                    $medicine->decrement('stock', $qty);
                }
            }

            // 4. Cek notifikasi ulang jika kondisi berubah menjadi parah
            $this->checkAndNotifyParent($visit);

            return $visit;
        });
    }

    /**
     * Membatalkan/menghapus kunjungan UKS (Kembalikan stok)
     */
    public function deleteVisit(UksVisit $visit): bool
    {
        return DB::transaction(function () use ($visit) {
            foreach ($visit->medicines as $med) {
                $med->increment('stock', $med->pivot->quantity);
            }
            $visit->medicines()->detach();

            return $visit->delete();
        });
    }

    /**
     * Kirim notifikasi darurat via WhatsApp ke orang tua
     */
    public function checkAndNotifyParent(UksVisit $visit): void
    {
        // Kondisi darurat: severity = berat, atau disposisi = pulang / rujuk_rs
        $isEmergency = $visit->severity === 'berat' || in_array($visit->disposition, ['pulang', 'rujuk_rs']);

        if ($isEmergency && ! $visit->parent_notified) {
            $student = $visit->student;
            $parent = $student?->parent;

            if ($parent && $parent->phone_number) {
                $studentName = $student->name;
                $classroom = $student->classroom?->name ?? '-';
                $time = $visit->visit_time->translatedFormat('d F Y H:i');

                // Siapkan pemetaan disposisi ke bahasa manusia
                $dispMapping = [
                    'kembali_kelas' => 'Diperbolehkan kembali ke kelas',
                    'istirahat_uks' => 'Sedang beristirahat di UKS',
                    'pulang' => 'Mohon dijemput untuk dipulangkan ke rumah',
                    'rujuk_rs' => 'Dirujuk ke Rumah Sakit terdekat',
                ];
                $dispositionText = $dispMapping[$visit->disposition] ?? 'Sedang ditangani di UKS';

                // Check if active automation for uks_visit_severe exists
                $automationService = app(\App\Services\WhatsappAutomationService::class);
                $context = [
                    'nama_siswa' => $studentName,
                    'nama_orang_tua' => $parent->name,
                    'kelas' => $classroom,
                    'tanggal' => $time,
                    'keluhan' => $visit->complaint,
                    'diagnosa' => $visit->diagnosis,
                    'penanganan' => $visit->treatment,
                    'gejala' => $visit->severity,
                    'disposisi' => $dispositionText
                ];

                $automation = \App\Models\WhatsappAutomation::where('trigger', 'uks_visit_severe')
                    ->where('is_active', true)
                    ->with('template')
                    ->first();

                if ($automation && $automation->template) {
                    $message = $automationService->parseMessage($automation->template->content, $context);
                } else {
                    $message = " *PEMBERITAHUAN DARURAT UKS SMA ISLAM SULTAN AGUNG 1*\n\n"
                        ."Bapak/Ibu Orang Tua/Wali dari siswa:\n"
                        ."Nama: *{$studentName}*\n"
                        ."Kelas: *{$classroom}*\n\n"
                        ."Menginfokan bahwa putra/putri Bapak/Ibu masuk ke Unit Kesehatan Sekolah (UKS) pada:\n"
                        ."Waktu: {$time} WIB\n"
                        ."Keluhan: *{$visit->complaint}*\n"
                        ."Diagnosis Sementara: {$visit->diagnosis}\n"
                        ."Penanganan: {$visit->treatment}\n"
                        .'Tingkat Gejala: *'.strtoupper($visit->severity)."*\n\n"
                        ."Status Tindakan: *{$dispositionText}*\n\n"
                        ."Mohon segera menghubungi petugas UKS sekolah atau wali kelas untuk koordinasi penanganan lebih lanjut.\n"
                        .'Terima kasih.';
                }

                try {
                    $this->whatsappService->sendMessage($parent->phone_number, $message, 'uks_emergency');
                    $visit->update(['parent_notified' => true]);
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim WhatsApp darurat UKS ke Ortu: '.$e->getMessage());
                }
            }
        }
    }
}
