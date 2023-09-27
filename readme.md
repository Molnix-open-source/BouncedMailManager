
  
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

| Variable | Description |
| ------------- | ------- |
| `BOUNCEMAIL_HOST` | IMAP host url, default: **MAIL_HOST** from .env |
| `BOUNCEMAIL_PORT` | IMAP port, default: **993**|
| `BOUNCEMAIL_USERNAME` | IMAP username, default: **MAIL_USERNAME** from .env|
| `BOUNCEMAIL_PASSWORD` | IMAP password, default: **MAIL_PASSWORD** from .env|
| `BOUNCEMAIL_MAILBOX` | IMAP password, default: **INBOX**|
| `BOUNCEMAIL_DELETE_MODE` | IMAP password, default: **true**|

#### Configuration

  The package will automatically register a service provider. You can publish for further customization.

| Tag | Description |
| ------------- | ------- |
| `config` | Configuration |
| `views` | Notification email markdown |
| `translations` | Bounce reason translations |

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
$messages = (new  BounceManager(
string  $host, string  $port = '993', string  $username, string  $password, string  $mailbox = 'INBOX'
))->get()

```