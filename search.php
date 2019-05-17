<?php
require_once('dbinit.php');
require_once('pagination.php');
require_once('functions.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();


$user_id = 0; // id пользователя
$user_name = "";
$is_auth = 0;
$to_search = "";
$search_content = "";


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($_GET['user_id']))
	{
    	$user_id = $_GET['user_id'];
        $visit_cookie = 'visit_' . $user_id;
        if (isset($_SESSION[$visit_cookie])) {
            $is_auth = 1;
            $user_name = $_SESSION[$visit_cookie];
        }
   	}
	if (isset($_GET['search']))
	{
		$to_search = $_GET['search'];
		if (!(strpos($to_search, "Поиск лота") === false)) {
			$error = "Не задана поисковая последовательность";
        }
	}
}

if (empty($error))
{
	$lots_count = 0;
	//ищем в базе данные по запросу пользователя
	$sql = "SELECT COUNT(*) FROM lots l" .
	" JOIN categories c ON l.cat_id = c.key_id" .
	" WHERE MATCH(l.name, descr) AGAINST('$to_search' IN BOOLEAN MODE)";
	$result = mysqli_query($link, $sql);
	if ($result) {
		$rows = mysqli_fetch_row($result);
		$lots_count = $rows[0];
		$offset_page = ($lot_page - 1) * $max_lots_per_page;
		$max_page = floor($lots_count / $max_lots_per_page);
		if ($lots_count % $max_lots_per_page > 0) {
			$max_page++;
		}	    
	    $sql = "SELECT l.name, c.name as cat_name, cat_id, l.price, descr, img_url, l.key_id, l.dt_fin FROM lots l" .
	        " JOIN categories c ON l.cat_id = c.key_id" .
	        " WHERE MATCH(l.name, descr) AGAINST('$to_search' IN BOOLEAN MODE)" .
    	    " LIMIT $max_lots_per_page OFFSET $offset_page";
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
		    if (empty($catsInfoArray))
		    {
		    	$error = "По Вашему запросу ничего не найдено";
		    }
		}
    	else {
    		$error = mysqli_error($link) . " --1";
    	}
	}
	else {
		$error = mysqli_error($link) . " --2";
	}
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
}
else {
    $search_content = include_template('error.php', ['error' => $error]);
}
    
print($search_content);