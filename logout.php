<?php
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id']; 
        $visit_cookie = 'visit_' . $user_id;
        if (isset($_SESSION[$visit_cookie])) {
            $_SESSION[$visit_cookie] = [];
            header("Location: index.php");
        }
    }
}
