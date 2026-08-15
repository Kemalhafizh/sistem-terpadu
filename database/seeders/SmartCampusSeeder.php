<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VisitorLog;
use App\Models\MerchandiseItem;
use App\Models\MerchandiseOrder;
use App\Models\CanteenMenu;
use App\Models\MealPlan;
use App\Models\TicketedEvent;
use App\Models\EventTicket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SmartCampusSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure a user exists for testing
        $user = User::firstOrCreate([
            'email' => 'admin@sultanagung1.sch.id'
        ], [
            'name' => 'Admin Sekolah',
            'password' => bcrypt('password'),
        ]);

        // 1. Visitors
        VisitorLog::create([
            'visitor_name' => 'Budi Santoso',
            'identity_number' => '3374123456780001',
            'organization_name' => 'PT Pemeliharaan AC',
            'purpose' => 'maintenance',
            'badge_number' => 'V-AC001',
            'status' => 'active',
            'check_in_time' => now()->subHours(2),
        ]);

        VisitorLog::create([
            'visitor_name' => 'Siti Aminah',
            'purpose' => 'parent_visit',
            'badge_number' => 'V-PRT02',
            'status' => 'completed',
            'check_in_time' => now()->subDays(1)->setHour(9),
            'check_out_time' => now()->subDays(1)->setHour(11),
        ]);

        // 2. Merchandise
        $uniform = MerchandiseItem::create([
            'item_name' => 'Seragam OSIS Lengan Panjang',
            'sku' => 'SRG-OS-L-01',
            'category' => 'uniform',
            'price' => 150000,
            'current_stock' => 50,
            'available_sizes' => ['S', 'M', 'L', 'XL'],
        ]);

        $stationery = MerchandiseItem::create([
            'item_name' => 'Buku Tulis Logo Sekolah (Pack)',
            'sku' => 'STN-BK-01',
            'category' => 'stationery',
            'price' => 45000,
            'current_stock' => 100,
        ]);

        MerchandiseOrder::create([
            'order_number' => 'ORD-123456',
            'student_id' => $user->id,
            'total_amount' => 195000,
            'payment_status' => 'paid',
            'fulfillment_status' => 'processing',
            'order_items' => [
                ['id' => $uniform->id, 'quantity' => 1, 'price' => 150000],
                ['id' => $stationery->id, 'quantity' => 1, 'price' => 45000],
            ],
        ]);

        // 3. Canteen
        $menu1 = CanteenMenu::create([
            'meal_name' => 'Nasi Goreng Sayur Spesial',
            'ingredients' => 'Nasi, Telur, Wortel, Brokoli, Bumbu',
            'calories' => 450,
            'price' => 15000,
            'is_healthy_certified' => true,
        ]);

        MealPlan::create([
            'serve_date' => now()->toDateString(),
            'canteen_menu_id' => $menu1->id,
            'meal_time' => 'lunch',
            'preparation_quota' => 200,
        ]);

        // 4. Events
        $event = TicketedEvent::create([
            'event_name' => 'Pentas Seni Tahunan 2026',
            'description' => 'Acara pameran karya dan penampilan seni siswa.',
            'event_datetime' => now()->addDays(14),
            'venue' => 'Auditorium SMA Islam Sultan Agung 1',
            'max_capacity' => 500,
            'is_public' => true,
            'organizer_id' => $user->id,
        ]);

        EventTicket::create([
            'ticketed_event_id' => $event->id,
            'ticket_code' => 'TKT-PENSI-01',
            'attendee_name' => 'Andi Darmawan',
            'ticket_type' => 'student',
            'price' => 25000,
        ]);
    }
}
