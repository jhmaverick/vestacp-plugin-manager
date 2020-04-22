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

// Header
include($_SERVER['DOCUMENT_ROOT'] . '/templates/header.html');
// Panel
top_panel($user, $TAB);

// Start content block
echo '<div class="l-center units vesta-plugins-add">';
echo '<link rel="stylesheet" href="/css/plugin.css"/>';

function default_template() {
    ?>
    <form action="index.php" method="post">
        <h1>Install Plugin</h1>

        <p class="vst-text"><b><?= __("Github repository") ?></b></p>
        <input type="text" class="vst-input" name="plugin-url" required/>
        <br><br>

        <input type="hidden" name="action" value="install"/>
        <button class="button confirm" type="submit"><?= __("Install") ?></button>
        <button class="button cancel" type="button" onclick="location.href='/list/plugin/'"><?= __('Back') ?></button>
    </form>
    <?php
}

function action_install($plugin_url) {
    echo "<pre>";
    system(VESTA_CMD . "v-add-plugin \"$plugin_url\"");
    echo "</pre>";

    global $backbutton;
    $backbutton = "/add/plugin/";
}

function action_update($plugin_name) {
    echo "<pre>";
    system(VESTA_CMD . "v-update-plugin \"$plugin_name\"");
    echo "</pre>";

    echo '<script>window.history.pushState("","","/add/plugin/");</script>';

    global $backbutton;
    $backbutton = "/list/plugin/";
}

if (isset($_GET['action']) && $_GET['action'] == "update"
    && isset($_GET['plugin']) && !empty($_GET['plugin'])
) {
    $plugin_name = trim($_GET['plugin']);
    action_update($plugin_name);
} else if (isset($_POST['action']) && $_POST['action'] == "install"
    && isset($_POST['plugin-url']) && !empty($_POST['plugin-url'])
) {
    $plugin_url = trim($_POST['plugin-url']);
    action_install($plugin_url);
} else {
    default_template();
}

if (isset($backbutton) && $backbutton !== false) {
    echo "<div style=\"margin: 60px 0 30px;\">" .
        "<button class=\"button cancel\" onclick=\"location.href='" . $backbutton . "'\">" . __('Back') . "</button>" .
        "</div>";
}

echo "</div>";

include_once($_SERVER['DOCUMENT_ROOT'] . '/templates/scripts.html');
include_once($_SERVER['DOCUMENT_ROOT'] . '/templates/footer.html');

