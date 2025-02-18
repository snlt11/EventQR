<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\EventParticipant;
use Exception;
use App\Helper\MessageError;
use App\Jobs\SendStatusEmail;
use App\Models\Event;
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

        $batch = Bus::batch([])->allowFailures()->dispatch();

        foreach ($updatedParticipants as $eventParticipant) {
            $batch->add(new SendStatusEmail($eventParticipant, $request->status));
        }
        return response()->json([
            'success' => true,
            'message' => $message . " successfully to {$request->status}. Emails will be sent shortly.",
        ], 200);
    }

    public function listOfParticipants(Request $request, $eventId)
    {
        $event = Event::find($eventId);

        $event || throw new MessageError('Event not found.');

        $query = EventParticipant::where('event_id', $eventId)
            ->with(['participant:id,name,email', 'event:id,title']);

        $filters = [
            'name' => fn($query, $value) => $query->whereHas('participant', function ($q) use ($value) {
                $q->where('name', 'like', "%$value%");
            }),
            'email' => fn($query, $value) => $query->whereHas('participant', function ($q) use ($value) {
                $q->where('email', 'like', "%$value%");
            }),
            'status' => fn($query, $value) => $query->where('status', $value),
            'checked_in' => fn($query, $value) => $value === 'true' ? $query->whereNotNull('checked_in_at') : $query->whereNull('checked_in_at'),
        ];

        foreach ($filters as $param => $filterFunction) {
            if ($request->has($param)) {
                $filterFunction($query, $request->input($param));
            }
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $participants = $query->select([
            'event_participants.id',
            'event_participants.participant_id',
            'event_participants.event_id',
            'event_participants.status',
            'event_participants.checked_in_at',
        ])->paginate($perPage, ['*'], 'page', $page);

        $participantsInfo = $participants->map(function ($eventParticipant) {
            return [
                'id' => $eventParticipant->id,
                'name' => $eventParticipant->participant->name,
                'email' => $eventParticipant->participant->email,
                'status' => $eventParticipant->status,
                'checked_in_at' => $eventParticipant->checked_in_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => $participants->isEmpty() ? 'No participants found' : 'Participants retrieved successfully',
            'event_title' => $event->title,
            'data' => $participantsInfo,
            'total_count' => $participants->count(),
            'filtered_count' => $participants->total(),
            'current_page' => $participants->currentPage(),
            'per_page' => $participants->perPage(),
            'last_page' => $participants->lastPage(),
        ], 200);
    }
}
