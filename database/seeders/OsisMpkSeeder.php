<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\MpkAudit;
use App\Models\MpkLpj;
use App\Models\OsisCandidate;
use App\Models\OsisElection;
use App\Models\OsisMember;
use App\Models\OsisTransaction;
use App\Models\OsisVote;
use App\Models\OsisWorkProgram;
use App\Models\Student;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OsisMpkSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $academicYear = AcademicYear::current() ?: AcademicYear::first();

        // 1. Get some students to act as OSIS members
        $students = Student::take(20)->get();
        if ($students->isEmpty()) {
            return;
        }

        $positions = [
            'Ketua OSIS', 'Wakil Ketua OSIS', 'Sekretaris I', 'Sekretaris II',
            'Bendahara I', 'Bendahara II', 'Ketua Sekbid Keagamaan',
            'Ketua Sekbid Bela Negara', 'Ketua Sekbid Olahraga',
            'Ketua Sekbid Humas & IT', 'Anggota Sekbid Keagamaan', 'Anggota Sekbid Olahraga',
        ];

        // Create Osis Members
        foreach ($positions as $index => $pos) {
            OsisMember::create([
                'student_id' => $students[$index]->id,
                'position' => $pos,
                'academic_year_id' => $academicYear->id,
                'status' => 'active',
            ]);
        }

        // 2. Create 8 Osis Work Programs
        $programs = [
            ['title' => 'LDKS (Latihan Dasar Kepemimpinan Siswa)', 'sekbid' => 'Bela Negara', 'plan' => 6000000, 'actual' => 5850000, 'status' => 'completed'],
            ['title' => 'PHBI Maulid Nabi Muhammad SAW', 'sekbid' => 'Keagamaan', 'plan' => 4500000, 'actual' => 4500000, 'status' => 'completed'],
            ['title' => 'Class Meeting Semester Ganjil', 'sekbid' => 'Olahraga', 'plan' => 3000000, 'actual' => 2900000, 'status' => 'completed'],
            ['title' => 'SISTA Cup & Pentas Seni Tahunan', 'sekbid' => 'Humas & IT', 'plan' => 15000000, 'actual' => 0, 'status' => 'planned'],
            ['title' => 'Bakti Sosial & Santunan Anak Yatim', 'sekbid' => 'Keagamaan', 'plan' => 5000000, 'actual' => 5200000, 'status' => 'completed'],
            ['title' => 'Penyuluhan Bahaya Narkoba & Seks Bebas', 'sekbid' => 'Bela Negara', 'plan' => 2500000, 'actual' => 2500000, 'status' => 'completed'],
            ['title' => 'Lomba Mading Digital & Poster Sekolah', 'sekbid' => 'Humas & IT', 'plan' => 1500000, 'actual' => 1400000, 'status' => 'completed'],
            ['title' => 'Liga Persahabatan Futsal Antar Kelas', 'sekbid' => 'Olahraga', 'plan' => 2000000, 'actual' => 0, 'status' => 'cancelled'],
        ];

        foreach ($programs as $index => $p) {
            OsisWorkProgram::create([
                'academic_year_id' => $academicYear->id,
                'title' => $p['title'],
                'sekbid' => $p['sekbid'],
                'description' => $faker->paragraph,
                'budget_plan' => $p['plan'],
                'budget_actual' => $p['actual'],
                'planned_date' => now()->subMonths(6 - $index)->toDateString(),
                'actual_date' => $p['actual'] > 0 ? now()->subMonths(6 - $index)->addDays(2)->toDateString() : null,
                'status' => $p['status'],
                'report' => $p['actual'] > 0 ? 'Laporan pertanggungjawaban lengkap program '.$p['title'].'.' : null,
                'photo_paths' => $p['actual'] > 0 ? ['osis/photos/prog_'.$index.'_1.jpg', 'osis/photos/prog_'.$index.'_2.jpg'] : null,
            ]);
        }

        // 3. Create 1 Osis Election
        $election = OsisElection::create([
            'academic_year_id' => $academicYear->id,
            'title' => 'Pemilihan Ketua OSIS Masa Bakti 2026/2027',
            'voting_start' => now()->subDays(5)->toDateString(),
            'voting_end' => now()->subDays(4)->toDateString(),
            'status' => 'closed',
        ]);

        // Create 3 Candidates
        $candidates = collect();
        $candidateData = [
            ['vision' => 'Menjadikan SMA Islam Sultan Agung 1 sebagai sekolah religius, inovatif, dan berwawasan lingkungan.', 'mission' => 'Meningkatkan kualitas ibadah harian siswa, mengadakan pekan inovasi teknologi, dan gerakan zero waste.'],
            ['vision' => 'Menciptakan wadah kolaborasi siswa yang inklusif, kreatif, dan berprestasi di tingkat nasional.', 'mission' => 'Mengadakan kompetisi seni tahunan, mempererat komunikasi OSIS dengan ekstrakurikuler, dan konseling sebaya.'],
            ['vision' => 'Mengembangkan OSIS yang adaptif, berintegritas tinggi, dan responsif terhadap aspirasi siswa.', 'mission' => 'Membuat mading digital interaktif, menyalurkan aspirasi fasilitas berkala, dan pelatihan kepemimpinan umum.'],
        ];

        for ($k = 0; $k < 3; $k++) {
            $candidate = OsisCandidate::create([
                'election_id' => $election->id,
                'student_id' => $students[$k + 12]->id, // select candidate from leftover students
                'candidate_number' => $k + 1,
                'vision' => $candidateData[$k]['vision'],
                'mission' => $candidateData[$k]['mission'],
                'photo_path' => 'osis/candidates/num_'.($k + 1).'.jpg',
                'vote_count' => 0,
            ]);
            $candidates->push($candidate);
        }

        // Seed 100 anonymous votes from other students
        $allStudents = Student::skip(20)->take(120)->get();
        if ($allStudents->isNotEmpty()) {
            $votedCount = 0;
            foreach ($allStudents as $student) {
                if ($votedCount >= 100) {
                    break;
                }

                $chosenCandidate = $faker->randomElement($candidates->all());
                OsisVote::create([
                    'election_id' => $election->id,
                    'voter_id' => $student->id,
                    'candidate_id' => $chosenCandidate->id,
                ]);

                // Increment vote count on candidate
                $chosenCandidate->increment('vote_count');
                $votedCount++;
            }
        }

        // 4. Create Osis Transactions (5 transactions)
        $adminUsers = User::whereIn('role', ['admin', 'superadmin'])->pluck('id');
        $recorderId = $adminUsers->first() ?: 1;

        $txs = [
            ['type' => 'income', 'amount' => 10000000.00, 'desc' => 'Pemberian Dana Hibah Operasional Sekolah', 'cat' => 'Sponsor', 'date' => now()->subMonths(3)->toDateString()],
            ['type' => 'income', 'amount' => 2500000.00, 'desc' => 'Uang Kas Bulanan Anggota OSIS Semester Ganjil', 'cat' => 'Iuran', 'date' => now()->subMonths(2)->toDateString()],
            ['type' => 'expense', 'amount' => 5850000.00, 'desc' => 'Pengeluaran Kegiatan LDKS OSIS', 'cat' => 'Kegiatan', 'date' => now()->subMonths(2)->toDateString()],
            ['type' => 'expense', 'amount' => 1200000.00, 'desc' => 'Pembelian Kertas dan ATK Kantor OSIS', 'cat' => 'Perlengkapan', 'date' => now()->subMonth()->toDateString()],
            ['type' => 'income', 'amount' => 5000000.00, 'desc' => 'Dana Sponsor BUMN untuk Class Meeting', 'cat' => 'Sponsor', 'date' => now()->subDays(15)->toDateString()],
        ];

        $transactions = collect();
        foreach ($txs as $tx) {
            $t = OsisTransaction::create([
                'type' => $tx['type'],
                'amount' => $tx['amount'],
                'description' => $tx['desc'],
                'category' => $tx['cat'],
                'transaction_date' => $tx['date'],
                'receipt_path' => 'osis/receipts/tx_'.$faker->unique()->numerify('#####').'.pdf',
                'recorded_by' => $recorderId,
            ]);
            $transactions->push($t);
        }

        // 5. Seed 3 MpkLpj and MpkAudit records
        $teachers = User::whereIn('role', ['guru', 'kepala_sekolah'])->pluck('id');
        $reviewerId = $teachers->first() ?: 1;

        for ($i = 0; $i < 3; $i++) {
            $lpj = MpkLpj::create([
                'uuid' => (string) Str::uuid(),
                'academic_year_id' => $academicYear->id,
                'title' => 'Laporan Pertanggungjawaban Kegiatan ke-'.($i + 1),
                'description' => $faker->sentence(10),
                'file_path' => 'mpk/lpj/lpj_event_'.($i + 1).'.pdf',
                'status' => 'approved',
                'reviewer_notes' => 'Laporan diterima dengan catatan kelengkapan foto dokumentasi diperbanyak.',
                'submitted_by' => $students[0]->user_id, // submitted by ketua osis
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now()->subMonths(1),
            ]);

            MpkAudit::create([
                'osis_transaction_id' => $transactions[$i]->id,
                'status' => 'verified',
                'notes' => 'Nominal transaksi sesuai dengan lampiran kuitansi fisik.',
                'audited_by' => $reviewerId,
            ]);
        }
    }
}
