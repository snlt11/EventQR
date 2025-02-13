<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Participant;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Log;

class SendInvitationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $participant;
    protected $qrCodeSvg;

    public function __construct(Participant $participant, $qrCodeSvg)
    {
        $this->participant = $participant;
        $this->qrCodeSvg = $qrCodeSvg;
    }

    public function handle()
    {
        Log::info('Starting to process SendInvitationEmail job for participant: ' . $this->participant->id);

        try {
            Mail::to($this->participant->email)->send(new InvitationMail($this->participant, $this->qrCodeSvg));
            Log::info('Successfully sent invitation email to participant: ' . $this->participant->id);
        } catch (\Exception $e) {
            Log::error('Failed to send invitation email to participant: ' . $this->participant->id . '. Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
