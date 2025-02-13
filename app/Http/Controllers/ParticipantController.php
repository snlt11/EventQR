<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\SendInvitationEmail;
use App\Exports\ParticipantsExport;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ParticipantController extends Controller
{
    public function participantDownloadInformation()
    {
        $fileName = 'participants-info-' . time() . '.xlsx';
        return Excel::download(new ParticipantsExport, $fileName);
    }

    public function participantCheckIn($token)
    {

        $participant = Participant::where('token', $token)->first();

        if (!$participant) {
            return response()->json(['message' => 'Invalid token.'], 404);
        }

        if ($participant->checked_in_at) {
            return response()->json([
                'message' => 'Participant already checked in.',
                'checked_in_at' => $participant->checked_in_at->toDateTimeString()
            ], 409);
        }

        $participant->update([
            'checked_in_at' => now(),
        ]);

        return response()->json([
            'message' => 'Check-in successful.',
            'participant' => [
                'name' => $participant->name,
                'email' => $participant->email,
                'checked_in_at' => $participant->checked_in_at->toDateTimeString(),
            ]
        ], 200);
    }
}
