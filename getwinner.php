<?php
require_once('email.php');
/*
Алгоритм работы:

Найти все лоты без победителей, дата истечения которых меньше или равна текущей дате.
Для каждого такого лота найти последнюю ставку.
Записать в лот победителем автора последней ставки.
Отправить победителю на email письмо — поздравление с победой.
*/

if (empty($error)) {
    $sql = "SELECT u.name as uname, l.name as lname, l.key_id, r.user_id, r.price, u.email FROM lots l" . 
    " JOIN rates r ON l.key_id = r.lot_id" . 
    " JOIN users u ON u.key_id = r.user_id" .
    " WHERE winner_id IS NULL AND l.dt_fin <= NOW()";
    $result = $yetiCave->query($sql);
    if ($result) {
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        foreach ($rows as $lot) {
            if (check_rate($yetiCave, $lot['key_id'], $lot['price'])) {
                $winner = $lot['user_id'];
                $key = $lot['key_id'];
                $sql = "UPDATE lots SET winner_id = $winner WHERE key_id = $key";
                $result = $yetiCave->query($sql);
                if ($result) {
                    $email_content = include_template('Emailtempl.php', ['lot' => $lot]);
                    congratulation($lot['uname'], $email_content, $lot['email']);
                } else {
                    $error = $yetiCave->error();
                }        
                break;
            }
        }
    } else {
        $error = $yetiCave->error();
    }
}
