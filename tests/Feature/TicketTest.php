<?php

/** @var TestCase|stdClass $this */

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Tests\TestCase;

beforeEach(function () {
    // Users
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->wakaSarpras = User::factory()->create(['role' => 'waka_sarpras']);
    $this->stafTu = User::factory()->create(['role' => 'staf_tu']);
    
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->siswa = User::factory()->create(['role' => 'student']);
    $this->parent = User::factory()->create(['role' => 'parent']);

    // Another reporter to test scoping
    $this->otherGuru = User::factory()->create(['role' => 'guru']);
});

test('authenticated users can access the helpdesk list page and see their scoped tickets', function () {
    // Create a ticket reported by guru
    $ticket1 = Ticket::create([
        'title' => 'Kerusakan LCD Proyektor',
        'description' => 'LCD Proyektor di kelas XII-B mati total.',
        'category' => 'sarpras',
        'priority' => 'high',
        'status' => 'open',
        'reporter_id' => $this->guru->id,
    ]);

    // Create a ticket reported by other guru
    $ticket2 = Ticket::create([
        'title' => 'Akun LMS Terkunci',
        'description' => 'Tidak bisa login ke aplikasi LMS Sekolah.',
        'category' => 'it',
        'priority' => 'medium',
        'status' => 'open',
        'reporter_id' => $this->otherGuru->id,
    ]);

    // Guru should see their own ticket but not the other's
    $responseGuru = $this->actingAs($this->guru)->get(route('helpdesk.tickets.index'));
    $responseGuru->assertStatus(200);
    $responseGuru->assertSee($ticket1->title);
    $responseGuru->assertDontSee($ticket2->title);

    // Admin should see both tickets
    $responseAdmin = $this->actingAs($this->admin)->get(route('helpdesk.tickets.index'));
    $responseAdmin->assertStatus(200);
    $responseAdmin->assertSee($ticket1->title);
    $responseAdmin->assertSee($ticket2->title);
});

test('a user can create a support ticket with automated sequential numbering and SLA calculation', function () {
    Notification::fake();

    $response = $this->actingAs($this->guru)->post(route('helpdesk.tickets.store'), [
        'title' => 'AC Bocor di Ruang Guru',
        'description' => 'Air AC menetes deras hingga membasahi meja kerja.',
        'category' => 'sarpras',
        'priority' => 'critical', // Critical priority = 4 hours SLA
    ]);

    // Assert redirect to show ticket page
    $ticket = Ticket::where('reporter_id', $this->guru->id)->first();
    $response->assertRedirect(route('helpdesk.tickets.show', $ticket->uuid));

    // Assert sequential ticket number generation (e.g. TK-YYYY-XXXX)
    $currentYear = date('Y');
    expect($ticket->ticket_number)->toStartWith("TK-{$currentYear}-");

    // Assert database content and SLA due_date calculation (Critical = 4 hours)
    $expectedDueDate = $ticket->created_at->addHours(4);
    expect($ticket->due_date->format('Y-m-d H:i'))->toBe($expectedDueDate->format('Y-m-d H:i'));

    // Assert that notifications were fired
    Notification::assertSentTo(
        $this->guru,
        TicketCreatedNotification::class
    );

    Notification::assertSentTo(
        [$this->stafTu, $this->wakaSarpras, $this->admin, $this->superadmin],
        TicketCreatedNotification::class
    );
});

test('users can only view their own tickets whereas staff can view all tickets', function () {
    // Guru ticket
    $ticket = Ticket::create([
        'title' => 'Keyboard Ruang Laboratorium Rusak',
        'description' => 'Beberapa tombol tidak berfungsi.',
        'category' => 'it',
        'priority' => 'low',
        'status' => 'open',
        'reporter_id' => $this->guru->id,
    ]);

    // Reporter (Guru) can view their own ticket
    $this->actingAs($this->guru)->get(route('helpdesk.tickets.show', $ticket->uuid))->assertStatus(200);

    // Other user (Siswa) cannot view Guru's ticket (403)
    $this->actingAs($this->siswa)->get(route('helpdesk.tickets.show', $ticket->uuid))->assertStatus(403);

    // Staff (TU) can view the ticket
    $this->actingAs($this->stafTu)->get(route('helpdesk.tickets.show', $ticket->uuid))->assertStatus(200);
});

test('public comments are visible to everyone, but internal comments are concealed from reporters', function () {
    // Ticket
    $ticket = Ticket::create([
        'title' => 'Internet Lambat',
        'description' => 'Koneksi lemot sekali di area perpustakaan.',
        'category' => 'it',
        'priority' => 'medium',
        'status' => 'open',
        'reporter_id' => $this->guru->id,
    ]);

    // 1. Staff posts a public comment
    $publicComment = TicketComment::create([
        'ticket_id' => $ticket->id,
        'user_id' => $this->stafTu->id,
        'body' => 'Sedang kami cek ke pihak provider Indihome.',
        'is_internal' => false,
    ]);

    // 2. Staff posts an internal note
    $internalComment = TicketComment::create([
        'ticket_id' => $ticket->id,
        'user_id' => $this->stafTu->id,
        'body' => 'Catatan Rahasia: Pihak provider infonya ada gangguan kabel FO di jalan raya.',
        'is_internal' => true,
    ]);

    // Reporter (Guru) views the ticket detail
    $responseReporter = $this->actingAs($this->guru)->get(route('helpdesk.tickets.show', $ticket->uuid));
    $responseReporter->assertSee($publicComment->body);
    $responseReporter->assertDontSee($internalComment->body); // MUST NOT see internal notes!

    // Technical Staff views the ticket detail
    $responseStaff = $this->actingAs($this->stafTu)->get(route('helpdesk.tickets.show', $ticket->uuid));
    $responseStaff->assertSee($publicComment->body);
    $responseStaff->assertSee($internalComment->body); // MUST see internal notes!
});

