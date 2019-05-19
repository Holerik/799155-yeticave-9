<?php
$error = "";
$error_content = "";
$host = 'localhost';
$user = 'root';
$dbname = 'yeticave';

require_once('helpers.php');

$catsArray = [];
$catsInfoArray = [];

$link = mysqli_connect($host, $user, "", $dbname);
if (!$link) {
    $error = mysqli_connect_error();
    $error_content = incude_template('error.php', ['error' => $error]);
}
else {
    mysqli_set_charset($link, "utf8");
  	$sql = "SELECT key_id, name, code FROM categories ORDER BY key_id";
    $result = mysqli_query($link, $sql);
   	if ($result) {
       	$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
       	foreach ($rows as $row) {
           	$catsArray[] = [
                           'id' => $row['key_id'],
                           'name' => $row['name'],
                           'code' => $row['code']
												];
       	}
   	}
   	else {
   		$error = mysqli_error($link);
   	}
}

require_once('functions.php');
require_once('getwinner.php');
