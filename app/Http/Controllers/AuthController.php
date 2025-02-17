<?php

namespace App\Http\Controllers;

use App\Helper\MessageError;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'referral_code' => 'required|string|exists:referral_codes,code',
        ]);

        $referralCode = ReferralCode::where('code', $request->referral_code)->first();

        $referralCode ?? throw new MessageError('The provided referral code does not exist.');
        $referralCode->isExpired() && throw new MessageError('The referral code has expired.');
        $referralCode->used_counts >= $referralCode->total_counts && throw new MessageError('The referral code has reached its maximum usage limit.');
        !($referralCode->is_active) && throw new MessageError('The referral code is no longer active.');


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        $referralCode->increment('used_counts');

        $referralCode->used_counts >= $referralCode->total_counts && $referralCode->update(['is_active' => false]);

        $token = $user->createToken('AuthToken')->accessToken;
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        !Auth::attempt($request->only('email', 'password')) && throw new MessageError('The provided credentials are incorrect.');

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('AuthToken')->accessToken;

        return response()->json([
            'user' => $user,
            'access_token' => 'Bearer ' . $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
