<?php

namespace App\Services\Content;

use App\Models\MediaAsset;
use App\Models\MediaFolder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaAssetService
{
    /**
     * Upload / Register new media asset into DAM repository.
     */
    public function upload(
        array $fileData,
        int $uploadedBy,
        ?int $folderId = null,
        array $tags = [],
        bool $isPublic = false
    ): MediaAsset {
        $filename = Str::slug(pathinfo($fileData['original_filename'], PATHINFO_FILENAME)) . '_' . time() . '.' . pathinfo($fileData['original_filename'], PATHINFO_EXTENSION);
        $storagePath = $fileData['storage_path'] ?? "dam_assets/{$filename}";

        return MediaAsset::create([
            'filename' => $filename,
            'original_filename' => $fileData['original_filename'],
            'mime_type' => $fileData['mime_type'] ?? 'application/octet-stream',
            'file_size_bytes' => $fileData['file_size_bytes'] ?? 1024,
            'storage_path' => $storagePath,
            'thumbnail_path' => $fileData['thumbnail_path'] ?? null,
            'uploaded_by' => $uploadedBy,
            'folder_id' => $folderId,
            'tags' => $tags,
            'metadata' => $fileData['metadata'] ?? [],
            'version' => 1,
            'is_public' => $isPublic,
        ]);
    }

    /**
     * Create subfolder in media library.
     */
    public function createFolder(string $name, int $createdBy, ?int $parentId = null): MediaFolder
    {
        return MediaFolder::create([
            'name' => $name,
            'parent_id' => $parentId,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Move media asset to a target folder.
     */
    public function moveToFolder(int $assetId, ?int $folderId): MediaAsset
    {
        $asset = MediaAsset::findOrFail($assetId);
        $asset->folder_id = $folderId;
        $asset->save();

        return $asset;
    }

    /**
     * Delete media asset.
     */
    public function delete(int $assetId, int $userId): bool
    {
        $asset = MediaAsset::findOrFail($assetId);

        if ($asset->uploaded_by !== $userId && ! auth()->user()->hasRole(['superadmin', 'admin'])) {
            throw new \Exception('Akses ditolak: Hanya pengunggah atau Admin yang dapat menghapus aset ini.');
        }

        return $asset->delete();
    }

    /**
     * Search media assets by filename or metadata tags.
     */
    public function search(string $query, ?int $folderId = null): Collection
    {
        $q = MediaAsset::with(['uploader', 'folder'])
            ->where(function ($sub) use ($query) {
                $sub->where('original_filename', 'like', "%{$query}%")
                    ->orWhere('filename', 'like', "%{$query}%")
                    ->orWhere('tags', 'like', "%{$query}%");
            });

        if ($folderId) {
            $q->where('folder_id', $folderId);
        }

        return $q->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get media assets filtered by tag.
     */
    public function getByTag(string $tag): Collection
    {
        return MediaAsset::whereJsonContains('tags', $tag)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get storage usage statistics breakdown.
     */
    public function getStorageUsage(): array
    {
        $totalBytes = MediaAsset::sum('file_size_bytes');
        $totalCount = MediaAsset::count();

        $imagesCount = MediaAsset::where('mime_type', 'like', 'image/%')->count();
        $documentsCount = MediaAsset::where('mime_type', 'like', 'application/%')->count();
        $videoCount = MediaAsset::where('mime_type', 'like', 'video/%')->count();

        return [
            'total_bytes' => (int) $totalBytes,
            'total_count' => $totalCount,
            'formatted_total_size' => (new MediaAsset(['file_size_bytes' => $totalBytes]))->formatted_size,
            'images_count' => $imagesCount,
            'documents_count' => $documentsCount,
            'video_count' => $videoCount,
        ];
    }
}
