<?php

namespace Services\Back;


use PHPMailer\Exception;
use PHPMailer\PHPMailer;
use PHPMailer\SMTP;

class MailService
{
    public static function send(string $to, string $subject, string $message): void
    {
        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 2;
            $mail->isSMTP();
            $mail->Host = CONFIG["MAIL_HOST"];
            $mail->Port = CONFIG["MAIL_PORT"];

            $mail->setFrom(CONFIG["MAIL_FROM"], CONFIG["MAIL_FROM_NAME"]);
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
