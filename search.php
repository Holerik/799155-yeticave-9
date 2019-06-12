<?php
require_once('dbinit.php');
require_once('pagination.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();


$user_id = 0; // id пользователя
$user_name = "";
$is_auth = 0;
$to_search = "";
$search_content = "";
$max_lots_per_page = 9;
$safe_search = "";

$catsInfoArray = [];

$bets_count = [];

if (isset($_SESSION['sess_id'])) {
    $user_id = $_SESSION['sess_id'];
}
if (isset($_SESSION['sess_name'])) {
    $user_name = $_SESSION['sess_name'];
    $is_auth = 1;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['search'])) {
        $to_search = $yetiCave->escape_str($_GET['search']);
        if (!(strpos($to_search, "Поиск лота") === false)) {
            $error = "Не задана поисковая последовательность";
        } else {
            $safe_search = trim($to_search);
        }
    }
}

if (empty($error) && !empty($safe_search)) {
    $lots_count = 0;
    //ищем в базе данные по запросу пользователя
    $sql = "SELECT COUNT(*) FROM lots l" .
    " JOIN categories c ON l.cat_id = c.key_id" .
    " WHERE MATCH(l.name, descr) AGAINST('$safe_search' IN BOOLEAN MODE) AND l.dt_fin > NOW()";
    $result = $yetiCave->query($sql);
    if ($result) {
        $rows = mysqli_fetch_row($result);
        $lots_count = $rows[0];
        $offset_page = ($lot_page - 1) * $max_lots_per_page;
        $max_page = floor($lots_count / $max_lots_per_page);
        if ($lots_count % $max_lots_per_page > 0) {
            $max_page++;
       }    
    } else {
        $error = $yetiCave->error();
        header("Location:_404php?hdr=Поиск&msg=" . $error);
    }
    if (empty($error)) {
        unset($catsInfoArray);
        $sql = "SELECT l.name, c.name as cat_name, cat_id, l.price, descr, img_url, l.key_id, l.dt_fin FROM lots l" .
            " JOIN categories c ON l.cat_id = c.key_id" .
            " WHERE MATCH(l.name, descr) AGAINST('$safe_search' IN BOOLEAN MODE)  AND l.dt_fin > NOW()" .
            " ORDER BY l.dt_add DESC" .
            " LIMIT $max_lots_per_page OFFSET $offset_page";
        $result = $yetiCave->query($sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach ($rows as $row) {
                $bets_count = 0;
                $sql = "SELECT COUNT(*) FROM rates r WHERE r.lot_id = " . $row['key_id'];
                $result1 = $yetiCave->query($sql);
                if ($result) {
                    $lrows = mysqli_fetch_row($result1);
                    $bets_count = $lrows[0];
                } else {
                    $error = $yetiCave->error();
                    break;
                }
                $catsInfoArray[] = [
                    'lot_name' => $row['name'],
                    'cat_name' => $row['cat_name'],
                    'lot_price' => $row['price'],
                    'lot_img' => $row['img_url'],
                    'lot_id' => $row['key_id'],
                    'cat_id' => $row['cat_id'],
                    'dt_fin' => $row['dt_fin'],
                    'bets_count' => $bets_count
                ];
            }
        } else {
            $error = $yetiCave->error();
            header("Location:_404php?hdr=Поиск&msg=" . $error);
        }
    }
}

if (empty($catsInfoArray)) {
    header("Location: _404.php?hdr=Поиск&msg=По Вашему запросу ничего не найдено");
}

if (empty($error)) {
    $search_content = include_template('Searchtempl.php', [
                        'catsArray' => $catsArray,
                        'catsInfoArray' => $catsInfoArray,
                        'to_search' => $to_search,
                        'user_name' => $user_name,
                        'user_id' => $user_id,
                        'is_auth' => $is_auth,
                        'max_page' => $max_page,
                        'lot_page' => $lot_page
    ]);
} else {
    header("Location:_404php?hdr=Поиск&msg=" . $error);
}
    
print($search_content);
