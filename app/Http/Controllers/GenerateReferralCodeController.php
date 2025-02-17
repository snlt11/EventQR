<?php

namespace App\Http\Controllers;

use App\Helper\CodeGenerator;
use App\Models\ReferralCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GenerateReferralCodeController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'expiration_datetime' => 'nullable|date|after:now',
            'total_counts' => 'nullable|integer|min:1|max:50',
        ]);

        $code = CodeGenerator::generate();

        $expiresAt = match (true) {
            $request->filled('expiration_datetime') => Carbon::parse($request->input('expiration_datetime')),
            default => Carbon::now()->addDays(7),
        };

        $totalCounts = $request->input('total_counts', 1);

        ReferralCode::create([
            'code' => $code,
            'admin_id' => Auth::id(),
            'expires_at' => $expiresAt,
            'is_active' => true,
            'used_counts' => 0,
            'total_counts' => $totalCounts,
        ]);

        return response()->json([
            'referral_code' => $code,
            'expires_at' => "{$expiresAt->format('Y-m-d H:i')}",
        ]);
    }
}
