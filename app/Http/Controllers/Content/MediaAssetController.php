<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use App\Services\Content\MediaAssetService;
use Illuminate\Http\Request;

class MediaAssetController extends Controller
{
    /**
     * Display Centralized Digital Asset Management (DAM) Console.
     */
    public function index(Request $request, MediaAssetService $mediaService)
    {
        $folderId = $request->query('folder_id');
        $searchQuery = $request->query('q');

        $currentFolder = $folderId ? MediaFolder::find($folderId) : null;
        $folders = MediaFolder::where('parent_id', $folderId)->get();

        if ($searchQuery) {
            $assets = $mediaService->search($searchQuery, $folderId ? (int) $folderId : null);
        } else {
            $assets = $mediaService->search('', $folderId ? (int) $folderId : null);
        }

        $stats = $mediaService->getStorageUsage();

        return view('content.media.library', compact('folders', 'currentFolder', 'assets', 'stats', 'searchQuery'));
    }

    /**
     * Upload / Register new media asset.
     */
    public function upload(Request $request, MediaAssetService $mediaService)
    {
        $request->validate([
            'original_filename' => 'required|string|max:255',
            'mime_type' => 'required|string',
            'file_size_bytes' => 'required|integer',
            'folder_id' => 'nullable|exists:media_folders,id',
            'tags' => 'nullable|string',
        ]);

        $uploadedBy = auth()->id();
        $tagsArray = array_filter(array_map('trim', explode(',', $request->input('tags', ''))));

        $asset = $mediaService->upload([
            'original_filename' => $request->original_filename,
            'mime_type' => $request->mime_type,
            'file_size_bytes' => (int) $request->file_size_bytes,
        ], $uploadedBy, $request->folder_id ? (int) $request->folder_id : null, $tagsArray);

        return back()->with('success', "Aset media [{$asset->original_filename}] berhasil diunggah ke repositori DAM!");
    }

    /**
     * Create new subfolder in DAM library.
     */
    public function createFolder(Request $request, MediaAssetService $mediaService)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        $createdBy = auth()->id();
        $folder = $mediaService->createFolder($request->name, $createdBy, $request->parent_id ? (int) $request->parent_id : null);

        return back()->with('success', "Folder [{$folder->name}] berhasil dibuat!");
    }

    /**
     * Move asset to another folder.
     */
    public function moveToFolder(Request $request, int $id, MediaAssetService $mediaService)
    {
        $request->validate([
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        $asset = $mediaService->moveToFolder($id, $request->folder_id ? (int) $request->folder_id : null);

        return back()->with('success', "Aset [{$asset->original_filename}] dipindahkan ke folder sasaran!");
    }

    /**
     * Delete media asset.
     */
    public function destroy(int $id, MediaAssetService $mediaService)
    {
        $userId = auth()->id();
        try {
            $mediaService->delete($id, $userId);

            return back()->with('success', 'Aset media berhasil dihapus dari repositori.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * API Storage statistics endpoint.
     */
    public function storageStats(MediaAssetService $mediaService)
    {
        return response()->json($mediaService->getStorageUsage());
    }
}
