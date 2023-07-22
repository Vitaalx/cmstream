<?php

namespace Services\Back;


use PHPMailer\Exception;
use PHPMailer\PHPMailer;
use PHPMailer\SMTP;

/**
 * This class is used to send mails.
 * This class use PHPMailer.
 * Check documentation here: https://github.com/PHPMailer/PHPMailer/tree/master/docs
 */
class MailService
{
    public static function send(string $to, string $subject, string $message): void
    {
        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = CONFIG["MAIL_HOST"];
            $mail->Port = CONFIG["MAIL_PORT"];

            $mail->setFrom(CONFIG["MAIL_FROM"], CONFIG["APP_NAME"]);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->send();
        } catch (\Exception $e) {
            throw new Exception("mail.error");
        }
    }
}
