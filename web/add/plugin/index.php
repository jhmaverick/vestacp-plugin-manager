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

        <label for="reinstall">
            <input type="checkbox" name="reinstall" id="reinstall" class="show-checkbox"/> <?= __("Reinstall if exist") ?>
        </label>
        <br><br>

        <input type="hidden" name="action" value="install"/>
        <button class="button confirm" type="submit"><?= __("Install") ?></button>
        <button class="button cancel" type="button" onclick="location.href='/list/plugin/'"><?= __('Back') ?></button>
    </form>
    <?php
}

function action_install($plugin_url, $reinstall = false) {
    $reinstall = ($reinstall == true) ? "yes" : "";

    echo "<pre>";
    system(VESTA_CMD . "v-add-plugin \"$plugin_url\"");
    echo "</pre>";

    global $backbutton;
    $backbutton = "/list/plugin/";
}

if (isset($_GET['action']) && $_GET['action'] == "reinstall"
    && isset($_GET['plugin-url']) && !empty($_GET['plugin-url'])
) {
    $plugin_url = trim($_GET['plugin-url']);
    action_install($plugin_url, true);
} else if (isset($_POST['action']) && $_POST['action'] == "install"
    && isset($_POST['plugin-url']) && !empty($_POST['plugin-url'])
) {
    $plugin_url = trim($_POST['plugin-url']);
    $reinstall = (isset($_POST['reinstall']));

    action_install($plugin_url, $reinstall);
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

