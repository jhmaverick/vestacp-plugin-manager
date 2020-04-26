<?php

// Tab name
$TAB = "Plugins";

// Include vesta functions
include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == "update" && isset($_GET['plugin']) && !empty($_GET['plugin'])) {
    $output = Vesta::exec('v-update-plugin', trim($_GET['plugin']));
    Vesta::render_cmd_output($output, null, "/plugin-manager/");
    echo '<script>window.history.pushState("","","/plugin-manager/add/");</script>';
} else if (isset($_POST['action']) && $_POST['action'] == "install" && isset($_POST['plugin-url']) && !empty($_POST['plugin-url'])) {
    $output = Vesta::exec('v-add-plugin', trim($_POST['plugin-url']));
    Vesta::render_cmd_output($output, null, "/plugin-manager/add/");
} else {
    Vesta::render("/plugin-manager/templates/add.php");
}
