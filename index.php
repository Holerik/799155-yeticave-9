<?php
require_once('dbinit.php');
require_once('pagination.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();
$user_name = "";
$user_id = 0;
$is_auth = 0;
$max_lots_per_page = 6;
$pageName = 'YetiCave';

if (isset($_SESSION['sess_id'])) {
    $user_id = $_SESSION['sess_id'];
}

if (isset($_SESSION['sess_name'])) {
    $user_name = $_SESSION['sess_name'];
    $is_auth = 1;
}

$catsInfoArray[] = [
    'lot_name' => "",
    'cat_name' => "",
    'lot_price' => 0,
    'lot_img' => "",
    'lot_id' => 0,
    'cat_id' => 0,
    'dt_fin' => 0
];

if (empty($error)) {
    $sql ="SELECT COUNT(*) FROM lots l" .
    " WHERE l.dt_fin > NOW()";
    $result = $yetiCave->query($sql);
}
if ($result) {
    $rows = mysqli_fetch_row($result);
    $lots_count = $rows[0];
    $offset_page = ($lot_page - 1) * $max_lots_per_page;
    $max_page = floor($lots_count / $max_lots_per_page);
    if ($lots_count % $max_lots_per_page > 0) {
        $max_page++;
    }
    $sql = "SELECT l.name, c.name as cat_name, cat_id, l.price, img_url, l.key_id, l.dt_fin FROM lots l" .
    " JOIN categories c ON l.cat_id = c.key_id" .
    " WHERE l.dt_fin > NOW()" .
    " ORDER BY l.dt_add DESC" .
    " LIMIT $max_lots_per_page OFFSET $offset_page";
    $result = $yetiCave->query($sql);
    if ($result) {
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        if ($lots_count > 0) {
            unset($catsInfoArray);
        }
        foreach ($rows as $row) {
            $catsInfoArray[] = [
                'lot_name' => $row['name'],
                'cat_name' => $row['cat_name'],
                'lot_price' => $row['price'],
                'lot_img' => $row['img_url'],
                'lot_id' => $row['key_id'],
                'cat_id' => $row['cat_id'],
                'dt_fin' => $row['dt_fin']
            ];
        }
    } else {
        $error = $yetiCave->error();
    }
} else {
    $error = $yetiCave->error();
}
if (empty($error)) {
    $header_content = include_template('Header.php', [
        'title' => $pageName,
        'user_id' => $user_id,
        'user_name' => $user_name,
        'is_auth' => $is_auth
    ]);
    
    $main_content =  include_template('Main.php', [
        'catsArray' => $catsArray,
        'catsInfoArray' => $catsInfoArray,
        'user_id' => $user_id,
        'max_page' => $max_page,
        'lot_page' => $lot_page
    ]);
    
    $footer_content = include_template('Footer.php', [
        'catsArray' => $catsArray,
        'user_id' => $user_id,
        'is_auth' => $is_auth
    ]);

    $layout_content = include_template('Layout.php', [
        'header' => $header_content,
        'content' => $main_content,
        'footer' => $footer_content
    ]);
    
    print($layout_content);
} else {
    header("Location:_404php?hdr=SQL error&msg=" . $error);
}
