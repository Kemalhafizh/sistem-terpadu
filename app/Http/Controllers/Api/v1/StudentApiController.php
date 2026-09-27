<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentApiController extends Controller
{
    /**
     * Get authenticated student profile (API V1 Gateway Endpoint).
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'version' => 'v1',
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'school' => 'SMA Islam Sultan Agung 1 Semarang',
            ],
        ]);
    }
}
