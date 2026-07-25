<?php

use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Models\User;
use App\Services\Content\MediaAssetService;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Admin Media DAM']);
    $this->mediaService = app(MediaAssetService::class);
});

test('media asset service creates folders, uploads assets, and calculates storage stats', function () {
    $folder = $this->mediaService->createFolder('Arsip SK Kemenag', $this->user->id);
    expect($folder->id)->not()->toBeNull();

    $asset = $this->mediaService->upload([
        'original_filename' => 'SK_Operasional_2026.pdf',
        'mime_type' => 'application/pdf',
        'file_size_bytes' => 1048576, // 1 MB
    ], $this->user->id, $folder->id, ['sk', 'kemenag']);

    expect($asset->id)->not()->toBeNull();
    expect($asset->formatted_size)->toBe('1.00 MB');

    $stats = $this->mediaService->getStorageUsage();
    expect($stats['total_bytes'])->toBeGreaterThanOrEqual(1048576);
    expect($stats['documents_count'])->toBeGreaterThanOrEqual(1);
});

test('media asset can be moved between folders and searched by tag', function () {
    $folder1 = $this->mediaService->createFolder('Folder A', $this->user->id);
    $folder2 = $this->mediaService->createFolder('Folder B', $this->user->id);

    $asset = $this->mediaService->upload([
        'original_filename' => 'Banner_PPDB_2026.png',
        'mime_type' => 'image/png',
        'file_size_bytes' => 500000,
    ], $this->user->id, $folder1->id, ['banner', 'ppdb']);

    expect($asset->folder_id)->toBe($folder1->id);

    // Move to folder2
    $moved = $this->mediaService->moveToFolder($asset->id, $folder2->id);
    expect($moved->folder_id)->toBe($folder2->id);

    // Search by tag
    $results = $this->mediaService->search('ppdb');
    expect($results->pluck('id'))->toContain($asset->id);
});

test('admin can view dam console and upload files via controller', function () {
    $indexResponse = $this->actingAs($this->user)->get(route('media.index'));
    $indexResponse->assertStatus(200);

    $uploadResponse = $this->actingAs($this->user)->post(route('media.upload'), [
        'original_filename' => 'Dokumen_Rencana_Kerja.pdf',
        'mime_type' => 'application/pdf',
        'file_size_bytes' => 2048000,
        'tags' => 'rkta, 2026',
    ]);

    $uploadResponse->assertRedirect();
    expect(MediaAsset::where('original_filename', 'Dokumen_Rencana_Kerja.pdf')->exists())->toBeTrue();
});

test('user can create subfolder and delete asset via controller', function () {
    $folderResponse = $this->actingAs($this->user)->post(route('media.create_folder'), [
        'name' => 'Subfolder Uji Coba',
    ]);
    $folderResponse->assertRedirect();

    $folder = MediaFolder::where('name', 'Subfolder Uji Coba')->first();
    expect($folder)->not()->toBeNull();

    $asset = $this->mediaService->upload([
        'original_filename' => 'Sample_Temp_Delete.jpg',
        'mime_type' => 'image/jpeg',
        'file_size_bytes' => 1000,
    ], $this->user->id);

    $deleteResponse = $this->actingAs($this->user)->delete(route('media.destroy', $asset->id));
    $deleteResponse->assertRedirect();

    expect(MediaAsset::find($asset->id))->toBeNull();
});
