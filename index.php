<?php
require_once('functions.php');
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();
$user_name = "";
$user_id = 0;
$is_auth = 0;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $visit_cookie = 'visit_' . $user_id;
        if (isset($_SESSION[$visit_cookie])) {
            $user_name = $_SESSION[$visit_cookie];
            $is_auth = 1;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
    <?php
     $header_content = include_template('Header.php', [
        'title' => $pageName,
        'user_id' => $user_id,
        'user_name' => $user_name,
        'is_auth' => $is_auth
    ]);
    
    $main_content =  include_template('Main.php', [
        'catsArray' => $catsArray,
        'catsInfoArray' => $catsInfoArray,
        'user_id' => $user_id
    ]);
    
    $footer_content = include_template('Footer.php', [
        'catsArray' => $catsArray,
        'user_id' => $user_id
    ]);
	
    $layout_content = include_template('Layout.php', [
        'header' => $header_content,
        'content' => $main_content,
        'footer' => $footer_content
    ]);
    
    print($layout_content);
    ?>
</html>
