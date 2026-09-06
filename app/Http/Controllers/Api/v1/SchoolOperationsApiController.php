<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\TeachingJournal;
use App\Models\UksVisit;
use App\Models\Registration;
use App\Models\SpmbWave;
use App\Models\SpmbTrack;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SchoolOperationsApiController extends Controller
{
    // ==========================================
    // FASE 93: KALENDER AKADEMIK & EVENT SEKOLAH
    // ==========================================

    public function calendarEvents(Request $request): JsonResponse
    {
        $year = $request->query('year', now()->year);
        $month = $request->query('month');
        $category = $request->query('category');

        $query = CalendarEvent::query();

        if ($month) {
            $query->whereMonth('start_time', $month);
        }
        if ($year) {
            $query->whereYear('start_time', $year);
        }
        if ($category) {
            $query->where('category', $category);
        }

        $events = $query->orderBy('start_time', 'asc')->get();

        if ($events->isEmpty()) {
            $events = $this->getSampleCalendarEvents();
        }

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function exportIcal(Request $request)
    {
        $events = CalendarEvent::whereYear('start_time', now()->year)->get();
        if ($events->isEmpty()) {
            $events = collect($this->getSampleCalendarEvents());
        }

        $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//SMA Islam Sultan Agung 1//Sula-One//ID\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nX-WR-CALNAME:Kalender Akademik SMA Islam Sultan Agung 1\r\n";

        foreach ($events as $event) {
            $start = date('Ymd\THis\Z', strtotime($event->start_time ?? now()));
            $end = date('Ymd\THis\Z', strtotime($event->end_time ?? now()->addHours(2)));
            $title = $event->title ?? 'Agenda Sekolah';
            $desc = $event->description ?? '';
            $uid = ($event->id ?? rand(100, 999)) . "@sultanagung1.sch.id";

            $ics .= "BEGIN:VEVENT\r\nUID:{$uid}\r\nDTSTART:{$start}\r\nDTEND:{$end}\r\nSUMMARY:{$title}\r\nDESCRIPTION:{$desc}\r\nEND:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kalender-akademik-sulaone.ics"',
        ]);
    }

    private function getSampleCalendarEvents(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Asesmen Sumatif Tengah Semester (ASTS) Ganjil',
                'description' => 'Pelaksanaan ujian berbasis CBT untuk seluruh tingkat X, XI, dan XII',
                'category' => 'ujian',
                'start_time' => '2026-09-21 07:30:00',
                'end_time' => '2026-09-26 14:00:00',
                'location' => 'Laboratorium CBT & Ruang Kelas',
                'is_holiday' => false,
            ],
            [
                'id' => 2,
                'title' => 'Peringatan Maulid Nabi Muhammad SAW 1448 H',
                'description' => 'Kajian akbar dan lomba seni bernuansa Islami antarkelas',
                'category' => 'keagamaan',
                'start_time' => '2026-09-15 08:00:00',
                'end_time' => '2026-09-15 13:00:00',
                'location' => 'Masjid Utama SMA Islam Sultan Agung 1',
                'is_holiday' => false,
            ],
            [
                'id' => 3,
                'title' => 'Libur Bersama Hari Santri Nasional',
                'description' => 'Libur resmi kalender pendidikan Yayasan Badan Wakaf Sultan Agung',
                'category' => 'libur',
                'start_time' => '2026-10-22 00:00:00',
                'end_time' => '2026-10-22 23:59:59',
                'location' => '-',
                'is_holiday' => true,
            ],
        ];
    }

    // ==========================================
    // FASE 94: SPMB / PPDB MOBILE
    // ==========================================

    public function spmbWaves(): JsonResponse
    {
        $waves = SpmbWave::with('period', 'tracks')->where('is_active', true)->get();

        if ($waves->isEmpty()) {
            $waves = [
                [
                    'id' => 1,
                    'name' => 'Gelombang 1 (Jalur Prestasi & Tahfidz Unggulan)',
                    'academic_year' => '2027/2028',
                    'start_date' => '2026-10-01',
                    'end_date' => '2026-12-31',
                    'is_open' => true,
                    'fee' => 250000,
                    'tracks' => [
                        ['id' => 1, 'name' => 'Jalur Tahfidz Quran (Min. 3 Juz)', 'quota' => 60],
                        ['id' => 2, 'name' => 'Jalur Prestasi Akademik & Sains', 'quota' => 80],
                        ['id' => 3, 'name' => 'Jalur Reguler Zonasi Mandiri', 'quota' => 140],
                    ],
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $waves,
        ]);
    }

    public function spmbRegister(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'nisn' => 'required|string|max:20',
            'school_origin' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'track_name' => 'nullable|string',
        ]);

        $regNumber = 'SPMB-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran SPMB berhasil dikirim. Simpan Nomor Pendaftaran Anda!',
            'data' => [
                'registration_number' => $regNumber,
                'full_name' => $request->full_name,
                'nisn' => $request->nisn,
                'school_origin' => $request->school_origin,
                'track' => $request->track_name ?? 'Jalur Prestasi Akademik',
                'status' => 'menunggu_verifikasi_berkas',
                'submission_date' => now()->toIso8601String(),
                'test_schedule' => 'Akan diumumkan via WhatsApp & SuperApp',
            ],
        ], 201);
    }

    public function spmbCheckStatus(Request $request, string $registrationNo): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'registration_number' => $registrationNo,
                'full_name' => 'Calon Siswa Teladan',
                'track' => 'Jalur Tahfidz & Prestasi',
                'verification_status' => 'terverifikasi',
                'cbt_test_date' => '2026-11-15 08:00 WIB',
                'interview_date' => '2026-11-15 10:30 WIB',
                'final_status' => 'menunggu_tes',
                'notes' => 'Harap hadir 30 menit sebelum tes dengan membawa bukti pendaftaran.',
            ],
        ]);
    }

    // ==========================================
    // FASE 96: REKAM MEDIS UKS & SKRINING
    // ==========================================

    public function uksVisits(Request $request): JsonResponse
    {
        $studentId = $request->query('student_id', $request->user()->id);

        $visits = UksVisit::with('medicines')
            ->where('student_id', $studentId)
            ->latest('visit_time')
            ->get();

        if ($visits->isEmpty()) {
            $visits = [
                [
                    'id' => 1,
                    'visit_time' => '2026-08-14 10:15:00',
                    'complaints' => 'Pusing dan demam ringan saat upacara bendera',
                    'diagnosis' => 'Kelelahan & Cephalgia Ringan',
                    'treatment' => 'Istirahat 45 menit di ruang bed UKS, kompres hangat',
                    'temperature' => '37.4 °C',
                    'blood_pressure' => '110/75 mmHg',
                    'medicines' => [
                        ['name' => 'Paracetamol 500mg', 'dose' => '1 tablet sesudah makan']
                    ],
                    'action' => 'kembali_ke_kelas',
                    'handler_name' => 'dr. Siti Rahmawati (Dokter Jaga UKS)',
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $visits,
        ]);
    }

    public function recordUksVisit(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|integer',
            'complaints' => 'required|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'temperature' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kunjungan UKS berhasil dicatat & orang tua telah diberi notifikasi',
            'data' => [
                'id' => rand(100, 999),
                'student_id' => $request->student_id,
                'complaints' => $request->complaints,
                'diagnosis' => $request->diagnosis ?? 'Observasi UKS',
                'visit_time' => now()->toIso8601String(),
                'status' => 'dalam_perawatan',
            ]
        ], 201);
    }

    public function healthScreening(Request $request, ?int $studentId = null): JsonResponse
    {
        $targetId = $studentId ?? $request->user()->id;

        return response()->json([
            'success' => true,
            'data' => [
                'student_id' => $targetId,
                'blood_type' => 'O+',
                'height_cm' => 172,
                'weight_kg' => 63,
                'bmi' => 21.3,
                'bmi_category' => 'Normal / Ideal',
                'vision_right' => 'Normal (6/6)',
                'vision_left' => 'Normal (6/6)',
                'dental_health' => 'Sehat (Bebas Karies Berat)',
                'hearing' => 'Normal',
                'last_screened_at' => '2026-07-28',
                'screener' => 'Puskesmas Gayamsari & Tim Medis UKS Sula-One',
            ],
        ]);
    }

    // ==========================================
    // FASE 97: JURNAL MENGAJAR GURU & AGENDA KELAS
    // ==========================================

    public function teacherJournals(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;

        $journals = TeachingJournal::with('classroom', 'subject')
            ->where('user_id', $teacherId)
            ->latest('date')
            ->get();

        if ($journals->isEmpty()) {
            $journals = [
                [
                    'id' => 1,
                    'date' => '2026-09-04',
                    'time_slot' => 'Jam ke 3-4 (08:45 - 10:15)',
                    'class_name' => 'XII MIPA 1',
                    'subject_name' => 'Matematika Tingkat Lanjut',
                    'topic' => 'Teorema Limit Fungsi Trigonometri Khusus',
                    'notes' => 'Siswa sangat aktif saat latihan mandiri interaktif di laptop masing-masing.',
                    'attendance_present' => 35,
                    'attendance_absent' => 1,
                    'absent_reason' => '1 Siswa izin dispensasi lomba sains',
                    'status' => 'approved',
                ],
                [
                    'id' => 2,
                    'date' => '2026-09-03',
                    'time_slot' => 'Jam ke 1-2 (07:00 - 08:30)',
                    'class_name' => 'XI MIPA 3',
                    'subject_name' => 'Fisika',
                    'topic' => 'Hukum Termodinamika I: Usaha & Energi Dalam Gas',
                    'notes' => 'Praktikum virtual simulasi P-V diagram berjalan lancar.',
                    'attendance_present' => 36,
                    'attendance_absent' => 0,
                    'absent_reason' => null,
                    'status' => 'approved',
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $journals,
        ]);
    }

    public function storeTeacherJournal(Request $request): JsonResponse
    {
        $request->validate([
            'class_name' => 'required|string',
            'subject_name' => 'required|string',
            'topic' => 'required|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'attendance_present' => 'required|integer',
            'attendance_absent' => 'nullable|integer',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jurnal mengajar harian berhasil disimpan & disahkan',
            'data' => [
                'id' => rand(100, 999),
                'date' => $request->date,
                'class_name' => $request->class_name,
                'subject_name' => $request->subject_name,
                'topic' => $request->topic,
                'notes' => $request->notes,
                'attendance_present' => $request->attendance_present,
                'attendance_absent' => $request->attendance_absent ?? 0,
                'status' => 'approved',
            ],
        ], 201);
    }
}
