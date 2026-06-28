<?php

namespace App\Listeners;

use App\Events\DemoRequestApproved;
use Illuminate\Support\Facades\Mail;
use App\Mail\DemoRequestApprovedMail;

class SendDemoRequestApprovedEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DemoRequestApproved $event)
    {
        Mail::to($event->demoRequest->email)
            ->send(
                new DemoRequestApprovedMail(
                    $event->demoRequest,
                    $event->tempPassword
                )
            );
    }
}
