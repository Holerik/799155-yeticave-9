<?php
require_once('dbinit.php');
require_once('pagination.php');
require_once('functions.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();
$user_name = "";
$user_id = 0;
$is_auth = 0;
$max_lots_per_page = 6;

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
	if (empty($error)) {
		$sql ="SELECT COUNT(*) FROM lots";
		$result = mysqli_query($link, $sql);
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
		" LIMIT $max_lots_per_page OFFSET $offset_page ";
	    $result = mysqli_query($link, $sql);
	    if ($result) {
		    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
		    foreach ($rows as $row) {
			    $catsInfoArray[] = [
		    	'Название' => $row['name'],
			    'Категория' => $row['cat_name'],
		    	'Цена' => $row['price'],
			    'URL картинки' => $row['img_url'],
			    'lot_id' => $row['key_id'],
			    'cat_id' => $row['cat_id'],
		    	'dt_fin' => $row['dt_fin']
	    		];
		    }
		}
    	else {
    		$error = mysqli_error($link);
    	}
	}
  	else {
   		$error = mysqli_error($link);
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
        	'user_id' => $user_id
    	]);
	
    	$layout_content = include_template('Layout.php', [
        	'header' => $header_content,
        	'content' => $main_content,
        	'footer' => $footer_content
    	]);
    
    	print($layout_content);
    }
	if (!empty($error)) {
    	 $error_content = include_template('error.php', ['error' => $error]);
    	 print($error_content);
	}
    ?>
</html>
