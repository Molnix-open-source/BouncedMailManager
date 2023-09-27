<?php

namespace Molnix\BouncedMailManager\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class BounceNotificationMail extends Mailable
{
    use Queueable;

    public $bounces;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($bounces)
    {
        $this->bounces = $bounces;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('bouncemanager::messages.notification_subject'))
        ->markdown('bouncemanager::emails.notification')->with('bounces', $this->bounces);
    }
}
