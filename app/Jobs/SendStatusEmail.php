<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ParticipantStatusEmail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;

class SendStatusEmail implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventParticipant;
    protected $status;
    public $tries = 3;
    public $maxExceptions = 3;
    public $backoff = [10, 30, 60];

    public function __construct(EventParticipant $eventParticipant, $status)
    {
        $this->eventParticipant = $eventParticipant;
        $this->status = $status;
    }

    public function handle()
    {
        try {
            $participant = $this->eventParticipant->participant;
            $event = $this->eventParticipant->event;

            $qrCodeSvg = null;
            if ($this->status === 'approve') {
                $qrCodeSvg = QrCode::size(300)->generate($this->eventParticipant->token);
            }

            Mail::to($participant->email)
                ->send(new ParticipantStatusEmail($participant, $event, $this->status, $qrCodeSvg));

            Log::info("Status email sent successfully for EventParticipant ID: {$this->eventParticipant->id}");
        } catch (\Exception $e) {
            Log::error("Error sending status email for EventParticipant ID: {$this->eventParticipant->id}", []);

            if ($this->attempts() >= $this->tries) {
                Log::critical("Failed to send status email after {$this->tries} attempts for EventParticipant ID: {$this->eventParticipant->id}");
            }
            throw $e;
        }
    }
}
