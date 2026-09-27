<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KnowledgeArticleController extends Controller
{
    public function index()
    {
        return view('knowledge.articles.index');
    }

    public function create()
    {
        return view('knowledge.articles.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('knowledge.articles.index');
    }

    public function show($id)
    {
        return view('knowledge.articles.show');
    }

    public function toggleLike($id)
    {
        return back();
    }

    public function storeComment(Request $request, $id)
    {
        return back();
    }
}
