<?php

use App\Models\DocumentSignature;
use App\Models\User;
use App\Services\Communication\DigitalSignatureService;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Dr. H. Ahmad Ruslan, M.Pd.']);
    $this->signatureService = app(DigitalSignatureService::class);
});

test('digital signature service requests and signs document with sha256 hash', function () {
    $request = $this->signatureService->requestSignature(
        'Rapor Akademik',
        501,
        $this->user->id,
        'Kepala Sekolah'
    );

    expect($request->isSigned())->toBeFalse();
    expect($request->qr_verification_code)->toContain('SISTA-DOC-');

    $signed = $this->signatureService->sign(
        $request->id,
        $this->user->id,
        '127.0.0.1'
    );

    expect($signed->isSigned())->toBeTrue();
    expect($signed->signature_hash)->not()->toBeNull();
    expect(strlen($signed->signature_hash))->toBe(64); // SHA-256 length
});

test('public verification endpoint verifies signed document without authentication', function () {
    $doc = $this->signatureService->requestSignature(
        'Surat Keputusan (SK)',
        702,
        $this->user->id,
        'Kepala Sekolah'
    );

    $this->signatureService->sign($doc->id, $this->user->id, '192.168.1.10');

    // Test Service verification
    $verification = $this->signatureService->verify($doc->qr_verification_code);
    expect($verification['is_valid'])->toBeTrue();
    expect($verification['signer_name'])->toBe('Dr. H. Ahmad Ruslan, M.Pd.');

    // Test Public Controller Route (No Auth required)
    $response = $this->get(route('documents.verify_public', $doc->qr_verification_code));
    $response->assertStatus(200);
    $response->assertSee('DOKUMEN DIJAMIN ABSAH', false);
    $response->assertSee('Dr. H. Ahmad Ruslan, M.Pd.');
});

test('user can view pending signatures queue and sign via controller', function () {
    $doc = $this->signatureService->requestSignature(
        'Kwitansi Pembayaran',
        888,
        $this->user->id,
        'Bendahara Sekolah'
    );

    $queueResponse = $this->actingAs($this->user)->get(route('documents.pending_signatures'));
    $queueResponse->assertStatus(200);
    $queueResponse->assertSee($doc->qr_verification_code);

    $signFormResponse = $this->actingAs($this->user)->get(route('documents.sign_form', $doc->id));
    $signFormResponse->assertStatus(200);
    $signFormResponse->assertSee('Bubuhi Tanda Tangan Digital Sekarang');

    $executeSignResponse = $this->actingAs($this->user)->post(route('documents.sign', $doc->id));
    $executeSignResponse->assertRedirect(route('documents.pending_signatures'));

    $doc->refresh();
    expect($doc->isSigned())->toBeTrue();
});

test('user can view signature history audit log', function () {
    $doc = $this->signatureService->requestSignature('Surat Keterangan', 999, $this->user->id, 'Kepala Sekolah');
    $this->signatureService->sign($doc->id, $this->user->id, '127.0.0.1');

    $historyResponse = $this->actingAs($this->user)->get(route('documents.signature_history'));
    $historyResponse->assertStatus(200);
    $historyResponse->assertSee($doc->qr_verification_code);
});
