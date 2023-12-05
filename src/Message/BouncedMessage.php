<?php

declare(strict_types=1);

namespace Molnix\BouncedMailManager\Message;

use Webklex\PHPIMAP\Message;

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
        $this->reason  = $this->setupReason();
        $this->headers = new Header($this->message);
    }

    public function getReason($lang = 'en'): string
    {
        $translations = require(__DIR__ . "/../../resources/lang/$lang/messages.php");
        return  isset($translations[$this->reason]) ? $translations[$this->reason] : $this->reason;
    }

    protected function setupReason(): string
    {
        $body = $this->message->getTextBody();

        if (!$body && '' === $body) {
            $body = $this->message->getHTMLBody();
        }

        if (!$body && '' === $body) {
            return null;
        }

        return Parser::parse($body);
    }
}
