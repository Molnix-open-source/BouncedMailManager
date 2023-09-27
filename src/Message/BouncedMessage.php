<?php

declare(strict_types=1);

namespace Molnix\BouncedMailManager\Message;

use Ddeboer\Imap\Message;

class BouncedMessage
{
    protected $message;

    public $reason;
    public $headers;
    public $subject;

    public function __construct(Message $message)
    {
        $this->message = $message;

        $this->subject = $this->message->getSubject();
        $this->reason  = $this->getReason();
        $this->headers = new Header($this->message);
    }

    protected function getReason(): string
    {
        $body = $this->message->getBodyText();

        if (!$body && '' === $body) {
            $body = $this->message->getBodyHtml();
        }

        if (!$body && '' === $body) {
            return null;
        }

        return Parser::parse($body);
    }
}
