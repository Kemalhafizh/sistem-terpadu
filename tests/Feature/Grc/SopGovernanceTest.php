<?php

use App\Models\SopAcknowledgement;
use App\Models\SopDocument;
use App\Models\User;
use App\Services\Grc\SopGovernanceService;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Admin Kebijakan SOP']);
    $this->sopService = app(SopGovernanceService::class);
});

test('sop governance service handles publishing, versioning, and digital acknowledgements', function () {
    $sop = SopDocument::create([
        'title' => 'SOP Keamanan Test',
        'code' => 'SOP-TEST-01',
        'category' => 'it',
        'content_html' => 'Isi Kebijakan Test',
        'version' => 1,
        'status' => 'draft',
        'effective_date' => now()->toDateString(),
        'review_date' => now()->addYear()->toDateString(),
        'author_id' => $this->user->id,
    ]);

    $published = $this->sopService->publish($sop->id, $this->user->id);
    expect($published->status)->toBe('published');
    expect($published->approved_by)->toBe($this->user->id);

    $ack = $this->sopService->acknowledge($sop->id, $this->user->id, '192.168.1.100');
    expect($ack->id)->not()->toBeNull();
    expect($ack->ip_address)->toBe('192.168.1.100');

    $compliance = $this->sopService->getComplianceRate($sop->id);
    expect($compliance)->toBeGreaterThan(0.0);

    $newVersion = $this->sopService->createVersion($sop->id, ['title' => 'SOP Keamanan Test V2']);
    expect($newVersion->version)->toBe(2);
    expect($newVersion->code)->toBe('SOP-TEST-01-V2');
});

test('user can view searchable sop library and reader interface via controller', function () {
    $sop = SopDocument::create([
        'title' => 'SOP Akademik Controller Test',
        'code' => 'SOP-AKD-01',
        'category' => 'academic',
        'content_html' => 'Isi SOP Akademik',
        'version' => 1,
        'status' => 'published',
        'effective_date' => now()->toDateString(),
        'review_date' => now()->addYear()->toDateString(),
        'author_id' => $this->user->id,
    ]);

    $indexResponse = $this->actingAs($this->user)->get(route('sop.index'));
    $indexResponse->assertStatus(200);

    $showResponse = $this->actingAs($this->user)->get(route('sop.show', $sop->id));
    $showResponse->assertStatus(200);
    $showResponse->assertSee('SOP-AKD-01');
});

test('user can acknowledge sop and admin can view compliance report via controller', function () {
    $sop = SopDocument::create([
        'title' => 'SOP Keuangan Sign Test',
        'code' => 'SOP-FIN-01',
        'category' => 'financial',
        'content_html' => 'Isi SOP Keuangan',
        'version' => 1,
        'status' => 'published',
        'effective_date' => now()->toDateString(),
        'review_date' => now()->addYear()->toDateString(),
        'author_id' => $this->user->id,
    ]);

    $ackResponse = $this->actingAs($this->user)->post(route('sop.acknowledge', $sop->id));
    $ackResponse->assertRedirect();
    expect(SopAcknowledgement::where('sop_document_id', $sop->id)->where('user_id', $this->user->id)->exists())->toBeTrue();

    $complianceResponse = $this->actingAs($this->user)->get(route('sop.compliance'));
    $complianceResponse->assertStatus(200);
    $complianceResponse->assertSee('SOP-FIN-01');
});
