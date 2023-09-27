<?php

namespace Molnix\BouncedMailManager\Message;

use Ddeboer\Imap\Message;

class Header
{
    public const SENDER_HEADER = 'bmm-sender';
    public const SENT_TO_HEADER = 'bmm-sent-to';
    public const SUBJECT_HEADER = 'bmm-subject';
    public const VERIFY_HEADER = 'bmm-check';

    protected $message;

    public $sender = null;
    public $sentTo = null;
    public $subject = null;
    public $verified = false;


    /**
     * Mail header object
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->sender = $this->getHeader(Header::SENDER_HEADER);
        $this->sentTo = $this->getHeader(Header::SENT_TO_HEADER);
        $this->subject = $this->getHeader(Header::SUBJECT_HEADER);
        $verifyHash = $this->getHeader(self::VERIFY_HEADER);
        $this->verified = password_verify($this->sender.$this->sentTo, $verifyHash);
    }

    /**
     * Returns array of custom headers to be injected into the sending mail
     *
     * @param string $sender Sender email, can be used to send bounce notification
     * @param string $sentTo Email sent to
     * @param string $subject Optional subject, or identifier
     * @return array
     */
    public static function getCustomHeaders(string $sender, string $sentTo, string $subject = ''): array
    {
        return [
            self::SENDER_HEADER => $sender,
            self::SENT_TO_HEADER => $sentTo,
            self::SUBJECT_HEADER => $subject,
            self::VERIFY_HEADER => password_hash($sender.$sentTo, PASSWORD_BCRYPT)
        ];
    }

    /**
     * Get header from the mail
     *
     * @param string $key
     * @return string
     */
    protected function getHeader(string $key): string
    {
        $body = $this->message->getRawMessage();
        return (preg_match("/^$key:.*/m", $body, $match)) ? trim(str_replace("$key:", '', $match[0])) : '';
    }

}
