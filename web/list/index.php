<?php

$TAB = "Plugins";

include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user/");
    exit;
}

Vesta::render(__DIR__ . "/../templates/list.php", [], ['tab' => $TAB]);

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
