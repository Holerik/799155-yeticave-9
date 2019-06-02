<?php
$error = "";
$host = 'localhost';

require_once('helpers.php');

$catsArray = [];

$yetiCave = new MySqliBase($host, 'root', '', 'yeticave');

if ($yetiCave->ok()) {
    $sql = "SELECT key_id as id, name, code FROM categories ORDER BY key_id";
    $result = $yetiCave->query($sql);
    if ($result) {
        $catsArray = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

if (null !== $yetiCave->error()) {
    header("Location:_404php?hdr=SQL error&msg=" . $yetiCave->error());
}

require_once('functions.php');
require_once('getwinner.php');
