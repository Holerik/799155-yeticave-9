<?php
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

$user_info = [];
$errors = [];   //перечень ошибок для полей формы

$required_fields = ['name', 'email', 'password', 'message'];
$dictionary = [
    'name' => 'Имя пользователя',
    'email' => 'Адрес эл.почты',
    'password' => 'Пароль авторизации',
    'message' => 'Поле для контакта'
];

$user_id = 0; // id пользователя
$user_info = [
    'name' => "",
    'email' => "",
    'password' => "",
    'message' => "",
    'avatar' => ""
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Проверка полей на заполненность
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            $errors[$field] = 'Поле отсутствует!';
            continue;
        }
        if (empty($_POST[$field])) {
             $errors[$field] = 'Поле не заполнено!';
             continue;
        }
        //обезопасимся от XSS-уязвимости
        $user_info[$field] = htmlspecialchars($_POST[$field]);
    }
    if (!filter_var($user_info['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Поле не соответствует email!';
    }
    if (strlen($_POST['message']) > 255) {
        $errors['message'] = 'Извините, но не более 255 символов';
    }

    //обработка графических данных
    if (isset($_FILES['avatar'])) {
        if (!empty($_FILES['avatar']['name'])) {
            $user_info['avatar'] = htmlspecialchars($_FILES['avatar']['name']);
        }
    }
    if (isset($_FILES['avatar']) && count($errors) == 0) {
        $file_path = __DIR__ . '\\uploads\\';
        $file_name = $yetiCave->escape_str($_FILES['avatar']['name']);
        move_uploaded_file($_FILES['avatar']['tmp_name'], $file_path . $file_name);
        //проверка на ожидаемый графический формат
        $type = "";
        $ext = "";
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $ftype = finfo_file($finfo, $file_path . $file_name);
            $pos = strpos($ftype, '/');
            $type = substr($ftype, 0, $pos);
            $ext = substr($ftype, $pos + 1);
            finfo_close($finfo);
        } else {
            //Открытие базы данных fileinfo не удалось;
            $pos = strpos($file_name, '.');
            $ext = substr($file_name, $pos + 1);
            $type = 'image';
        }
        //замена оригинального имени на случайное
        if ($type === 'image' && ($ext === 'jpg' || $ext === 'png' || $ext === 'jpeg')) {
            $uniq_name = uniqid();
            $new_file_name = $uniq_name . '.' . $ext;
            rename($file_path . $file_name, $file_path . $new_file_name);
            $user_info['avatar'] =  "uploads/" . $new_file_name;
            //добавим в каталог 'маленький' файл с изображением
            resize_img(50, 0, $file_path . $new_file_name, $file_path . $new_file_name);
        } else {
            $errors['avatar'] = 'Укажите файл с расширением jpg, jpeg или png';
        }
    } 

    $safe_name = "";
    $safe_email = "";
    $safe_info = "";    

    if (count($errors) == 0) {
        $safe_name = $yetiCave->escape_str($user_info['name']);
        $safe_email = $yetiCave->escape_str($user_info['email']);
        $safe_info = $yetiCave->escape_str($user_info['message']);
    
        $sql = "SELECT * FROM users WHERE email = '$safe_email'";
        $result = $yetiCave->query($sql);
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $errors['email'] = 'Почта ' . $safe_email . ' уже зарегистрирована!';
            }
        } else {
            $error = $yetiCave->error();
            header("Location:_404.php?hdr=SQL error&msg=" . $error);
        }
    }

    if (count($errors) == 0 && empty($error)) {
        if (empty($user_info['avatar'])) {
            $user_info['avatar'] = 'img/user.png';
        }
        //зарегистрируем пользователя и получим его id
        //получим для пароля хэш
        $avatar = $user_info['avatar'];
        $passwordHash = password_hash($user_info['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, name, password, info, avatar_path)"
                    . " VALUES (?, ?, ?, ?, ?)";
        $stmt = $yetiCave->prepare_stmt($sql, [$safe_email, $safe_name, $passwordHash, $safe_info, $avatar]);
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
            $user_id = $yetiCave->last_id();
        } else {
            $error = $yetiCave->error();
            header("Location:_404.php?hdr=SQL error&msg=" . $error);
        }
    }
    if ($user_id > 0) {
        //переходим на страницу авторизации
        header("Location:_404.php?hdr=Вы зарегистрированы&msg=Перейтите на страницу авторизации");
        //header("Location:login.php");
    }
}

if (empty($error)) {
    $sign_content = include_template('Signtempl.php', [
        'catsInfo' => $catsArray,
        'userInfo' => $user_info,
        'user_id' => $user_id,
        'errors' => $errors,
        'dictionary' => $dictionary
    ]);
} else {
    header("Location:_404.php?hdr=SQL error&msg=" . $error);
}
    
print($sign_content);