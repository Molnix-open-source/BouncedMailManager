<?php

namespace Molnix\BouncedMailManager\Traits;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\Auth;
use Molnix\BouncedMailManager\Message\Header;

trait BounceMailHeaders
{
    protected $bounceSender = null;

    /**
     * Setup sender email using the auth.
     *
     * @return Mailable
     */
    public function setupBounceManager(): Mailable
    {
        if (Auth::check() && Auth::user()->email) {
            $this->bounceSender = Auth::user()->email;
        }
        return $this;
    }

    public function headers(): Headers
    {
        if (!$this->bounceSender) {
            return new Headers();
        }
        $sentTo = collect($this->to)->pluck('address')->implode(',');
        return new Headers(
            text: Header::getCustomHeaders($this->bounceSender, $sentTo, $this->subject)
        );
    }


    /**
     * Add headers to the message, optionally pass sender email (not reuired if setup is called)
     *
     * @param string|null $sender
     * @return Mailable
     */
    public function addBounceManagerHeaders(string $sender = null): Mailable
    {
        if ($sender) {
            $this->bounceSender = $sender;
        }

        return $this;
    }
}
