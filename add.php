<?php
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();


$user_id = 0;
$user_name = "";
$is_auth = 0;

$required_fields = ['category', 'message', 'lot-name', 'lot-rate', 'lot-step', 'lot-date', 'lot-img'];
$dictionary = [
    'category' => 'Категория',
    'message' => 'Описание',
    'lot-name' => 'Наименование',
    'lot-rate' => 'Начальная цена',
    'lot-step' => 'Шаг ставки',
    'lot-date' => 'Дата окончания',
    'lot-img' => 'Изображение'
];

//данные полей формы
$lot_info[] = [
    'category' => "",
    'message' => "",
    'lot-name' => "",
    'lot-rate' => 0,
    'lot-step' => 0,
    'lot-date' => 0,
    'lot-img' => ""
];

$errors = [];   //перечень ошибок для полей формы
$add_content = "";

if (isset($_SESSION['sess_id'])) {
    $user_id = $_SESSION['sess_id'];
}
if (isset($_SESSION['sess_name'])) {
    $user_name = $_SESSION['sess_name'];
    $is_auth = 1;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['category'])) {
        //это плейсхолдер - категория не выбрана
        if ($_POST['category'] === 'Выберите категорию') {
            $_POST['category'] = "";
        }
    }
    
    //Проверка полей на заполненность
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) && !($field === 'lot-img')) {
            $errors[$field] = 'Поле отсутствует!';
            continue;
        }
        if (empty($_POST[$field]) && !($field === 'lot-img')) {
            $errors[$field] = 'Поле не заполнено!';
            continue;
        }
        if ($field === 'lot-rate' || ($field === 'lot-step')) {
            $options = [
                     'options' => [
                     'default' => 0,
                     'min_range' => 1,
                     'max_range' => 1000000
                      ]
                ];
            if (!filter_var($_POST[$field], FILTER_VALIDATE_INT, $options)) {
                        $errors[$field] = 'Поле не должно содержать символов!';
                        print($field . "-- " . $lot_info[$field] . "  ---\n");
                        continue;
            }   
        }
        //обезопасимся от XSS-уязвимости
        if ($field === 'lot-img') {
            //обработка графических данных
            if (isset($_FILES['lot-img'])) {
                if (empty($_FILES['lot-img']['name'])) {
                    $errors['lot-img'] = 'Не выбран файл изображения';
                } else {
                    $lot_info[$field] = htmlspecialchars($_FILES['lot-img']['name']);
                }
            }
        } else {
            $lot_info[$field] = htmlspecialchars($_POST[$field]);
        }
    }
    if (!isset($errors['lot-img'])) {
        $file_path = __DIR__ . '\\uploads\\';
        $file_name = $_FILES['lot-img']['name'];
        move_uploaded_file($_FILES['lot-img']['tmp_name'], $file_path . $file_name);
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
        if ($type === 'image') {
            $new_file_name = uniqid() . '.' . $ext;
            rename($file_path . $file_name, $file_path . $new_file_name);
            $lot_info['lot-img'] =  "uploads/" . $new_file_name;
        } else {
            $errors['lot-img'] = 'Укажите файл с графическими данными';
        }
    } 

    //Проверка полей на ожидаемый формат
    //проверка формата поля с датой
    if (!is_date_valid($lot_info['lot-date'])) {
        $errors['lot-date'] = "Неверный формат даты";
    } else {
        //дата должна быть новее сегодняшней
        $now = time();
        $lot = strtotime($lot_info['lot-date']);
        if ($lot < $now) {
            $errors['lot-date'] = 'Укажите более новую дату';
        }
    }
    if (count($errors) == 0) {
        $cat_id = 0;
        foreach ($catsArray as $cat) {
            if ($cat['name'] === $lot_info['category']) {
                $cat_id = $cat['id'];
                break;
            }
        }
        $safe_name = $yetiCave->escape_str($lot_info['lot-name']);
        $safe_descr = $yetiCave->escape_str($lot_info['message']);
        //запишем данные лота в базу
        //поищем дубль
        $sql = "SELECT l.name FROM lots l WHERE l.name LIKE '$safe_name'";
        $result = $yetiCave->query($sql);
        $flag = false;
        if ($result) {
            $rows = mysqli_fetch_row($result);
            if (isset($rows)) {
                $flag = true;
            }
        }
        if ($flag === false) {
            $sql = "INSERT INTO lots (dt_add, name, descr, img_url, price, dt_fin, rate_step, cat_id, autor_id)"
            . " VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $yetiCave->prepare_stmt($sql, [$safe_name, $safe_descr,
            $lot_info['lot-img'], $lot_info['lot-rate'], $lot_info['lot-date'], $lot_info['lot-step'], $cat_id, $user_id]);
            $result = mysqli_stmt_execute($stmt);
            if ($result) {
                $last_id = $yetiCave->last_id();
                header("Location: lot.php?lot_id=" . $last_id);
            } else {
                $error = $yetiCave->error();
            }
        } else {
            $error = "Такой лот уже есть в базе";
        } 
    }
}

if (empty($error)) {
    if ($is_auth == 1) {
        $add_content = include_template('Addtempl.php', [
                    'catsInfo' => $catsArray,
                    'lotInfo' => $lot_info,
                    'user_name' => $user_name,
                    'user_id' => $user_id,
                    'is_auth' => $is_auth,
                    'errors' => $errors,
                    'dictionary' => $dictionary
        ]);
    } else {
        header("Location:_404.php?hdr=Error 403&msg=Пожалуйста, авторизуйтесь!");
    }
} else {
    header("Location:_404.php?hdr=SQL error&msg=" . $error);
}

print($add_content);
