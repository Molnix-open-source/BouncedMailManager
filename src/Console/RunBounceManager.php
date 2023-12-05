<?php

namespace Molnix\BouncedMailManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Molnix\BouncedMailManager\BounceManager;
use Molnix\BouncedMailManager\Clients\ImapClient;
use Molnix\BouncedMailManager\Clients\O365Client;
use Molnix\BouncedMailManager\Mail\BounceNotificationMail;

class RunBounceManager extends Command
{
    protected $signature = 'bouncemanager:run 
                            {days=1 : To be used in imap search since today - days. -1 for full}
                            {--Q|queue : Whether the email should be queued}';

    protected $description = 'Run bounce manager';

    public function handle()
    {

        $days = (int) $this->argument('days');
        $shouldQueue = $this->option('queue');
        $bounceManager = (new BounceManager())
        ->setDaysFrom($days);

        switch(config('bouncemanager.type')) {
            case 'o365':
                $bounceManager->setClient(new O365Client(
                    config('bouncemanager.username'),
                    config('bouncemanager.azure_tenant_id'),
                    config('bouncemanager.oauth_client_id'),
                    config('bouncemanager.oauth_secret'),
                ));
                break;
            case 'imap':
                $bounceManager->setClient(
                    new ImapClient(
                        config('bouncemanager.host'),
                        config('bouncemanager.port'),
                        config('bouncemanager.username'),
                        config('bouncemanager.password'),
                        config('bouncemanager.mailbox'),
                        config('bouncemanager.options')
                    )
                );
                break;
        }



        if(config('bouncemanager.delete_mode')) {
            $bounceManager->enableDeleteMode();
        }

        $notifications = $bounceManager->toArray();


        foreach($notifications as $to => $bounceContent) {
            $user = DB::table(config('bouncemanager.usertable'))->where('email', $to)->first();

            $locale = 'en';
            if($user && $user->locale) {
                $locale = $user->locale;
            }
            $mail = Mail::to($to)->locale($locale);

            $bcc = $this->getBCC();
            if(sizeof($bcc)) {
                $mail->bcc($bcc);
            }

            if($shouldQueue) {
                $mail->queue(new BounceNotificationMail($bounceContent));
            } else {
                $mail->send(new BounceNotificationMail($bounceContent));
            }

        }



    }
    protected function getBCC()
    {
        if(!is_array(config('bouncemanager.bcc'))) {
            return [config('bouncemanager.bcc')];
        }
        return config('bouncemanager.bcc');
    }
}
