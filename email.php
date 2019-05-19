<?php
require_once('./vendor/autoload.php');

function congratulation($content, $email) {
    $transport = new Swift_SmtpTransport('smtp.phpdemo.ru', 25);
    $transport->setPassword('htmlacademy');
    $transport->setUsername('keks');
    $message = new Swift_Message('Аукцион YetiCave');
    $message->setTo(['keks@htmlacademy.ru', 'keks@htmlacademy.ru' => 'Кекс']);
    $message->setFrom(['keks@phpdemo.ru' => 'YetiCave']);
    $message->setMaxLineLength(255);
    $message->setBody($content, 'text/html');
    $mailer = new Swift_Mailer($transport);
    $result = $mailer->send($message);
}