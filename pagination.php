<?php
//общее количество лотов
$lots_count = 0;
//максимальное количество лотов на странице
$max_lots_per_page = 1;
$offset_page = 0;

//текущий номер страницы (нумерация от 1)
$lot_page = 1;
//максимальный номер страницы
$max_page = 1;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['lot_page'])) {
    	$lot_page = $_GET['lot_page'];
    }
    if (isset($_GET['lot_ppage'])) {
    	$max_lots_per_page = $_GET['lot_ppage'];
    }
}

 