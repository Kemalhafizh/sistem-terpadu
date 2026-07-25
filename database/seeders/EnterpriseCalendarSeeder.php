<?php

namespace Database\Seeders;

use App\Models\BookableResource;
use App\Models\User;
use App\Services\Communication\CalendarService;
use App\Services\Communication\ResourceBookingService;
use Illuminate\Database\Seeder;

class EnterpriseCalendarSeeder extends Seeder
{
    public function run(): void
    {
        $calendarService = app(CalendarService::class);
        $bookingService = app(ResourceBookingService::class);
        $users = User::take(3)->get();

        if ($users->isEmpty()) {
            return;
        }

        $organizer = $users->first();

        // Seed Bookable Resources
        $aula = BookableResource::updateOrCreate(
            ['name' => 'Aula Utama SMA Islam Sultan Agung 1'],
            [
                'type' => 'room',
                'capacity' => 500,
                'location' => 'Gedung Utama Lantai 3',
                'description' => 'Aula serbaguna ber-AC lengkap dengan sound system, proyektor 4K, dan panggung.',
                'is_available' => true,
            ]
        );

        $labCbt = BookableResource::updateOrCreate(
            ['name' => 'Lab Komputer CBT Sains'],
            [
                'type' => 'facility',
                'capacity' => 40,
                'location' => 'Gedung Lab Lantai 2',
                'description' => 'Laboratorium komputer ujian terintegrasi 40 unit PC desktop.',
                'is_available' => true,
            ]
        );

        // Seed Calendar Events
        $calendarService->createEvent([
            'title' => 'Rapat Koordinasi Penilaian Akhir Semester (PAS)',
            'description' => 'Penyusunan jadwal dan bank soal CBT semester genap.',
            'event_type' => 'meeting',
            'start_datetime' => now()->addDays(2)->setHour(9)->setMinute(0)->toDateTimeString(),
            'end_datetime' => now()->addDays(2)->setHour(11)->setMinute(30)->toDateTimeString(),
            'location' => 'Ruang Conference YBWSA',
            'visibility' => 'staff',
        ], $organizer->id);

        $calendarService->createEvent([
            'title' => 'Kajian Rutin Jumat Pagi & Doa Bersama',
            'description' => 'Kajian keislaman mingguan untuk seluruh siswa dan dewan guru.',
            'event_type' => 'ceremony',
            'start_datetime' => now()->next('Friday')->setHour(7)->setMinute(0)->toDateTimeString(),
            'end_datetime' => now()->next('Friday')->setHour(8)->setMinute(0)->toDateTimeString(),
            'location' => 'Masjid Aqsa Sekolah',
            'visibility' => 'public',
        ], $organizer->id);

        // Seed Resource Booking
        $bookingService->createBooking([
            'bookable_resource_id' => $aula->id,
            'purpose' => 'Seminar Motivasi Karir & Persiapan UTBK Siswa Kelas 12',
            'start_datetime' => now()->addDays(4)->setHour(8)->setMinute(0)->toDateTimeString(),
            'end_datetime' => now()->addDays(4)->setHour(12)->setMinute(0)->toDateTimeString(),
        ], $organizer->id);
    }
}
