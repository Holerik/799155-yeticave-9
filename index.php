<?php
require_once('dbinit.php');
require_once('functions.php');
?>
<!DOCTYPE html>
<html lang="ru">
    <?php
    $layout_content = "";
    $header_content = include_template('Header.php', [
        'title' => $pageName,
        'user_name' => $user_name,
        'is_auth' => $is_auth
    ]);
    
    $main_content =  include_template('Main.php', [
        'catsArray' => $catsArray,
        'catsInfoArray' => $catsInfoArray
    ]);
    
    $footer_content = include_template('Footer.php', [
        'catsArray' => $catsArray
    ]);
    
    if (empty($error_content)) {
        $layout_content = include_template('Layout.php', [
            'header' => $header_content,
            'content' => $main_content,
            'footer' => $footer_content
        ]);
    }
    else {
        $layout_content = $error_content;
    }
    
    print($layout_content);
    ?>
</html>
