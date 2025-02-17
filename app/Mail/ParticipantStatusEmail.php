<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Participant;
use App\Models\Event;

class ParticipantStatusEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $participant;
    public $event;
    public $status;
    public $qrCodeSvg;

    public function __construct(Participant $participant, Event $event, $status, $qrCodeSvg)
    {
        $this->participant = $participant;
        $this->event = $event;
        $this->status = $status;
        $this->qrCodeSvg = $qrCodeSvg;
    }

    public function build()
    {
        return $this->view('emails.sendEmail')
                    ->with([
                        'participant' => $this->participant,
                        'event' => $this->event,
                        'status' => $this->status,
                        'qrCodeSvg' => $this->qrCodeSvg
                    ]);
    }
}
