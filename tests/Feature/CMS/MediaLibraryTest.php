<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    Storage::fake('public');
});

test('admin can access media library index', function () {
    $response = $this->actingAs($this->admin)->get(route('cms.media.index'));
    $response->assertStatus(200);
});

test('admin can upload image file to media library', function () {
    $file = UploadedFile::fake()->image('campus_photo.jpg');

    $response = $this->actingAs($this->admin)->post(route('cms.media.index'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('cms.media.index'));
    Storage::disk('public')->assertExists('cms/media/'.$file->hashName());
});

test('admin can delete file from media library', function () {
    // 1. Create a dummy file in fake storage
    $filePath = 'cms/media/logo.png';
    Storage::disk('public')->put($filePath, 'dummy content');
    Storage::disk('public')->assertExists($filePath);

    // 2. Perform delete request
    $response = $this->actingAs($this->admin)->get(route('cms.media.index', ['delete' => $filePath]));

    $response->assertRedirect(route('cms.media.index'));
    Storage::disk('public')->assertMissing($filePath);
});
