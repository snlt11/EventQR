<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Participant;
use App\Models\EventParticipant;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Helper\MessageError;

class EventController extends Controller
{
    public function createEvent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'questions' => 'required|array',
                'start_datetime' => 'required|date|after_or_equal:now',
                'end_datetime' => 'nullable|date|after_or_equal:start_datetime',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $formData = $validator->validated();
            $questions = is_string($formData['questions']) ? json_decode($formData['questions'], true) : $formData['questions'];
            $event = Event::create([
                'title' => $formData['title'],
                'description' => $formData['description'] ?? null,
                'questions' => $questions,
                'creator_id' => Auth::id(),
                'is_published' => false,
                'start_datetime' => $formData['start_datetime'] ?? null,
                'end_datetime' => $formData['end_datetime'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => [
                    'event_id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'questions' => $questions,
                    'start_datetime' => $event->start_datetime,
                    'end_datetime' => $event->end_datetime,
                    'is_published' => $event->is_published,
                ],
            ], 201);
        } catch (\Exception $e) {
            logger()->error('Error in createForm: ' . $e->getMessage());
            logger()->error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the form.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleEventPublishStatus($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);

            $event->is_published = !$event->is_published;
            $event->save();

            $status = $event->is_published ? 'published' : 'unpublished';

            return response()->json([
                'success' => true,
                'message' => "{$event->title} {$status} successfully",
                'url' => url("/event/{$event->id}"),
            ], 200);
        } catch (\Exception $e) {
            logger()->error('Error in toggleEventPublishStatus: ' . $e->getMessage());
            logger()->error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while toggling the event publish status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listEvents()
    {
        try {

            $events = Event::whereNull('deleted_at')->get();

            return response()->json([
                'success' => true,
                'message' => 'Events retrieved successfully',
                'data' => $events,
            ], 200);
        } catch (\Exception $e) {
            logger()->error('Error in listEvents: ' . $e->getMessage());
            logger()->error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving events.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitEventForm(Request $request, $eventId)
    {
        try {

            $event = Event::findOrFail($eventId);

            $event->is_published || throw new MessageError('Event is not published.');

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email'],
            ]);

            $participantData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            $existingEventParticipant = EventParticipant::whereHas('participant', function ($query) use ($participantData) {
                $query->where('email', $participantData['email']);
            })->where('event_id', $event->id)->first();

            $existingEventParticipant && throw new MessageError('You have already registered for this event.');

            $formData = $request->except(['name', 'email']);
            $answers = is_string($formData) ? json_decode($formData, true) : $formData;

            $participant = Participant::updateOrCreate(
                ['email' => $participantData['email']],
                $participantData
            );

            $eventParticipant = EventParticipant::updateOrCreate(
                ['event_id' => $event->id, 'participant_id' => $participant->id],
                [
                    'token' => $this->generateToken($event->id, $participant->id),
                    'status' => 'pending',
                    'answers' => $answers
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Event form submitted successfully',
                'token' => $eventParticipant->token,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new MessageError('Validation error: ' . json_encode($e->errors()), 422, $e);
        } catch (MessageError $e) {
            return $e->render($request);
        } catch (\Exception $e) {
            logger()->error('Error submitting event form: ' . $e->getMessage());
            throw new MessageError('Error submitting event form');
        }
    }

    private function generateToken($eventId, $participantId)
    {
        $key = config('keys.token_key');
        $payload = [
            'event_id' => $eventId,
            'participant_id' => $participantId,
            'uuid' => Str::uuid()->toString(),
        ];

        return JWT::encode($payload, $key, 'HS256');
    }

    public function changeEventStatus($eventId, $status)
    {
        try {
            $event = EventParticipant::where('event_id', $eventId)->first();

            $event && throw new MessageError('Event not found');

            $event->status = $status;
            $event->save();

            return response()->json([
                'success' => true,
                'message' => "Event status changed to $status successfully",
            ], 200);
        } catch (MessageError $e) {
            return $e->render(request());
        } catch (\Exception $e) {
            logger()->error('Error in changeEventStatus: ' . $e->getMessage());
            throw new MessageError('An error occurred while changing event status');
        }
    }
}
