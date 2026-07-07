<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CookieConsentController extends Controller
{
    public function store(Request $request)
    {
        $consent = $request->input('consent', 'declined');
        $allowed = ['accepted', 'declined'];

        if (!in_array($consent, $allowed, true)) {
            $consent = 'declined';
        }

        $request->session()->put('cookie_consent', $consent === 'accepted');

        return response()->json([
            'success' => true,
            'consent' => $consent,
        ])->cookie(Cookie::make('cookie_consent', $consent, 60 * 24 * 365));
    }
}
