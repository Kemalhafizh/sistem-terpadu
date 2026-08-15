<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Student\ShowcaseService;

class ShowcaseController extends Controller
{
    protected $service;

    public function __construct(ShowcaseService $service)
    {
        $this->service = $service;
    }

    public function gallery()
    {
        return view('showcase.gallery');
    }

    public function myShowcase()
    {
        return view('showcase.editor');
    }

    public function create()
    {
        return view('showcase.editor');
    }

    public function store(Request $request)
    {
        return back()->with('success', 'Karya berhasil dipublikasi');
    }

    public function publicProfile()
    {
        return view('showcase.public-profile');
    }

    public function endorse(Request $request)
    {
        return back()->with('success', 'Dukungan berhasil diberikan');
    }
}
