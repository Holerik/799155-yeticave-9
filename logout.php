<?php
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

if (isset($_SESSION['sess_id'])) {
    unset($_SESSION['sess_id']);
}

if (isset($_SESSION['sess_name'])) {
    unset($_SESSION['sess_name']);
}

header("Location: index.php");
