<?php

namespace Molnix\BouncedMailManager;

use Carbon\Carbon;
use Molnix\BouncedMailManager\Clients\ImapClient;
use Molnix\BouncedMailManager\Clients\ClientContract;
use Molnix\BouncedMailManager\Message\BouncedMessage;
use Molnix\BouncedMailManager\Exceptions\BounceManagerException;

class BounceManager
{
    protected $config;
    protected $daysFrom;
    protected $deleteMode = false;
    protected $client;
    protected $mailbox;
    protected $options;

    /**
     * Creates new instance of bounce manager
     *
     * @param string|null|null $host
     * @param string $port
     * @param string|null|null $username
     * @param string|null|null $password
     * @param string $mailbox
     * @param array $options
     */
    public function __construct(string|null $host = null, string $port = '993', string|null $username = null, string|null $password = null, string $mailbox = 'INBOX', array $options = [])
    {
        $this->setMailbox($mailbox);

        if($host) {
            $this->setClient(new ImapClient($host, (int) $port, $username, $password, $options));
        }

    }

    /**
     * Sets client, example Imap or O365 or custom
     *
     * @param ClientContract $client
     * @return BounceManager
     */
    public function setClient(ClientContract $client): BounceManager
    {
        $this->client = $client->getClient();
        $this->client->connect();
        return $this;
    }

    /**
     * Set mailbox to parse
     *
     * @param string $mailbox
     * @return BounceManager
     */
    public function setMailbox(string $mailbox = 'INBOX'): BounceManager
    {
        $this->mailbox = $mailbox;
        return $this;
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
    public function get(int $daysFrom = null): array
    {


        return $this->parse($daysFrom);
    }

    /**
     * Get bounces as array
     *
     * @param integer|null $daysFrom
     * @return array
     */
    public function toArray(int $daysFrom = null): array
    {
        $messages = $this->parse($daysFrom);
        $bounces = [];
        foreach($messages as $message) {
            if(!isset($bounces[$message->headers->sender])) {
                $bounces[$message->headers->sender] = [];
            }

            $bounces[$message->headers->sender][] = [
                'sent_to' => $message->headers->sentTo,
                'subject' => $message->headers->subject,
                'reason' => $message->reason,
            ];
        }
        return $bounces;
    }

    /**
     * Parse bounces from mailbox
     *
     * @param integer|null $daysFrom
     * @return array
     */
    protected function parse(int $daysFrom = null): array
    {
        if(!$this->client) {
            throw new BounceManagerException('Client not created, use setClient() or provide with constructor', 1);
        }
        $daysFrom = $daysFrom ? $daysFrom : $this->daysFrom;
        $mailbox = $this->client->getFolderByName($this->mailbox);
        $folder = $mailbox->query()->leaveUnread();
        $messages = ($daysFrom == -1) ? $folder : $folder->since(Carbon::now()->subDays($daysFrom));

        if (!$this->deleteMode) {
            $messages->unseen();
        }

        $bounces = [];
        foreach ($messages->get() as $message) {
            print_r($message->getRawBody());

            $bouncedMessage = new BouncedMessage($message);

            if (!$bouncedMessage->headers->verified) {
                continue;
            }

            if (!$bouncedMessage->reason || $bouncedMessage->reason == '') {
                continue;
            }



            $bounces[] = $bouncedMessage;
            if($this->deleteMode) {
                $message->delete();
            } else {
                $message->setFlag('Seen');
            }
        }
        return $bounces;
    }


}
