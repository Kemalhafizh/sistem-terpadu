<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ForumReaction;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $teachers = User::where('role', 'guru')->get();
        $students = User::where('role', 'student')->get();
        $parents = User::where('role', 'parent')->get();
        $classrooms = Classroom::all();
        $subjects = Subject::all();

        ForumThread::truncate();
        ForumReply::truncate();
        ForumReaction::truncate();

        // 1. Pinned Global Announcements by Admin (2 threads)
        $thread1 = ForumThread::create([
            'title' => 'Tata Tertib Akademik & Protokol Pembelajaran SMA ISA 1 Semarang',
            'body' => "Assalamu'alaikum Wr. Wb. Harap seluruh siswa dan wali murid memperhatikan tata tertib akademik sekolah yang berlaku untuk tahun ajaran ini. Aturan lengkap dapat dilihat di dashboard panduan masing-masing.",
            'user_id' => $admin->id,
            'scope' => 'school',
            'is_pinned' => true,
            'is_locked' => false,
            'is_announcement' => true,
        ]);

        $thread2 = ForumThread::create([
            'title' => 'Panduan Pembayaran SPP Bulanan Menggunakan QRIS & Transfer Bank',
            'body' => 'Bagi orang tua / wali murid yang ingin melakukan pembayaran SPP secara online, silakan gunakan menu tagihan dan checkout melalui Midtrans Snap. Bukti pembayaran akan terbit otomatis.',
            'user_id' => $admin->id,
            'scope' => 'school',
            'is_pinned' => true,
            'is_locked' => true,
            'is_announcement' => true,
        ]);

        // 2. School-wide general discussions by teachers and students (6 threads)
        $generalTopics = [
            ['title' => 'Tips Sukses Lolos UTBK 2026 Mandiri', 'body' => 'Ayo share trik belajar dan link latihan soal UTBK di sini untuk adik-adik kelas X dan XI.'],
            ['title' => 'Klub Robotika & Coding Sultan Agung Dibuka Kembali!', 'body' => 'Bagi yang berminat bergabung di klub robotika, silakan daftar ke Ibu Dwi Rahayu.'],
            ['title' => 'Persiapan Kegiatan Peringatan Hari Besar Islam (PHBI) Maulid Nabi', 'body' => 'Mari panitia OSIS kumpul di aula untuk mendiskusikan konsep acara pentas seni religi.'],
            ['title' => 'Rekomendasi Kitab Tafsir Al-Quran Terbaik untuk Pemula', 'body' => 'Ustadz Zainul mohon rekomendasi kitab tafsir yang mudah dipahami santri tahfidz pemula.'],
            ['title' => 'Pentingnya Menjaga Adab Terhadap Guru di Sekolah', 'body' => 'Adab lebih utama daripada ilmu. Mari kita bahas bagaimana mengaplikasikannya sehari-hari.'],
            ['title' => 'Tanya: Apakah perpustakaan digital SISTA bisa diakses dari luar sekolah?', 'body' => 'Mohon infonya admin, apakah kami bisa download e-book dari rumah? Syukron.'],
        ];

        $allThreads = [$thread1, $thread2];

        foreach ($generalTopics as $topic) {
            $author = rand(1, 100) <= 40 ? $teachers->random() : $students->random();
            $allThreads[] = ForumThread::create([
                'title' => $topic['title'],
                'body' => $topic['body'],
                'user_id' => $author->id,
                'scope' => 'school',
                'is_pinned' => false,
                'is_locked' => false,
                'is_announcement' => false,
            ]);
        }

        // 3. Classroom-scoped discussions (12 threads, 2 per classroom)
        foreach ($classrooms as $classroom) {
            $classStudents = Student::where('classroom_id', $classroom->id)->with('user')->get();
            if ($classStudents->isEmpty()) {
                continue;
            }

            $classroomGuru = $classroom->teacher_id;
            
            // Thread 1: By Teacher
            $allThreads[] = ForumThread::create([
                'title' => 'Tugas & Diskusi Materi Pembelajaran Kelas ' . $classroom->name,
                'body' => 'Silakan posting pertanyaan atau tanggapan mengenai materi pelajaran yang kurang jelas di kelas kita.',
                'user_id' => $classroomGuru,
                'classroom_id' => $classroom->id,
                'scope' => 'class',
                'is_pinned' => true,
                'is_locked' => false,
                'is_announcement' => false,
            ]);

            // Thread 2: By Student
            $studentAuthor = $classStudents->random()->user;
            $allThreads[] = ForumThread::create([
                'title' => 'Diskusi Kelompok Tugas Kerja Kelompok Kelas ' . $classroom->name,
                'body' => 'Teman-teman sekelas, mari kumpul setelah shalat dzuhur untuk membagi tugas kelompok fisika/ekonomi.',
                'user_id' => $studentAuthor->id,
                'classroom_id' => $classroom->id,
                'scope' => 'class',
                'is_pinned' => false,
                'is_locked' => false,
                'is_announcement' => false,
            ]);
        }

        // 4. Seed Replies (~100 replies)
        $replySamples = [
            'Sangat setuju dengan info ini. Terima kasih banyak admin!',
            'Alhamdulillah, ini mempermudah kami wali murid untuk pantau anak.',
            'Bagaimana kalau kita adakan koordinasi hari sabtu besok?',
            'Syukron Ustadz atas ilmu dan nasehatnya yang sangat bermanfaat.',
            'Saya mau tanya, apakah ada link pendaftaran online-nya?',
            'Buku ini sangat lengkap dan mudah dipahami, recommended!',
            'Insya Allah kami siap berpartisipasi dan membantu jalannya acara.',
            'Boleh minta file PDF materinya dishare di sini?',
            'Keren sekali programnya! Semoga istiqomah.',
            'Apakah tugas ini dikumpulkan secara mandiri atau per kelompok?',
            'Saya rasa ide tersebut sangat luar biasa, ayo segera kita eksekusi.',
            'Terima kasih atas tanggapannya, sangat membantu menjelaskan keraguan saya.'
        ];

        $replyCounter = 0;
        foreach ($allThreads as $thread) {
            // Seed 4-6 replies per thread to get ~100 replies total
            $replyCount = rand(4, 6);
            $threadReplies = [];

            for ($r = 0; $r < $replyCount; $r++) {
                // Determine replier: teachers, students or parents (no parents if locked/announcement usually, but we keep it general)
                $replier = $students->random();
                if (rand(1, 100) <= 30) {
                    $replier = $teachers->random();
                } elseif (rand(1, 100) <= 15) {
                    $replier = $parents->random();
                }

                // If thread is locked and replier is student/parent, override to teacher/admin
                if ($thread->is_locked && $replier->role !== 'guru' && $replier->role !== 'admin') {
                    $replier = $teachers->random();
                }

                $parentReplyId = null;
                // 30% chance this is a nested reply (to a previous reply on this thread)
                if (!empty($threadReplies) && rand(1, 100) <= 30) {
                    $parentReplyId = $threadReplies[array_rand($threadReplies)]->id;
                }

                $reply = ForumReply::create([
                    'thread_id' => $thread->id,
                    'user_id' => $replier->id,
                    'parent_id' => $parentReplyId,
                    'body' => $replySamples[array_rand($replySamples)] . ' (' . $replier->name . ')',
                    'is_best_answer' => ($r === 0 && rand(1, 100) <= 20) ? true : false,
                ]);

                $threadReplies[] = $reply;
                $replyCounter++;

                // Seed reactions for this reply
                $reactionCount = rand(0, 4);
                $reactionUsers = $students->random(min(4, $students->count()));
                for ($rc = 0; $rc < $reactionCount; $rc++) {
                    ForumReaction::create([
                        'user_id' => $reactionUsers[$rc]->id,
                        'reactable_type' => ForumReply::class,
                        'reactable_id' => $reply->id,
                        'type' => ['', '', '', ''][rand(0, 3)],
                    ]);
                }
            }

            // Seed reactions for this thread
            $reactionCount = rand(1, 6);
            $reactionUsers = $students->random(min(6, $students->count()));
            for ($tc = 0; $tc < $reactionCount; $tc++) {
                ForumReaction::create([
                    'user_id' => $reactionUsers[$tc]->id,
                    'reactable_type' => ForumThread::class,
                    'reactable_id' => $thread->id,
                    'type' => ['', '', '', ''][rand(0, 3)],
                ]);
            }
        }
    }
}
