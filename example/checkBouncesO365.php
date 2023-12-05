<?php

use Molnix\BouncedMailManager\BounceManager;
use Molnix\BouncedMailManager\Clients\O365Client;

include '../vendor/autoload.php';
$config = require('_config.php');
$manager = new BounceManager();
$manager->setClient(new O365Client($config['username'], $config['tenant_id'], $config['client_id'], $config['client_secret']));

print_r($manager->toArray(-1));
