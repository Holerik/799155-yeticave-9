<?php
require_once('dbinit.php');

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
        $user_info[$field] = "";
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
    if (count($errors) == 0) {
        //есть ли пользователь с таким именем?
        $sql = "SELECT name key_id FROM users";
        $result = mysqli_query($link, $sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach ($rows as $row) {
            	if ($row['name'] === $user_info['name']) {
			        //переходим на страницу авторизации
            		$user_id =  $row['key_id'];
		        	header("Location: login.php?user_id=" . $user_id);
            	}
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
    if ($user_id > 0) {
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