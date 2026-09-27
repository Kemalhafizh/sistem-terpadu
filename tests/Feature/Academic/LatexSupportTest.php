<?php

use App\Models\User;
use App\Support\VendorAsset;

beforeEach(function () {
    $this->teacher = User::factory()->create(['role' => 'guru']);
});

test('app layout renders katex latex scripts and math delimiters', function () {
    $response = $this->actingAs($this->teacher)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee(VendorAsset::styleUrl('katex'), false);
    $response->assertSee(VendorAsset::url('katex'), false);
    $response->assertSee(VendorAsset::url('katex-mhchem'), false);
    $response->assertSee(VendorAsset::url('katex-auto-render'), false);
    $response->assertSee('renderSistaLatex');
});

test('cbt question bank creation view renders latex toolbar and live preview for saintek teachers', function () {
    $response = $this->actingAs($this->teacher)->get(route('cbt.question-banks.create'));

    $response->assertStatus(200);
    $response->assertSee('LaTeX Saintek:');
    $response->assertSee('\frac{a}{b}');
    $response->assertSee('\sqrt{x}');
    $response->assertSee('\ce{H2SO4}');
    $response->assertSee('latex-preview-box-question_text');
});
