<?php
namespace Molnix\BouncedMailManager\Message;

class Parser
{
    public const BOUNCE_REASON_DNS_UNKNOWN = 'bounce_reason_dns_unknown';
    public const BOUNCE_REASON_NOT_FOUND = 'bounce_reason_not_found';
    public const BOUNCE_REASON_FULL = 'bounce_reason_full';
    public const BOUNCE_REASON_INACTIVE = 'bounce_reason_inactive';
    public const BOUNCE_REASON_DELAYED = 'bounce_reason_delayed';
    public const BOUNCE_REASON_INTERNAL_ERROR = 'bounce_reason_internal_error';
    public const BOUNCE_REASON_ANTISPAM = 'bounce_reason_antispam';
    public const BOUNCE_REASON_CONTENT_REJECT = 'bounce_reason_content_reject';
    public const BOUNCE_REASON_REJECTED = 'bounce_reason_rejected';

    /**
     * Parsed provided body and return the bounce reason
     *
     * @param string $body
     * @return string
     */
    public static function parse(string $body) : string
    {
        
        /* rule: dns_unknown
        * sample:
        *   Technical details of permanent failure:
        *   DNS Error: Domain name not found
        */
        if (preg_match("/domain\s+name\s+not\s+found/i", $body, $match)) {
            return self::BOUNCE_REASON_DNS_UNKNOWN;
        }

        /* rule: unknown
        * sample:
        *   xxxxx@yourdomain.com
        *   no such address here
        */
        if (preg_match("/no\s+such\s+address\s+here/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* Gmail Bounce Error
        * rule: unknown
        * sample:
        *   Delivery to the following recipient failed permanently:
        *   xxxxx@yourdomain.com
        */
        if (
            strpos($body, 'Technical details of permanent failure') === false // if there are technical details, try another test-case
            &&
            preg_match("/Delivery to the following (?:recipient|recipients) failed permanently\X*?(\S+@\S+\w)/ui", $body, $match)
        ) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /*
        * rule: unknown
        * sample:
        * <xxxxx@yourdomain.com>: host mail-host[111.111.111.111]
            said: 550 5.1.1 This user does not exist
        */
        if (preg_match("/user.+?not\s+exist/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   <xxxxx@yourdomain.com>:
        *   111.111.111.111 does not like recipient.
        *   Remote host said: 550 User unknown
        */
        if (preg_match("/user\s+unknown/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *
        */
        if (preg_match("/unknown\s+user/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   <xxxxx@yourdomain.com>:
        *   Sorry, no mailbox here by that name. vpopmail (#5.1.1)
        */
        if (preg_match("/no\s+mailbox/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   xxxxx@yourdomain.com<br>
        *   local: Sorry, can't find user's mailbox. (#5.1.1)<br>
        */
        if (preg_match("/can't\s+find.*mailbox/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }
        /* rule: unknown
        * sample:
        *   ##########################################################
        *   #  This is an automated response from a mail delivery    #
        *   #  program.  Your message could not be delivered to      #
        *   #  the following address:                                #
        *   #                                                        #
        *   #      "|/usr/local/bin/mailfilt -u #dkms"               #
        *   #        (reason: Can't create output)                   #
        *   #        (expanded from: <xxxxx@yourdomain.com>)         #
        *   #                                                        #
        */
        if (preg_match("/Can't\s+create\s+output.*<(\S+@\S+\w)>/is", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   ????????????????:
        *   xxxxx@yourdomain.com : ????, ?????.
        */
        if (preg_match('/=D5=CA=BA=C5=B2=BB=B4=E6=D4=DA/i', $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   xxxxx@yourdomain.com
        *   Unrouteable address
        */
        if (preg_match("/Unrouteable\s+address/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   Delivery to the following recipients failed.
        *   xxxxx@yourdomain.com
        */
        if (preg_match("/delivery[^\n\r]+failed\S*\s+(\S+@\S+\w)\s/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   A message that you sent could not be delivered to one or more of its
        *   recipients. This is a permanent error. The following address(es) failed:
        *
        *   xxxxx@yourdomain.com
        *   unknown local-part "xxxxx" in domain "yourdomain.com"
        */
        if (preg_match("/unknown\s+local-part/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   <xxxxx@yourdomain.com>:
        *   111.111.111.11 does not like recipient.
        *   Remote host said: 550 Invalid recipient: <xxxxx@yourdomain.com>
        */
        if (preg_match("/Invalid.*(?:alias|account|recipient|address|email|mailbox|user).*<(\S+@\S+\w)>/is", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   Sent >>> RCPT TO: <xxxxx@yourdomain.com>
        *   Received <<< 550 xxxxx@yourdomain.com... No such user
        *
        *   Could not deliver mail to this user.
        *   xxxxx@yourdomain.com
        *   *****************     End of message     ***************
        */
        if (preg_match("/No\s+such.*(?:alias|account|recipient|address|email|mailbox|user).*<(\S+@\S+\w)>/is", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   Diagnostic-Code: X-Notes; Recipient user name info (a@b.c) not unique.  Several matches found in Domino Directory.
        */
        if (preg_match('/not unique.\s+Several matches found/i', $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: full
        * sample 1:
        *   <xxxxx@yourdomain.com>:
        *   This account is over quota and unable to receive mail.
        *   sample 2:
        *   <xxxxx@yourdomain.com>:
        *   Warning: undefined mail delivery mode: normal (ignored).
        *   The users mailfolder is over the allowed quota (size). (#5.2.2)
        */
        if (preg_match('/over.*quota/i', $body, $match)) {
            return self::BOUNCE_REASON_FULL;
        }

        /* rule: full
        * sample:
        *   ----- Transcript of session follows -----
        *   mail.local: /var/mail/2b/10/kellen.lee: Disc quota exceeded
        *   554 <xxxxx@yourdomain.com>... Service unavailable
        */
        if (preg_match("/quota\s+exceeded.*<(\S+@\S+\w)>/is", $body, $match)) {
            return self::BOUNCE_REASON_FULL;
        }

        /* rule: full
        * sample:
        *   Hi. This is the qmail-send program at 263.domain.com.
        *   <xxxxx@yourdomain.com>:
        *   - User disk quota exceeded. (#4.3.0)
        */
        if (preg_match("/quota\s+exceeded|message\s+size\s+exceeded/i", $body, $match)) {
            return self::BOUNCE_REASON_FULL;
        }

        /* rule: full
        * sample:
        *   xxxxx@yourdomain.com
        *   mailbox is full (MTA-imposed quota exceeded while writing to file /mbx201/mbx011/A100/09/35/A1000935772/mail/.inbox):
        */
        if (preg_match('/mailbox.*full/i', $body, $match)) {
            return self::BOUNCE_REASON_FULL;
        }

        /* rule: full
        * sample:
        *   The message to xxxxx@yourdomain.com is bounced because : Quota exceed the hard limit
        */
        if (preg_match("/The message to (\S+@\S+\w)\s.*bounce.*Quota exceed/i", $body, $match)) {
            return self::BOUNCE_REASON_FULL;
        }

        /* rule: full
        * sample:
        *   Message rejected. Not enough storage space in user's mailbox to accept message.
        */
        if (preg_match("/not\s+enough\s+storage\s+space/i", $body, $match)) {
            return self::BOUNCE_REASON_FULL;
        }

        /* rule: inactive
        * sample:
        *   xxxxx@yourdomain.com<br>
        *   553 user is inactive (eyou mta)
        */
        if (preg_match('/user is inactive/i', $body, $match)) {
            return self::BOUNCE_REASON_INACTIVE;
        }

        /*
        * <xxxxx@xxx.xxx> is restricted
        */
        if (preg_match("/(\S+@\S+\w).*n? is restricted/i", $body, $match)) {
            return self::BOUNCE_REASON_INACTIVE;
        }

        /* rule: inactive
        * sample:
        *   xxxxx@yourdomain.com [Inactive account]
        */
        if (preg_match('/inactive account/i', $body, $match)) {
            return self::BOUNCE_REASON_INACTIVE;
        }

        /*
        *<xxxxxx@xxxx.xxx>: host mx3.HOTMAIL.COM said: 550
        * Requested action not taken: mailbox unavailable (in reply to RCPT TO command)
        */
        if (preg_match("/<(\S+@\S+\w)>.*\n.*mailbox unavailable/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /*
        * rule: mailbox unknown;
        * sample:
        * xxxxx@yourdomain.com
        * 550-5.1.1 The email
        * account that you tried to reach does not exist. Please try 550-5.1.1
        * double-checking the recipient's email address for typos or 550-5.1.1
        * unnecessary spaces. Learn more at 550 5.1.1
        * http://support.google.com/mail/bin/answer.py?answer=6596 n7si4762785wiy.46
        * (in reply to RCPT TO command)
        */
        if (preg_match("/<(\S+@\S+\w)>.*\n?.*\n?.*account that you tried to reach does not exist/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: dns_unknown
        * sample1:
        *   Delivery to the following recipient failed permanently:
        *
        *     a@b.c
        *
        *   Technical details of permanent failure:
        *   TEMP_FAILURE: Could not initiate SMTP conversation with any hosts:
        *   [b.c (1): Connection timed out]
        * sample2:
        *   Delivery to the following recipient failed permanently:
        *
        *     a@b.c
        *
        *   Technical details of permanent failure:
        *   TEMP_FAILURE: Could not initiate SMTP conversation with any hosts:
        *   [pop.b.c (1): Connection dropped]
        */
        if (preg_match('/Technical details of permanent failure:\s+TEMP_FAILURE: Could not initiate SMTP conversation with any hosts/i', $body, $match)) {
            return self::BOUNCE_REASON_DNS_UNKNOWN;
        }

        /* rule: delayed
        * sample:
        *   Delivery to the following recipient has been delayed:
        *
        *     a@b.c
        *
        *   Message will be retried for 2 more day(s)
        *
        *   Technical details of temporary failure:
        *   TEMP_FAILURE: Could not initiate SMTP conversation with any hosts:
        *   [b.c (50): Connection timed out]
        */
        if (preg_match('/Technical details of temporary failure:\s+TEMP_FAILURE: Could not initiate SMTP conversation with any hosts/i', $body, $match)) {
            return self::BOUNCE_REASON_DELAYED;
        }

        /* rule: delayed
        * sample:
        *   Delivery to the following recipient has been delayed:
        *
        *     a@b.c
        *
        *   Message will be retried for 2 more day(s)
        *
        *   Technical details of temporary failure:
        *   TEMP_FAILURE: The recipient server did not accept our requests to connect. Learn more at ...
        *   [b.c (10): Connection dropped]
        */
        if (preg_match('/Technical details of temporary failure:\s+TEMP_FAILURE: The recipient server did not accept our requests to connect./i', $body, $match)) {
            return self::BOUNCE_REASON_DELAYED;
        }

        /* rule: internal_error
        * sample:
        *   <xxxxx@yourdomain.com>:
        *   Unable to switch to /var/vpopmail/domains/domain.com: input/output error. (#4.3.0)
        */
        if (preg_match("/input\/output error/i", $body, $match)) {
            return self::BOUNCE_REASON_INTERNAL_ERROR;
        }

        /* rule: internal_error
        * sample:
        *   <xxxxx@yourdomain.com>:
        *   can not open new email file errno=13 file=/home/vpopmail/domains/fromc.com/0/domain/Maildir/tmp/1155254417.28358.mx05,S=212350
        */
        if (\preg_match('/can not open new email file/i', $body, $match)) {
            return self::BOUNCE_REASON_INTERNAL_ERROR;
        }



        /* rule: block
        * sample:
        *   Delivery to the following recipient failed permanently:
        *     a@b.c
        *   Technical details of permanent failure:
        *   PERM_FAILURE: SMTP Error (state 9): 550 5.7.1 Your message (sent through 209.85.132.244) was blocked by ROTA DNSBL. If you are not a spammer, open http://www.rota.lv/DNSBL and follow instructions or call +371 7019029, or send an e-mail message from another address to dz@ROTA.lv with the blocked sender e-mail name.
        */
        if (preg_match("/Your message \([^)]+\) was blocked by|message has been blocked/i", $body, $match)) {
            return self::BOUNCE_REASON_ANTISPAM;
        }

        /* rule: content_reject
        * sample:
        *   Failed to deliver to '<a@b.c>'
        *   Messages without To: fields are not accepted here
        */
        if (preg_match("/Messages\s+without\s+\S+\s+fields\s+are\s+not\s+accepted\s+here/i", $body, $match)) {
            return self::BOUNCE_REASON_CONTENT_REJECT;
        }

        /* rule: inactive
        * sample:
        *   <xxxxx@yourdomain.com>:
        *   This address no longer accepts mail.
        */
        if (preg_match("/(?:alias|account|recipient|address|email|mailbox|user).*no\s+longer\s+accepts\s+mail/i", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }


        /* rule: unknown
        * sample:
        *   554 delivery error
        *   This user doesn't have a yahoo.com account
        */
        if (preg_match("/554.*delivery error.*this user.*doesn't have.*account/is", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   550 hotmail.com
        */
        if (preg_match('/550.*Requested.*action.*not.*taken:.*mailbox.*unavailable/is', $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   550 5.1.1 aim.com
        */
        if (\preg_match("/550 5\.1\.1.*Recipient address rejected/is", $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   550 .* (in reply to end of DATA command)
        */
        if (preg_match('/550.*in reply to end of DATA command/is', $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: unknown
        * sample:
        *   550 .* (in reply to RCPT TO command)
        */
        if (preg_match('/550.*in reply to RCPT TO command/is', $body, $match)) {
            return self::BOUNCE_REASON_NOT_FOUND;
        }

        /* rule: dns_unknown
        * sample:
        *    a@b.c:
        *      unrouteable mail domain "b.c"
        */
        if (preg_match("/unrouteable\s+mail\s+domain/i", $body, $match)) {
            return self::BOUNCE_REASON_DNS_UNKNOWN;
        }

        /* rule: rejected
        * sample:
        *      protection.outlook.com rejected your message to the following email addresses:
        */
        if (preg_match("/rejected\s+your\s+message\s+to\s+the\s+following\s+email\s+addresses/i", $body, $match)) {
            return self::BOUNCE_REASON_REJECTED;
        }

        return null;
    }
}