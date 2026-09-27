<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\EventAttendanceEvent;
use App\Models\EventAttendanceRecord;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EventAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clean existing records
        Schema::disableForeignKeyConstraints();
        EventAttendanceRecord::truncate();
        EventAttendanceEvent::truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Fetch required users, classrooms, students
        $teachers = User::where('role', 'guru')->get();
        if ($teachers->isEmpty()) {
            $teachers = User::take(5)->get();
        }

        $students = Student::with('user')->get();
        if ($students->isEmpty()) {
            return; // Exit if no students/users are seeded yet
        }

        $classrooms = Classroom::all();

        // Random creator
        $creator = User::whereIn('role', ['admin', 'superadmin', 'guru'])->first() ?? User::first();

        // 3. Define 10 events
        $eventsData = [
            [
                'name' => 'Upacara Bendera Hari Senin',
                'type' => 'upacara',
                'days_ago' => 28,
                'start_time' => '07:00:00',
                'end_time' => '08:00:00',
                'target' => 'all_students',
                'target_ids' => null,
            ],
            [
                'name' => 'Sholat Dhuha Berjamaah & Dzikir Pagi',
                'type' => 'sholat_dhuha',
                'days_ago' => 25,
                'start_time' => '07:30:00',
                'end_time' => '08:30:00',
                'target' => 'all_students',
                'target_ids' => null,
            ],
            [
                'name' => 'Rapat Koordinasi Bulanan Dewan Guru',
                'type' => 'rapat_guru',
                'days_ago' => 20,
                'start_time' => '13:30:00',
                'end_time' => '15:30:00',
                'target' => 'all_teachers',
                'target_ids' => null,
            ],
            [
                'name' => 'Kultum PAI & Muhadharah Kelas XII',
                'type' => 'kultum',
                'days_ago' => 18,
                'start_time' => '12:30:00',
                'end_time' => '13:00:00',
                'target' => 'specific_class',
                'target_ids' => $classrooms->whereIn('name', ['XII-A', 'XII-B'])->pluck('id')->toArray(),
            ],
            [
                'name' => 'Peringatan Isra Mi\'raj Nabi Muhammad SAW 1447 H',
                'type' => 'phbi',
                'days_ago' => 15,
                'start_time' => '08:00:00',
                'end_time' => '11:30:00',
                'target' => 'all_students',
                'target_ids' => null,
            ],
            [
                'name' => 'Pembekalan Pengurus OSIS & MPK Baru',
                'type' => 'osis',
                'days_ago' => 10,
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'target' => 'specific_group',
                'target_ids' => $students->take(12)->pluck('user_id')->toArray(),
            ],
            [
                'name' => 'Pembukaan Class Meeting Semester Genap',
                'type' => 'class_meeting',
                'days_ago' => 8,
                'start_time' => '07:30:00',
                'end_time' => '12:00:00',
                'target' => 'all_students',
                'target_ids' => null,
            ],
            [
                'name' => 'Sholat Dzuhur Berjamaah Bersama',
                'type' => 'sholat_dzuhur',
                'days_ago' => 5,
                'start_time' => '12:00:00',
                'end_time' => '12:45:00',
                'target' => 'all_students',
                'target_ids' => null,
            ],
            [
                'name' => 'Kajian Rutin Guru: Fikih Muamalah kontemporer',
                'type' => 'kultum',
                'days_ago' => 3,
                'start_time' => '13:30:00',
                'end_time' => '14:45:00',
                'target' => 'all_teachers',
                'target_ids' => null,
            ],
            [
                'name' => 'Upacara Hari Lahir Pancasila',
                'type' => 'upacara',
                'days_ago' => 1,
                'start_time' => '07:00:00',
                'end_time' => '08:00:00',
                'target' => 'all_students',
                'target_ids' => null,
            ],
        ];

        // 4. Loop to insert events and records
        foreach ($eventsData as $data) {
            $eventDate = Carbon::now()->subDays($data['days_ago'])->format('Y-m-d');

            $event = EventAttendanceEvent::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'event_date' => $eventDate,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'target' => $data['target'],
                'target_ids' => $data['target_ids'],
                'created_by' => $creator->id,
                'notes' => 'Seeded automatically. Realistis log kehadiran untuk SMA Islam Sultan Agung 1 Semarang.',
            ]);

            // Resolve target user ids
            $userIds = [];
            $target = $data['target'];
            $targetIds = $data['target_ids'] ?? [];

            switch ($target) {
                case 'all_students':
                    $userIds = User::where('role', 'student')->pluck('id')->toArray();
                    break;

                case 'all_teachers':
                    $userIds = User::where('role', 'guru')->pluck('id')->toArray();
                    break;

                case 'specific_class':
                    if (! empty($targetIds)) {
                        $userIds = Student::whereIn('classroom_id', $targetIds)
                            ->pluck('user_id')
                            ->filter()
                            ->toArray();
                    }
                    break;

                case 'specific_group':
                    $userIds = $targetIds;
                    break;
            }

            $userIds = array_unique($userIds);

            $records = [];
            foreach ($userIds as $userId) {
                // Determine randomized realistic status
                $rand = rand(1, 100);
                if ($rand <= 82) {
                    $status = 'hadir';
                } elseif ($rand <= 88) {
                    $status = 'terlambat';
                } elseif ($rand <= 92) {
                    $status = 'izin';
                } elseif ($rand <= 95) {
                    $status = 'sakit';
                } else {
                    $status = 'alpa';
                }

                // Check-in time simulation
                $checkInTime = null;
                if ($status === 'hadir') {
                    // check-in 15 minutes to 1 minute before start
                    $minutes = rand(1, 15);
                    $checkInTime = Carbon::createFromFormat('H:i:s', $data['start_time'])
                        ->subMinutes($minutes)
                        ->format('H:i:s');
                } elseif ($status === 'terlambat') {
                    // check-in 1 to 20 minutes after start
                    $minutes = rand(1, 20);
                    $checkInTime = Carbon::createFromFormat('H:i:s', $data['start_time'])
                        ->addMinutes($minutes)
                        ->format('H:i:s');
                }

                // Notes simulation
                $notes = null;
                if ($status === 'izin') {
                    $notes = ['Ada acara keluarga', 'Mengikuti lomba luar sekolah', 'Keperluan keluarga penting'][rand(0, 2)];
                } elseif ($status === 'sakit') {
                    $notes = ['Demam & flu', 'Sakit kepala', 'Surat dokter terlampir'][rand(0, 2)];
                } elseif ($status === 'terlambat') {
                    $notes = ['Ban sepeda bocor', 'Terjebak macet', 'Bangun kesiangan'][rand(0, 2)];
                }

                $records[] = [
                    'event_id' => $event->id,
                    'user_id' => $userId,
                    'status' => $status,
                    'check_in_time' => $checkInTime,
                    'notes' => $notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($records)) {
                foreach (array_chunk($records, 100) as $chunk) {
                    EventAttendanceRecord::insert($chunk);
                }
            }
        }
    }
}