test('non-staff users are blocked from posting internal comments', function () {
    // Ticket
    $ticket = Ticket::create([
        'title' => 'Kendala Kursi Patah',
        'description' => 'Kursi patah di barisan belakang.',
        'category' => 'sarpras',
        'priority' => 'low',
        'status' => 'open',
        'reporter_id' => $this->guru->id,
    ]);

    // Reporter (Guru) attempts to post an internal comment
    $response = $this->actingAs($this->guru)->post(route('helpdesk.tickets.comment', $ticket->uuid), [
        'body' => 'Percobaan membobol komentar internal.',
        'is_internal' => 1,
    ]);

    $response->assertStatus(403); // Forbidden!
});

test('staff can successfully assign technical assignee and ticket transitions to in_progress', function () {
    Notification::fake();

    // Ticket
    $ticket = Ticket::create([
        'title' => 'Atap Bocor Kelas XI',
        'description' => 'Genteng geser saat hujan angin kemarin.',
        'category' => 'sarpras',
        'priority' => 'high',
        'status' => 'open',
        'reporter_id' => $this->guru->id,
    ]);

    // Waka Sarpras assigns to Staff TU
    $response = $this->actingAs($this->wakaSarpras)->post(route('helpdesk.tickets.assign', $ticket->uuid), [
        'assignee_id' => $this->stafTu->id,
    ]);

    $response->assertRedirect();
    
    // Assert assignee updated and status transitioned
    $ticket->refresh();
    expect($ticket->assignee_id)->toBe($this->stafTu->id);
    expect($ticket->status)->toBe('in_progress');

    // Assert notification sent to reporter
    Notification::assertSentTo(
        $this->guru,
        TicketStatusUpdatedNotification::class
    );
});

test('staff can resolve ticket with resolution note and log resolution timing', function () {
    Notification::fake();

    // Ticket
    $ticket = Ticket::create([
        'title' => 'Kendala Printer TU',
        'description' => 'Kertas selalu tersangkut (paper jam).',
        'category' => 'it',
        'priority' => 'medium',
        'status' => 'in_progress',
        'reporter_id' => $this->guru->id,
        'assignee_id' => $this->stafTu->id,
    ]);

    // Resolve the ticket
    $resolutionText = 'Rol penarik kertas dibersihkan dan diganti karet penariknya. Printer normal kembali.';
    $response = $this->actingAs($this->stafTu)->post(route('helpdesk.tickets.resolve', $ticket->uuid), [
        'resolution' => $resolutionText,
    ]);

    $response->assertRedirect();

    // Assert resolution data
    $ticket->refresh();
    expect($ticket->status)->toBe('resolved');
    expect($ticket->resolution)->toBe($resolutionText);
    expect($ticket->resolved_at)->not->toBeNull();

    // Assert notification sent to reporter
    Notification::assertSentTo(
        $this->guru,
        TicketStatusUpdatedNotification::class
    );
});

test('staff can permanently close a resolved ticket', function () {
    // Ticket in resolved state
    $ticket = Ticket::create([
        'title' => 'Papan Tulis Rusak',
        'description' => 'Permukaannya mengelupas tidak bisa ditulis.',
        'category' => 'sarpras',
        'priority' => 'low',
        'status' => 'resolved',
        'reporter_id' => $this->guru->id,
        'assignee_id' => $this->stafTu->id,
        'resolution' => 'Sudah diganti dengan papan tulis white board baru.',
        'resolved_at' => now()->subHour(),
    ]);

    // Close the ticket
    $response = $this->actingAs($this->stafTu)->post(route('helpdesk.tickets.close', $ticket->uuid));
    $response->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe('closed');
    expect($ticket->closed_at)->not->toBeNull();
});

test('unauthorized users are blocked from executive administrative actions', function () {
    // Ticket
    $ticket = Ticket::create([
        'title' => 'Masalah Wastafel Kelas',
        'description' => 'Wastafel tersumbat tidak mengalir.',
        'category' => 'sarpras',
        'priority' => 'low',
        'status' => 'open',
        'reporter_id' => $this->guru->id,
    ]);

    // 1. Guru cannot access analytics dashboard
    $this->actingAs($this->guru)->get(route('helpdesk.dashboard'))->assertStatus(403);

    // 2. Guru cannot assign technician
    $this->actingAs($this->guru)->post(route('helpdesk.tickets.assign', $ticket->uuid), [
        'assignee_id' => $this->stafTu->id,
    ])->assertStatus(403);

    // 3. Guru cannot resolve ticket
    $this->actingAs($this->guru)->post(route('helpdesk.tickets.resolve', $ticket->uuid), [
        'resolution' => 'Dicoba diresolve sendiri.',
    ])->assertStatus(403);

    // 4. Guru cannot close ticket
    $this->actingAs($this->guru)->post(route('helpdesk.tickets.close', $ticket->uuid))->assertStatus(403);
});
