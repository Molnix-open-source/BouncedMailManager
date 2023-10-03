<?php

namespace Molnix\BouncedMailManager;

use Ddeboer\Imap\Server;
use Molnix\BouncedMailManager\Message\BouncedMessage;

class BounceManager
{
    protected $config;
    protected $daysFrom;
    protected $deleteMode = false;
    protected $connection;

    /**
     * IMAP connection
     *
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $mailbox
     */
    public function __construct(string $host, string $port = '993', string $username, string $password, string $mailbox = 'INBOX')
    {
        $this->config = [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'mailbox' => $mailbox,
        ];
        $server = new Server($this->config['host'], $this->config['port']);
        $this->connection = $server->authenticate($this->config['username'], $this->config['password']);

    }

    /**
     * Set number of days to parse from, default is 1. Use -1 for all emails.
     *
     * @param integer $numOfDays
     * @return BounceManager
     */
    public function setDaysFrom(int $numOfDays): BounceManager
    {
        $this->daysFrom = $numOfDays;
        return $this;
    }

    /**
     * Sets to delete the email after processing. Default is set to mark as seen.
     *
     * @return BounceManager
     */
    public function enableDeleteMode(): BounceManager
    {
        $this->deleteMode = true;
        return $this;
    }

    /**
     * Get bounced emails
     *
     * @param integer|null $daysFrom
     * @return array
     */
    public function get(int $daysFrom = 1): array
    {
        $today = new \DateTimeImmutable();
        $mailbox = $this->connection->getMailbox($this->config['mailbox']);
        $dateFrom = $today->sub(new \DateInterval("P{$this->daysFrom}D"));

        $messages = ($this->daysFrom == -1) ?
        $mailbox->getMessages() :
        $mailbox->getMessages(
            new \Ddeboer\Imap\Search\Date\Since($dateFrom),
            \SORTARRIVAL,
            true
        );
        $bounces = [];
        foreach ($messages as $message) {
            if (!$this->deleteMode && $message->isSeen()) {
                continue;
            }

            $bouncedMessage = new BouncedMessage($message);

            if (!$bouncedMessage->headers->verified) {
                continue;
            }

            if (!$bouncedMessage->reason) {
                continue;
            }



            $bounces[] = $bouncedMessage;
            if($this->deleteMode) {
                $message->delete();
            } else {
                $message->markAsSeen();
            }
        }
        return $bounces;
    }


}
