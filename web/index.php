<?php

$TAB = "Plugins";

include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

// Blocks other users from accessing the page, but still allows the administrator to access when logged as the user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user/");
    exit;
}

Vesta::render("/plugin-manager/templates/list.php");

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
