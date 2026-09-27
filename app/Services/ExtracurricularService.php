<?php

namespace App\Services;

use App\Models\Extracurricular;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMember;
use App\Models\OsisAnnouncement;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ExtracurricularService
{
    /**
     * Ambil katalog ekstrakurikuler aktif untuk mobile.
     */
    public function getEkskulList(?Student $student = null): array
    {
        $ekskuls = Extracurricular::with(['advisor', 'activeMembers'])
            ->where('is_active', true)
            ->get();

        return $ekskuls->map(function ($ekskul) use ($student) {
            return $ekskul->toMobileItemArray($student);
        })->toArray();
    }

    /**
     * Mendaftarkan siswa ke ekstrakurikuler dengan 4 validasi bisnis.
     */
    public function register(Student $student, int $ekskulId): ExtracurricularMember
    {
        return DB::transaction(function () use ($student, $ekskulId) {
            $ekskul = Extracurricular::where('is_active', true)->findOrFail($ekskulId);

            // 1. Validasi periode pendaftaran
            if (! $ekskul->is_registration_open) {
                throw new \Exception('Pendaftaran untuk ekstrakurikuler ini sedang ditutup.');
            }

            // 2. Cegah duplikasi keanggotaan aktif
            $alreadyRegistered = ExtracurricularMember::where('extracurricular_id', $ekskul->id)
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->exists();

            if ($alreadyRegistered) {
                throw new \Exception('Anda sudah terdaftar aktif di ekstrakurikuler ini.');
            }

            // 3. Batasi maksimal 2 ekskul per siswa
            $activeCount = ExtracurricularMember::where('student_id', $student->id)
                ->where('status', 'active')
                ->count();

            if ($activeCount >= 2) {
                throw new \Exception('Anda telah mencapai batas maksimal pendaftaran (maksimal 2 ekstrakurikuler).');
            }

            // 4. Cek ketersediaan kuota slot anggota
            if (! is_null($ekskul->max_members) && $ekskul->activeMembers()->count() >= $ekskul->max_members) {
                throw new \Exception('Pendaftaran gagal, kuota anggota ekstrakurikuler ini telah penuh.');
            }

            return ExtracurricularMember::create([
                'extracurricular_id' => $ekskul->id,
                'student_id' => $student->id,
                'role' => 'anggota',
                'status' => 'active',
                'joined_at' => now(),
            ]);
        });
    }

    /**
     * Mencatat presensi kehadiran latihan ekskul oleh Pembina/Pelatih.
     */
    public function submitAttendance(User $actor, int $ekskulId, string|int $studentId, ?string $qrPayload = null): ExtracurricularAttendance
    {
        $ekskul = Extracurricular::findOrFail($ekskulId);

        // Otorisasi: Siswa dilarang keras submit presensi mandiri
        if ($actor->role === 'student' || $actor->role === 'siswa') {
            throw new AuthorizationException('Hanya pembina atau pelatih ekstrakurikuler yang dapat mencatat presensi.');
        }

        // Jika guru lain yang bukan pembina ekskul ini dan bukan admin
        $isAdmin = in_array($actor->role, ['admin', 'superadmin'], true);
        if (! $isAdmin && $actor->id !== $ekskul->advisor_id) {
            throw new AuthorizationException('Anda bukan pembina yang ditugaskan untuk ekstrakurikuler ini.');
        }

        // Cari siswa berdasarkan ID atau NIS
        $student = Student::where('id', $studentId)
            ->orWhere('nis', $studentId)
            ->first();

        if (! $student && $qrPayload) {
            $student = Student::where('id', $qrPayload)
                ->orWhere('nis', $qrPayload)
                ->first();
        }

        if (! $student) {
            throw new \Exception('Data siswa tidak ditemukan.');
        }

        // Cek apakah siswa adalah anggota aktif ekskul ini
        $isMember = ExtracurricularMember::where('extracurricular_id', $ekskul->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        if (! $isMember) {
            throw new \Exception("Siswa ini bukan anggota aktif dari ekstrakurikuler {$ekskul->name}.");
        }

        return ExtracurricularAttendance::updateOrCreate(
            [
                'extracurricular_id' => $ekskul->id,
                'date' => now()->toDateString(),
                'student_id' => $student->id,
            ],
            [
                'status' => 'H',
                'notes' => 'Presensi latihan mobile'.($qrPayload ? " (QR: {$qrPayload})" : ''),
            ]
        );
    }

    /**
     * Ambil feed pengumuman OSIS.
     */
    public function getOsisFeed(?int $limit = 20, bool $paginated = false)
    {
        $query = OsisAnnouncement::published()
            ->orderBy('published_at', 'desc')
            ->latest();

        if ($paginated) {
            $paginator = $query->paginate($limit ?: 15);
            $paginator->getCollection()->transform(function ($item) {
                return $item->toMobileItemArray();
            });

            return $paginator;
        }

        return $query->limit($limit ?: 20)->get()->map(function ($item) {
            return $item->toMobileItemArray();
        })->toArray();
    }
}
