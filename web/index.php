<?php

$TAB = "Plugins";

include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user/");
    exit;
}

Vesta::render("/plugin-manager/templates/list.php");

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
