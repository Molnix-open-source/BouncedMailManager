<?php

use Molnix\BouncedMailManager\BounceManager;
use Molnix\BouncedMailManager\Clients\ImapClient;

include '../vendor/autoload.php';
$config = require('_config.php');
$manager = new BounceManager($config['host'], $config['port'], $config['username'], $config['password']);
// $manager->setClient(new ImapClient($config['host'], $config['port'], $config['username'], $config['password']));
print_r($manager->toArray(-1));
