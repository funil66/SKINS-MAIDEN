<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SteamAuthController extends Controller
{
    /**
     * Redirect the user to the Steam authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToSteam()
    {
        return Socialite::driver('steam')->redirect();
    }

    /**
     * Obtain the user information from Steam.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleSteamCallback()
    {
        try {
            $steamUser = Socialite::driver('steam')->user();

            $user = User::updateOrCreate(
                ['steam_id' => $steamUser->getId()],
                [
                    'name' => $steamUser->getNickname(),
                    'email' => $steamUser->getEmail(), // Note: Steam may not always provide an email
                    'password' => bcrypt(Str::random(24)), // Create a random password
                    'avatar' => $steamUser->getAvatar(),
                ]
            );

            $token = $user->createToken('steam-auth-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to login using Steam. Please try again.'], 500);
        }
    }
}
