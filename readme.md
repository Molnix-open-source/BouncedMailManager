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

- #### Create instance
```php
use Molnix\BouncedMailManager\BounceManager;
$manager = new BounceManager(
    'imap.example.com', 
    '993', 
    'wind@example.com', 
    'emailpassword', 
    string $mailbox = 'INBOX', 
    array $options = [] // extra optional options
);
```
or

```php
use Molnix\BouncedMailManager\BounceManager;
use Molnix\BouncedMailManager\Clients\ImapClient;

$manager->setClient(new ImapClient(
    'imap.example.com', 
    '993', 
    'wind@example.com', 
    'emailpassword', 
    string $mailbox = 'INBOX', 
    array $options = [] // extra optional options
));

```

or Office 365 way

```php
use Molnix\BouncedMailManager\BounceManager;
use Molnix\BouncedMailManager\Clients\O365Client;

$manager = new BounceManager();
$manager->setClient(new O365Client('wind@example.com', $tenant_id, $client_id, $client_secret));

```

- #### Change mailbox
```php 
$manager->setMailbox('OtherMailbBoxName');
```

- #### Set period of days to parse
```php 
// Use -1 for all mails.
$manager->setDaysFrom(10); // Since last 10 days
```

- #### Set how to handle processed email management. Default is to mark the mail as seen. To use delete after process:
```php 
$manager->enableDeleteMode(); // Since last 10 days
```
- #### Get data
```php 

$manager->toArray(); // Returns simple array of bounces.
$manager->get(); // Returns array of bounces objects.
```

### Setup office 365 mailbox.


The setup needed to be done in two section, 
1. Azure web portal
2. Powershell

## Azure web portal
1. #### Register app in azure

 ![Screenshot from 2023-12-04 17-12-11](https://github.com/Molnix-open-source/BouncedMailManager/assets/15659965/ff71aacb-ea6b-424b-9ecd-b576134be470)

2. #### Setup Permissions, Click on `API permissions` -> `APIs my organization uses` tab -> serach `Office 365 Exchange Online` -> `Application permissions` -> `IMAP.AccessAsApp`

![Screenshot from 2023-12-04 17-20-23](https://github.com/Molnix-open-source/BouncedMailManager/assets/15659965/9fa418ea-d3b2-488c-a35f-b043709bc458)


3. #### `Grant admin consent` by clicking the button

![Screenshot from 2023-12-04 17-21-44](https://github.com/Molnix-open-source/BouncedMailManager/assets/15659965/ecd44fa4-0599-42ab-a0db-0db1043ca3bf)

4. ### Create app secret. Once created, Copy the value as it shows only once.

![Screenshot from 2023-12-04 17-25-49](https://github.com/Molnix-open-source/BouncedMailManager/assets/15659965/3cf2b84e-7337-4475-99cf-f5fb882d204a)

## Powershell

Run these commands in powershell in order one by one. Remember to replace values for `$AppId` with `Application (client) ID`, `$TenantId` with `Directory (tenant) ID`. These can be found in the app page in azure web. `Your email ID` with the mailbox email you grant access to.

```powershell

Install-Module -Name AzureAD
Install-Module -Name ExchangeOnlineManagement


$AppId = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
$TenantId = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"

Import-module AzureAD
Connect-AzureAd -Tenant $TenantId

($Principal = Get-AzureADServicePrincipal -filter "AppId eq '$AppId'")
$PrincipalId = $Principal.ObjectId


$DisplayName = "Bounce manager IMAP Access"

Import-module ExchangeOnlineManagement
Connect-ExchangeOnline -Organization $TenantId

New-ServicePrincipal -AppId $AppId -ServiceId $PrincipalId -DisplayName $DisplayName

Add-MailboxPermission -User $PrincipalId -AccessRights FullAccess -Identity "Your email ID"
```
Usage
```php
use Molnix\BouncedMailManager\BounceManager;
use Molnix\BouncedMailManager\Clients\O365;

$manager = new BounceManager();
$manager->setClient(new O365('wind@example.com', $tenant_id, $client_id, $client_secret));
$manager->get();
```

Made with ❤ in Finland by [Molnix](https://molnix.com) and [Webbhuset](https://webbhuset.fi)
