<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\ClassFeedPost;
use Illuminate\Http\Request;

class ClassFeedController extends Controller
{
    public function index()
    {
        return redirect()->route('class_feed.timeline');
    }

    public function timeline()
    {
        $posts = ClassFeedPost::with('teacher')->latest()->get();

        return view('fase62.class-feed.timeline', compact('posts'));
    }

    public function storePost(Request $request)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function acknowledgePost($id)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function postCreator()
    {
        return view('fase62.class-feed.post-creator');
    }
}
