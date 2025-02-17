<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\EventParticipant;
use Exception;
use App\Helper\MessageError;
use App\Jobs\SendStatusEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

class ParticipantController extends Controller
{
    public function validateQRAndAuth(Request $request)
    {
        $key = config('keys.token_key');
        $earlyCheckInHour = 3;
        try {

            Auth::check() || throw new MessageError('Unauthenticated.');

            $user = Auth::user();
            in_array($user->role, ['user', 'admin']) || throw new MessageError('Unauthorized. Insufficient permissions.');

            $token = $request->input('token');
            $token ?? throw new MessageError('Token is required.');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            $eventParticipant = EventParticipant::where('event_id', $decoded->event_id)
                ->where('participant_id', $decoded->participant_id)
                ->with(['event', 'participant'])
                ->first();

            $event = $eventParticipant->event;
            $now = Carbon::now();
            $eventStartTime = Carbon::parse($event->start_datetime);
            $eventEndTime = Carbon::parse($event->end_datetime);

            $eventParticipant->event->is_published || throw new MessageError('Event is not published.');

            $eventParticipant ?? throw new MessageError('Event participant not found.');

            $now->gt($eventEndTime) && throw new MessageError('Event has already finished. Check-in is no longer available.');

            $now->lt($eventStartTime->copy()->subHours($earlyCheckInHour)) && throw new MessageError("Check-in is not yet available. You can check in {$earlyCheckInHour} hours before the event starts.");

            $now->gt($eventStartTime->copy()->addMinutes(30)) && throw new MessageError('Event has already started. Check-in is no longer available.');

            $eventParticipant->participant ?? throw new MessageError('Participant not found.');

            $eventParticipant->event ?? throw new MessageError('Event not found.');

            $eventParticipant->checked_in_at && throw new MessageError('Participant has already checked in.');

            return match ($eventParticipant->status) {
                'approve' => $this->handleApprovedParticipant($eventParticipant),
                'pending' => throw new MessageError('Participant approval is pending.'),
                'reject', 'block' => throw new MessageError('Participant is not allowed to attend.'),
                default => throw new MessageError('Unknown participant status.'),
            };
        } catch (MessageError $e) {
            return $e->render($request);
        } catch (Exception $e) {
            return response()->json(['message' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }

    private function handleApprovedParticipant($eventParticipant)
    {
        try {
            $eventParticipant->update(['checked_in_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Participant is checked in.',
                'checked_in_at' => Carbon::parse($eventParticipant->checked_in_at)->format('H:i')
            ], 200);
        } catch (Exception $e) {
            throw new MessageError('Failed to update check-in time: ' . $e->getMessage());
        }
    }

    public function updateParticipantStatus(Request $request)
    {
        $request->validate([
            'event_id'       => 'required',
            'participant_id' => 'required|array',
            'status'         => 'required|in:approve,pending,reject,block'
        ]);

        $query = EventParticipant::where('event_id', $request->event_id);

        $updateAllParticipants = in_array('participants', $request->participant_id);

        $affected = $updateAllParticipants
            ? $query->update(['status' => $request->status])
            : $query->whereIn('participant_id', $request->participant_id)
            ->update(['status' => $request->status]);

        $affected === 0 && throw new MessageError('No participants found.');

        $message = $updateAllParticipants ? "All participants' status updated" : "{$affected} participants status updated";

        $updatedParticipants = EventParticipant::where('event_id', $request->event_id)
            ->when(!$updateAllParticipants, function ($query) use ($request) {
                return $query->whereIn('participant_id', $request->participant_id);
            })
            ->with('participant', 'event')
            ->get();

        // $batch = Bus::batch([])->allowFailures()->dispatch();

        foreach ($updatedParticipants as $eventParticipant) {
            // $batch->add(new SendStatusEmail($eventParticipant, $request->status));
            new SendStatusEmail($eventParticipant, $request->status);
        }
        return response()->json([
            'success' => true,
            'message' => $message . " successfully to {$request->status}. Emails will be sent shortly.",
        ], 200);
    }
}
