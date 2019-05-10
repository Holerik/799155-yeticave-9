<?php
require_once('dbinit.php');
require_once('functions.php');
$lot_content = "";

if (isset($_GET['lot_id'])) {
    $lotId = $_GET['lot_id'];  
    if ($link) {
        $sql = "SELECT * FROM lots WHERE key_id = $lotId";
        $result = mysqli_query($link, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            if (mysqli_num_rows($result) > 0) {
                $lot_content = include_template('Lotempl.php', [
                    'lotInfo' => $row,
                    'catsInfo' => $catsArray,
                    'dblink' => $link
                    ]);
                print($lot_content);
            }
            else {
                http_response_code(404);
            }
        }
        else {
            $error = mysqli_error($link);
            $lot_content = include_template('error.php', ['error' => $error]);
            print($lot_content);
        }
    }
}
else {
    http_response_code(404);
}
?>

