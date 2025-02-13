<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Support\Str;
use App\Models\Participant;
use Illuminate\Http\Request;
use App\Jobs\SendInvitationEmail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FormController extends Controller
{
    public function publishForm(Request $request)
    {
        try {
            $formData = $request->all();

            $form = Form::create([
                'title' => $formData['title'],
                'questions' => json_encode($formData['questions']),
                'creator_id' => auth()->id() ?? null,
                'is_published' => true,
            ]);

            return response()->json([
                'success' => true,
                'url' => url("/form/{$form->id}"),
            ]);
        } catch (\Exception $e) {
            logger()->error('Error in publishForm: ' . $e->getMessage());
            logger()->error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the form.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showPublishedForm($id)
    {
        $form = Form::findOrFail($id);
        $decodedForm = [
            'id' => $form->id,
            'title' => $form->title,
            'questions' => json_decode($form->questions, true)
        ];
        return view('published-form', ['formData' => json_encode($decodedForm)]);
    }

    public function submitForm(Request $request, $id)
    {
        try {
            $form = Form::findOrFail($id);
            $data = $request->all();

            $validated = $request->validate([
                'name'  => ['required', 'string', 'max:255'],
                'email' => ['required', 'email'],
            ]);

            $participantData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'token' => Str::uuid(),
                'form_id' => $form->id,
            ];

            unset($data['name'], $data['email']);

            $participantData['info'] = json_encode($data);

            $participant = Participant::updateOrCreate(
                ['email' => $participantData['email'], 'form_id' => $form->id],
                $participantData
            );

            $checkInUrl = url("/api/checkin/{$participant->token}");
            $qrCodeSvg = QrCode::size(300)->generate($checkInUrl);

            dispatch(new SendInvitationEmail($participant, $qrCodeSvg))->onQueue('default');

            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully and invitation email queued',
                'id' => $participant->id
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            logger()->error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            logger()->error('Error submitting form: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error submitting form',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
