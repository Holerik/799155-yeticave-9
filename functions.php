<?php
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

require_once('helpers.php');
