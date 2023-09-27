<?php

namespace Molnix\BouncedMailManager\Traits;

use Illuminate\Contracts\Mail\Mailable;
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
        if(Auth::check() && Auth::user()->email) {
            $this->bounceSender = Auth::user()->email;
        }
        return $this;
    }


    /**
     * Add headers to the message, optionally pass sender email (not reuired if setup is called)
     *
     * @param string|null $sender
     * @return Mailable
     */
    public function addBounceManagerHeaders(string $sender = null): Mailable
    {
        $senderAddress = $sender ? $sender : $this->bounceSender;
        if(!$senderAddress) {
            return $this;
        }
        return $this->withSwiftMessage(function ($message) use ($senderAddress) {
            $sentTo = implode(',', array_keys($message->getTo()));
            foreach(Header::getCustomHeaders($senderAddress, $sentTo, $message->getSubject()) as $name => $value) {
                $message->getHeaders()->addTextHeader(
                    $name,
                    $value
                );
            }
        });
    }
}
