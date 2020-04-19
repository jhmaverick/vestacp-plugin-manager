<?php

$TAB = "Plugins";

include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

// Header
if (!isset($_GET['action'])) {
    include($_SERVER['DOCUMENT_ROOT'] . '/templates/header.html');
}

// Panel
if (!isset($_GET['action'])) {
    top_panel($user, $TAB);
}

echo "<div class=\"l-center units app-installer\">" .
    "<link rel=\"stylesheet\" href=\"/plugins/style.css\"/>";

if (isset($_POST['plugin-link']) && !empty($_POST['plugin-link'])) {
    $plugin_link = trim($_POST['plugin-link']);

    echo "<pre>";
    system(VESTA_CMD . "v-plugin-install \"$plugin_link\"");
    echo "</pre>";

    $backbutton = $_SERVER['REQUEST_URI'];
} else if (isset($_GET['uninstall']) && !empty($_GET['uninstall'])) {
    $plugin_name = trim($_GET['uninstall']);

    echo "<pre>";
    system(VESTA_CMD . "v-plugin-uninstall \"$plugin_name\"");
    echo "</pre>";

    $backbutton = "/plugins";
} else {
    if ($_SESSION['user'] == 'admin' && !isset($_SESSION['look'])) {
        ?>
        <form action="index.php" method="post">
            <h1>Install Plugin</h1>

            <p class="vst-text">Github link</p>
            <input type="text" class="vst-input" name="plugin-link"/>
            <button class="button confirm" type="submit">Install</button>
        </form>
    <?php } ?>

    <h1>Plugins</h1>
    <ul>
        <?php
        exec(VESTA_CMD . "v-plugin-list json", $output, $return_var);
        $plugins = json_decode(implode('', $output), true);

        foreach ($plugins as $plugin) {
            $dir_name = $plugin['dir_name'];
            $plugin_path = $plugin['path'];
            $plugin_name = (isset($plugin['name']) && !empty($plugin['name'])) ? $plugin['name'] : $dir_name;

            // Check if plugins has a page
            if (file_exists("$plugin_path/index.php")) {
                $display = "<a href=\"/plugins/$dir_name\">$plugin_name</a>";
            } else {
                $display = "$plugin_name";
            }

            echo "<li>$display <a href=\"/plugins/?uninstall=$dir_name\">Uninstall</a></li>";
        }
        ?>
    </ul>
    <?php
}

// Rodapé do Vesta
if (isset($backbutton) && $backbutton !== false) {
    echo "<div style=\"margin: 60px 0 30px;\">" .
        "<button class=\"button cancel\" onclick=\"location.href='" . $backbutton . "'\">" . __('Back') . "</button>" .
        "</div>";
}

echo "</div>";

include_once($_SERVER['DOCUMENT_ROOT'] . '/templates/scripts.html');
include_once($_SERVER['DOCUMENT_ROOT'] . '/templates/footer.html');

