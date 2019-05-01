<?php
$error = "";

function remained_time() {
    $retVal = [];
    $cur_date = date_parse(date('H:i:s d.m.Y'));
    $hour = $cur_date['hour'];
    $minute = $cur_date['minute'];
    $maxHour = 24;
    if ($minute > 0) {
        $maxHour--;
        $minute = 60 - $minute;
    }
    $hour = $maxHour - $hour;
    $retVal = [$hour, $minute];
    return $retVal;
}

function format_price($price) {
    $result = "";
    $price = ceil($price);
    if ($price > 1000) {
        $result = number_format($price, 0, ".", " ");
    }
    else {
        $result = $price;
    }
    $result .= " ₽";
    return $result;
}
