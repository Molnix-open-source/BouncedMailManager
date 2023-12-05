<?php

namespace Molnix\BouncedMailManager\Clients;

use Webklex\PHPIMAP\Client;

interface ClientContract
{
    public function getClient(): Client;
}
