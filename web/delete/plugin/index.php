<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit;
}

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['plugin'])) {
        $v_plugin = escapeshellarg($_GET['plugin']);
        exec (VESTA_CMD."v-delete-plugin ".$v_plugin, $output, $return_var);
    }
    //check_return_code($return_var,$output);
    unset($output);
}

header("Location: /list/plugin/");
exit;
