<?php

namespace Molnix\BouncedMailManager\Clients;

use GuzzleHttp\Client as GuzzleHttpClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Molnix\BouncedMailManager\Clients\ClientContract;

class O365Client implements ClientContract
{
    protected $config;

    public function __construct(string $username, string $tenantId, string $clientId, string $clientSecret)
    {

        $httpClient = new GuzzleHttpClient();
        $oauthResponse = $httpClient->post('https://login.microsoftonline.com/' . $tenantId . '/oauth2/v2.0/token', [
            'form_params' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'https://outlook.office365.com/.default',
                'grant_type' => 'client_credentials',
                ]
        ]);
        $responseData = json_decode($oauthResponse->getBody());
        $this->config = [
            'host'          => 'outlook.office365.com',
            'port'          => 993,
            'username'      => $username,
            'password'      => $responseData->access_token,
            'authentication' => 'oauth',
        ];


    }

    public function getClient(): Client
    {
        $cm = new ClientManager([]);
        return $cm->make($this->config);
    }
}
