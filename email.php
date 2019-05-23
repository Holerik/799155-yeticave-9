<?php
require_once('./vendor/autoload.php');

function congratulation($content, $email) {
    $transport = new Swift_SmtpTransport('smtp.mail.ru', 465, 'ssl');
    $transport->setPassword('Gb0df4dk');
    $transport->setUsername('a_zobnin');
    $message = new Swift_Message('Аукцион YetiCave');
    $message->setTo(['a_zobnin@mail.ru', 'a_zobnin@mail.ru' => 'Кекс']);
    $message->setFrom(['a_zobnin@mail.ru' => 'YetiCave']);
    $message->setMaxLineLength(255);
    $message->setBody($content, 'text/html');
    $mailer = new Swift_Mailer($transport);
    $result = $mailer->send($message);
}