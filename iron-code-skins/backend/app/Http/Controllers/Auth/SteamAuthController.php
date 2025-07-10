<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SteamAPIService;

class SteamAuthController extends Controller
{
    protected $steamService;

    public function __construct(SteamAPIService $steamService)
    {
        $this->steamService = $steamService;
    }

    public function redirectToSteam()
    {
        $url = $this->steamService->getLoginUrl();
        return redirect($url);
    }

    public function handleSteamCallback(Request $request)
    {
        $steamId = $request->input('openid.claimed_id');

        if (!$steamId) {
            return redirect('/login')->withErrors(['msg' => 'Steam authentication failed.']);
        }

        $user = $this->steamService->findOrCreateUser($steamId);

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Successfully logged in with Steam.');
    }
}