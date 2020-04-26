<?php

// Tab name
$TAB = "Plugins";

// Include vesta functions
include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user/");
    exit;
}

$output = Vesta::exec('v-update-sys-plugins');
Vesta::render_cmd_output($output, null, "/plugin-manager/");
echo '<script>window.history.pushState("","","/plugin-manager/update-sys-plugins/");</script>';
