# Bounced mail manager

A package can read IMAP and parse mails to check if they are bounced emails. **When used with laravel, can notifiy the original sender.**

## Installation:

```bash
composer require molnix/bounced-mail-manager
```

Add the custom headers created by the `getCustomHeaders` method to outgoing mails.

```php
Molnix\BouncedMailManager\Message\Header::getCustomHeaders(
string  $sender, // Sender email to send notificaitons
string  $sentTo, // send to email
string  $subject ='' optional subject
): array
```

### Laravel

Package can be customized with optional env variables

| Variable                     | Description                                                         |
| ---------------------------- | ------------------------------------------------------------------- | 
| `BOUNCEMAIL_HOST`            | IMAP host url, default: **MAIL_HOST** from .env                     |    
| `BOUNCEMAIL_PORT`            | IMAP port, default: **993**                                         |
| `BOUNCEMAIL_USERNAME`        | IMAP username, default: **MAIL_USERNAME** from .env                 |
| `BOUNCEMAIL_PASSWORD`        | IMAP password, default: **MAIL_PASSWORD** from .env                 |
| `BOUNCEMAIL_MAILBOX`         | Mailox name, default: **INBOX**                                     |
| `BOUNCEMAIL_DELETE_MODE`     | False will use _read/unread_ instead of deleting, default: **true** |
| `BOUNCEMAIL_TYPE`            | Mailbox type: null. imap,o365 , default: **imap**                   |                           
| `BOUNCEMAIL_OAUTH_CLIENT_ID` | Oauth client id , default: **null**                                 |
| `BOUNCEMAIL_OAUTH_SECRET`    | Oauth secret , default: **null**                                    |
| `BOUNCEMAIL_AZURE_TENANT_ID` | Azure tenant id if using office 365 , default: **null**             |

#### Configuration

The package will automatically register a service provider. You can publish for further customization.

| Tag            | Description                 |
| -------------- | --------------------------- |
| `config`       | Configuration               |
| `views`        | Notification email markdown |
| `translations` | Bounce reason translations  |

Optionally publish files if further customization is required.

```bash

php  artisan  vendor:publish  --provider="Molnix\BouncedMailManager\BounceManagerServiceProvider"  --tag="config"
php  artisan  vendor:publish  --provider="Molnix\BouncedMailManager\BounceManagerServiceProvider"  --tag="views"
php  artisan  vendor:publish  --provider="Molnix\BouncedMailManager\BounceManagerServiceProvider"  --tag="translations"
```

### Usage

Use `Molnix\BouncedMailManager\Traits\BounceMailHeaders` trait in Mailable to add headers to outgoing headers.

Option 1: With setup

```php
// PostMail
use BounceMailHeaders;
public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->setupBounceManager();
    }
public function build(){
    $this->subject('Comment added')
                ->markdown('emails.comment')
                ->addBounceManagerHeaders();
}
```

Option 2: Without setup

```php
// PostMail
use BounceMailHeaders;
public function __construct(Comment $comment, $sender)
    {
        $this->comment = $comment;
        $this->sender = $sender;
    }
public function build(){
    $this->subject('Comment added')
                ->markdown('emails.comment')
                ->addBounceManagerHeaders($this->sender);
}
```

Add this command to the scheduler.

```bash
php artisan bouncemanager:run
```

## PHP

```php

Check the [Wiki](https://github.com/Molnix-open-source/BouncedMailManager/wiki/Usage) for usage instructions.

$messages = (new  BounceManager(
string  $host, string  $port = '993', string  $username, string  $password, string  $mailbox = 'INBOX'
))->get()

```

Made with ❤ in Finland by [Molnix](https://molnix.com) and [Webbhuset](https://webbhuset.fi)
