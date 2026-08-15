<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassFeedPost;

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
        // Store logic
        return redirect()->back();
    }

    public function acknowledgePost($id)
    {
        // Acknowledge logic
        return redirect()->back();
    }

    public function postCreator()
    {
        return view('fase62.class-feed.post-creator');
    }
}
