<?php
$error = "";
$error_content = "";
$host = 'localhost';
$user = 'root';
$dbname = 'yeticave';
$catsArray = [];
$catsInfoArray = [];

$link = mysqli_connect($host, $user, "", $dbname);
if (!$link) {
    $error = mysqli_connect_error();
    $error_content = incude_template('error.php', ['error' => $error]);
    print("Ошибка MySQL: " . $error);
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
        $error = mysqli_connect_error();
        $error_content = incude_template('error.php', ['error' => $error]);
        print("Ошибка MySQL: " . $error);
    }
    $sql = "SELECT l.name, c.name as cat_name, l.price, img_url, l.key_id FROM lots l" .
     " JOIN categories c ON l.cat_id = c.key_id" .
     " WHERE dt_fin IS NULL";
    $result = mysqli_query($link, $sql);
    if ($result) {
         $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
         foreach ($rows as $row) {
            $catsInfoArray[] = [
                                'Название' => $row['name'],
                                'Категория' => $row['cat_name'],
                                'Цена' => $row['price'],
                                'URL картинки' => $row['img_url'],
                                'lot_id' => $row['key_id']
            ];
        }
    }
    else {
        $error = mysqli_connect_error();
        $error_content = incude_template('error.php', ['error' => $error]);
        print("Ошибка MySQL: " . $error);
        }
 
    //mysqli_close($link);
}
