<?php

namespace Molnix\BouncedMailManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Molnix\BouncedMailManager\BounceManager;
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
        $messages = (new BounceManager(
            config('bouncemanager.host'),
            config('bouncemanager.port'),
            config('bouncemanager.username'),
            config('bouncemanager.password'),
            config('bouncemanager.mailbox'),
        ))
        ->setDaysFrom($days);

        if(config('bouncemanager.delete_mode')) {
            $messages->enableDeleteMode();
        }
        $messages = $messages->get();
        $notifications = [];
        foreach($messages as $message) {
            if(!isset($notifications[$message->headers->sender])) {
                $notifications[$message->headers->sender] = [];
            }

            $notifications[$message->headers->sender][] = [
                'sent_to' => $message->headers->sentTo,
                'subject' => $message->headers->subject,
                'reason' => $message->reason,
            ];
        }

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
