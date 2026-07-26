<?php

namespace App\Http\Controllers\UX;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DesignSystemController extends Controller
{
    public function showcase()
    {
        return view('design-system.showcase');
    }

    public function documentation()
    {
        return view('design-system.documentation');
    }
}
