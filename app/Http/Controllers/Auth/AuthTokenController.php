<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AuthTokenController extends Controller
{
    public function create(Request $request)
    {
        $attributes = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'required|max:255',
        ]);

        $signedUrl = URL::temporarySignedRoute('auth.token', now()->addHour(), $attributes);
    }

    public function store(Request $request)
    {
        if (! $request->hasValidSignature()) abort(401);

        //
    }
}
