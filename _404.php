<?php
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

$user_id = 0;
$user_name = "";
$is_auth = 0;
$title = "Ошибка";

if (isset($_SESSION['sess_id'])) {
    $user_id = $_SESSION['sess_id'];
}
if (isset($_SESSION['sess_name'])) {
    $user_name = $_SESSION['sess_name'];
    $is_auth = 1;
}

$hdr = "404 Страница не найдена";
$msg = "Данной страницы не существует на сайте.";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['hdr'])) {
        $hdr = htmlspecialchars($_GET['hdr']);
        if (!strstr($hdr, "error")) {
            $title = "Информация";
        }
    }
    if (isset($_GET['msg'])) {
        $msg = htmlspecialchars($_GET['msg']);
    }
}

$content = include_template('_404templ.php', [
                    'catsInfo' => $catsArray,
                    'user_name' => $user_name,
                    'user_id' => $user_id,
                    'is_auth' => $is_auth,
                    'hdr' => $hdr,
                    'msg' => $msg,
                    'title' => $title
]);
print($content);
