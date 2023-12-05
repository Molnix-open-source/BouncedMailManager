<?php

namespace Molnix\BouncedMailManager\Clients;

use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Molnix\BouncedMailManager\Clients\ClientContract;

class ImapClient implements ClientContract
{
    protected $config;
    protected $options;

    public function __construct(string $host, int $port = 993, string $username, string $password, array $options = [])
    {
        $this->options = $options;
        $this->config = [
            'host'          => $host,
            'port'          => (int) $port,
            'username'      => $username,
            'password'      => $password,
        ];

        foreach($options as $key => $value) {
            $this->config[$key] = $value;
        }
    }

    public function getClient(): Client
    {
        $cm = new ClientManager([]);
        return $cm->make($this->config);
    }
}
