<?php

use Molnix\BouncedMailManager\Message\Header;
use PHPMailer\PHPMailer\PHPMailer;

include '../vendor/autoload.php';
$config = require('_config.php');


$mail = new PHPMailer(true);

try {
    //Server settings

    $mail->isSMTP();
    $mail->Host       = $config['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['username'];
    $mail->Password   = $config['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    //Recipients
    $mail->setFrom($config['username'], 'Bounce Manager');
    $mail->addAddress($config['test_mail']);

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Mail from bounce manager';
    $mail->Body    = 'This is a mail from <b>Bounce manager</b>';
    $mail->AltBody = 'This is a mail from Bounce manager';

    foreach(Header::getCustomHeaders($config['username'], $config['test_mail'], 'Mail from bounce manager') as $key => $val) {
        $mail->addCustomHeader($key, $val);
    }


    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
