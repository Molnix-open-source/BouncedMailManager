<?php

return [
    /*
    |--------------------------------------------------------------------
    | Scan IMAP box for bounced mails and send notification to the sender
    |--------------------------------------------------------------------
    |
    |
    */

    /**
     * Type of mailbox, imap or o365
     */
    'type' => env('BOUNCEMAIL_TYPE', env('BOUNCEMAIL_TYPE', 'imap')),

    /**
     * Oauth client id
     */
    'oauth_client_id' => env('BOUNCEMAIL_OAUTH_CLIENT_ID', env('BOUNCEMAIL_CLIENT_ID')),

    /**
     * Oauth secret
     */
    'oauth_secret' => env('BOUNCEMAIL_OAUTH_SECRET', env('BOUNCEMAIL_OAUTH_SECRET')),

    /**
     * Azure tenant id
     */
    'azure_tenant_id' => env('BOUNCEMAIL_AZURE_TENANT_ID', env('BOUNCEMAIL_AZURE_TENANT_ID')),

    /**
     * IMAP host url
     */
    'host' => env('BOUNCEMAIL_HOST', env('MAIL_HOST')),

    /**
     * IMAP port
     */
    'port' => env('BOUNCEMAIL_PORT', '993'),

    /**
     * IMAP username
     */
    'username' => env('BOUNCEMAIL_USERNAME', env('MAIL_USERNAME')),

    /**
     * IMAP password
     */
    'password' => env('BOUNCEMAIL_PASSWORD', env('MAIL_PASSWORD')),

    /**
     * IMAP inbox name
     */
    'mailbox' => env('BOUNCEMAIL_MAILBOX', 'INBOX'),

    /**
     * Enabling this will delete the email after processing. Default will mark as read
     */
    'delete_mode' => env('BOUNCEMAIL_DELETE_MODE', false),

    /**
     * BCC emails to send notifications to
     */
    'bcc' => [],

    /**
     * Users table to lookup locale, eng will be used by default
     */
    'usertable' =>   env('BOUNCEMAIL_USER_TABLE', 'users'),

    /**
     * Additional options
     */
    'options' => [],
];
