<?php
require_once("vendor/autoload.php");
/**
 * Отсылает поздравительную информацию
 * 
 * @param string $name    Имя получателя
 * @param string $content Содержимое письма
 * @param string $email   Адрес получателя
 * 
 * @return Ничего 
 */
function congratulation($name, $content, $email) 
{
    $transport = new Swift_SmtpTransport('smtp.mail.ru', 465, 'ssl');
    $transport->setPassword('Gb0df4dk');
    $transport->setUsername('a_zobnin');
    $message = new Swift_Message('Аукцион YetiCave');
    $message->setTo([$email, $email => $name]);
    $message->setFrom(['a_zobnin@mail.ru' => 'YetiCave']);
    $message->setMaxLineLength(255);
    $message->setBody($content, 'text/html');
    $mailer = new Swift_Mailer($transport);
    $result = $mailer->send($message);
}

/**
 * Отсылает информацию для восстановления пароля
 *
 * @param string $name    Имя получателя
 * @param string $content Содержимое письма
 * @param string $email   Адрес получателя
 * 
 * @return Ничего 
 */
function restoreinfo($name, $content, $email) 
{
    $transport = new Swift_SmtpTransport('smtp.mail.ru', 465, 'ssl');
    $transport->setPassword('Gb0df4dk');
    $transport->setUsername('a_zobnin');
    $message = new Swift_Message('Аукцион YetiCave');
    $message->setTo([$email, $email => 'Кекс']);
    $message->setFrom(['a_zobnin@mail.ru' => 'YetiCave']);
    $message->setMaxLineLength(255);
    $message->setBody($content, 'text/html');
    $mailer = new Swift_Mailer($transport);
    $result = $mailer->send($message);
}