<?php
require_once('dbinit.php');
require_once('functions.php');

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Проверка полей на заполненность
    foreach ($required_fields as $field) {
        if (isset($_POST[$field])) {
            if (empty($_POST[$field])) {
                $errors[$field] = 'Поле не заполнено!';
                $user_info[$field] = "";
            }
            else {
                //обезопасимся от XSS-уязвимости
                $user_info[$field] = htmlspecialchars($_POST[$field]);
            }
        }
    }
    if (!filter_var($user_info['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Поле не соответствует email!';
    }
    if (count($errors) == 0) {
        //есть ли пользователь с таким именем?
        $sql = "SELECT name FROM users";
        $result = mysqli_query($link, $sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach ($rows as $row)
            if ($row['name'] == $user_info['name']) {
                $error = "Пользователь с таким именем уже есть";
                break;
            }
        }
        else {
            $error = mysqli_error($link);
        }
        if (empty($error)) {
            //зарегистрируем пользователя и получим его id
            //получим для пароля хэш
            $passwordHash = password_hash($user_info['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (email, name, password, info)"
                . " VALUES (?, ?, ?, ?)";
            $stmt = db_get_prepare_stmt($link, $sql, [$user_info['email'], $user_info['name'],
            $passwordHash, $user_info['message']]);
            $result = mysqli_stmt_execute($stmt);
            if ($result) {
                $user_id = mysqli_insert_id($link);
            }
            else {
                $error = mysqli_error($link);
                print("Ошибка MySQL: " . $error);
            }
        }
    }    
    else {
        //проверим поля - м.б. этого достаточно для авторзации
        if (!isset($errors['email'])) {
            if (!empty($user_info['name'])) {
                //ищем в базе id пользователя по name
                $sql = "SELECT key_id, name FROM users";
                $result = mysqli_query($link, $sql);
                if ($result) {
                    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    foreach ($rows as $row) {
                        if ($row['name'] == $user_info['name']) {
                            $user_id = $row['key_id'];
                            break;
                        }
                    }
                }
                else {
                    $error = mysqli_error($link);
                }
            }
            if ($user_id == 0 && !empty($user_info['email'])) {
                //ищем в базе id пользователя по email
                $sql = "SELECT key_id, email FROM users";
                $result = mysqli_query($link, $sql);
                if ($result) {
                    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    foreach ($rows as $row) {
                        if ($row['email'] == $user_info['email']) {
                            $user_id = $row['key_id'];
                            break;
                        }
                    }
                }
                else {
                    $error = mysqli_error($link);
                }
            }
        }
    }
    if ($user_id > 0) {
        //если куки есть - увеличиваем счетчик посещений
        //если нет - создаем для него куки
        //открываем сессию для пользователя
        //переходим на страницу авторизации
        header("Location: login.php?user_id=" . $user_id);
    }
}

if (empty($error)) {
    $sign_content = include_template('Signtempl.php', [
    'catsInfo' => $catsArray,
    'userInfo' => $user_info,
    'errors' => $errors,
    'dictionary' => $dictionary
    ]);
}
else {
    $sign_content = include_template('error.php', ['error' => $error]);
}
    
print($sign_content);