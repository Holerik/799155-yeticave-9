<?php
require_once('dbinit.php');
require_once('pagination.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();
$user_name = "";
$user_id = 0;
$is_auth = 0;
//категория лота
$cat_id = 0;
//количество ставок для каждого лота
$bets_count = [];

$all_lots_content = "";
$max_lots_per_page = 9;
$lots_count = 0;

if (isset($_SESSION['sess_id'])) {
    $user_id = $_SESSION['sess_id'];
}
if (isset($_SESSION['sess_name'])) {
    $user_name = $_SESSION['sess_name'];
    $is_auth = 1;
}

$catsInfoArray = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['cat_id'])) {
        $cat_id = intval($_GET['cat_id']);
        //есть ли такая категория в базе
        $sql = "SELECT COUNT(*) FROM lots l WHERE l.cat_id = $cat_id";
        $result = $yetiCave->query($sql);
        if ($result) {
            $rows = mysqli_fetch_row($result);
            if ($rows[0] == 0) {
                header("Location: _404.php");
            }
        }

        if ($is_auth == 1) {
            //отметимся в куках для категории этих лотов
            $visit_cookie = 'visit_' . $user_id;
            $path = "/";
            if (isset($_COOKIE[$visit_cookie])) {
                $cookie = $_COOKIE[$visit_cookie];
                $cookie = updatecookie($cookie, $cat_id);
                $expire = time() + 3600;
                setcookie($visit_cookie, $cookie, $expire, $path, "", false, true);
            }
        }
    }
}

if (empty($error)) {
    $sql = "SELECT COUNT(*) FROM lots l WHERE l.cat_id = $cat_id AND l.dt_fin > NOW()";
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
        header("Location:_404.php?hdr=SQL error&msg=" . $error);
    }
}

if ($lots_count > 0) {
    $sql = "SELECT l.name, c.name as cat_name, cat_id, l.price, img_url, l.key_id, l.dt_fin FROM lots l" .
    " JOIN categories c ON l.cat_id = c.key_id  WHERE l.cat_id = $cat_id AND l.dt_fin > NOW()" .
    " ORDER BY l.dt_add DESC" .
    " LIMIT $max_lots_per_page OFFSET $offset_page";
    $result = $yetiCave->query($sql);
    if ($result) {
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
}


if (empty($error)) {
    foreach ($catsInfoArray as $catsInfo) {
        $bets_count[$catsInfo['lot_id']] = 0;
        $sql = "SELECT COUNT(*) FROM rates r WHERE r.lot_id = " . $catsInfo['lot_id'];
        $result = $yetiCave->query($sql);
        if ($result) {
            $rows = mysqli_fetch_row($result);
            $count = $rows[0];
            $bets_count[$catsInfo['lot_id']] = $count;
        } else {
            $error = $yetiCave->error();
            break;
        }
    }
}


if (empty($error)) {
    $all_lots_content = include_template('Alltempl.php', [
        'catsArray' => $catsArray,
        'catsInfoArray' => $catsInfoArray,
        'bets_count' => $bets_count,
        'cat_id' => $cat_id,
        'user_id' => $user_id,
        'user_name' => $user_name,
        'is_auth' => $is_auth,
        'max_page' => $max_page,
        'lot_page' => $lot_page
    ]);
} else {
    header("Location:_404.php?hdr=SQL error&msg=" . $error);
}
print($all_lots_content);
