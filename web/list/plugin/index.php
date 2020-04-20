<?php

// Tab name
$TAB = "Plugins";

// Include vesta functions
include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");
// Header
include($_SERVER['DOCUMENT_ROOT'] . '/templates/header.html');
// Panel
top_panel($user, $TAB);

echo '<div class="l-center">
<div class="l-sort clearfix noselect">';
if ($user == 'admin') {
    echo '<a class="l-sort__create-btn" href="/add/plugin/" title="' . __('Install plugin') . '"></a>';
}
echo '
<div class="l-sort-toolbar clearfix" style="min-height: 30px;"></div>
</div>
</div>
<div class="l-separator"></div>';


// Start content block
echo '<div class="l-center units vesta-plugins">';
echo '<link rel="stylesheet" href="/css/plugin.css"/>';


$output_plugins = [];
exec(VESTA_CMD . "v-list-plugins json", $output_plugins);
$plugins = json_decode(implode('', $output_plugins), true);

$i = 0;
foreach ($plugins as $plugin) {
    $plugin_name = $plugin['name'];
    $plugin_role = (isset($plugin['user-role']) && in_array($plugin['user-role'], ['all', 'admin'])) ? $plugin['user-role'] : "all";
    $plugin_version = (isset($plugin['version']) && is_string($plugin['version'])) ? $plugin['version'] : "";
    $plugin_desc = (isset($plugin['description']) && is_string($plugin['description'])) ? $plugin['description'] : "";
    $plugin_license = (isset($plugin['license']) && is_string($plugin['license'])) ? $plugin['license'] : "";
    $plugin_homepage = (isset($plugin['homepage']) && is_string($plugin['homepage'])) ? $plugin['homepage'] : "";
    $plugin_repository = (isset($plugin['repository']) && is_string($plugin['repository'])) ? $plugin['repository'] : "";
    $plugin_author_name = (isset($plugin['author']['name']) && is_string($plugin['author']['name'])) ? $plugin['author']['name'] : "";
    $plugin_author_email = (isset($plugin['author']['email']) && is_string($plugin['author']['email'])) ? $plugin['author']['email'] : "";
    $plugin_author_homepage = (isset($plugin['author']['homepage']) && is_string($plugin['author']['homepage'])) ? $plugin['author']['homepage'] : "";

    // Check user role
    if ($plugin_role == 'admin' && $user != 'admin') {
        continue;
    }

    // Check if plugins has a page
    if (file_exists("/usr/local/vesta/web/plugins/$plugin_name")) {
        $plugin_web = "/plugins/$plugin_name/";
        $display = "<a href=\"$plugin_web\">$plugin_name</a>";
    } else {
        $plugin_web = "";
        $display = "$plugin_name";
    }
    ?>

    <div class="l-unit" v_unit_id="<?= $plugin ?>" v_section="plugin">
        <div class="l-unit-toolbar clearfix">
            <!-- l-unit-toolbar__col -->
            <div class="l-unit-toolbar__col l-unit-toolbar__col--right noselect">
                <div class="actions-panel clearfix">
                    <?php if (!empty($plugin_web)) { ?>
                        <div class="actions-panel__col actions-panel__start shortcut-enter" key-action="href"><a
                                    href="<?= $plugin_web ?>"><?= __('Go to plugin') ?> <i></i></a><span
                                    class="shortcut">&nbsp;&#8629;</span></div>
                    <?php }

                    if ($user == "admin") {
                        ?>
                        <div class="actions-panel__col actions-panel__edit" key-action="js">
                            <a id="reinstall_link_<?= $i ?>" class="data-controls do_delete">
                                <?= __('reinstall') ?> <i class="do_delete"></i>
                                <input type="hidden" name="delete_url"
                                       value="/add/plugin/?action=reinstall&plugin-url=<?= urlencode($plugin_repository) ?>"/>
                                <div id="delete_dialog_<?= $i ?>" class="confirmation-text-delete hidden"
                                     title="<?= __('Confirmation') ?>">
                                    <p class="confirmation"><?= __('Are you sure you want to reinstall the plugin %s?', $plugin_name) ?></p>
                                </div>
                            </a>
                        </div>

                        <div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
                            <a id="delete_link_<?= $i ?>" class="data-controls do_delete">
                                <?= __('delete') ?> <i class="do_delete"></i>
                                <input type="hidden" name="delete_url"
                                       value="/delete/plugin/?plugin=<?= $plugin_name ?>&token=<?= $_SESSION['token'] ?>"/>
                                <div id="delete_dialog_<?= $i ?>" class="confirmation-text-delete hidden"
                                     title="<?= __('Confirmation') ?>">
                                    <p class="confirmation"><?= __('Are you sure you want to delete plugin %s?', $plugin_name) ?></p>
                                </div>
                            </a>
                            <span class="shortcut delete">&nbsp;Del</span>
                        </div>
                    <?php } ?>
                </div>
                <!-- /.actiona-panel -->
            </div>
            <!-- l-unit-toolbar__col -->
        </div>
        <!-- /.l-unit-toolbar -->

        <div class="l-unit__col l-unit__col--left clearfix"></div>
        <!-- /.l-unit__col -->

        <div class="l-unit__col l-unit__col--right">
            <div class="l-unit__name">
                <?= $display ?>
            </div>

            <div class="l-unit__desc">
                <?= $plugin_desc ?>
            </div>

            <div class="l-unit__stats">
                <table>
                    <tr>
                        <td>
                            <div class="l-unit__stat-cols clearfix last">
                                <div class="l-unit__stat-col l-unit__stat-col--left compact"><?= __('Version') ?>:</div>
                                <div class="l-unit__stat-col l-unit__stat-col--right">
                                    <b><?= $plugin_version ?></b>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="l-unit__stat-cols clearfix last">
                                <div class="l-unit__stat-col l-unit__stat-col--left compact"><?= __('Role') ?>:</div>
                                <div class="l-unit__stat-col l-unit__stat-col--right">
                                    <b><?= __($plugin_role) ?></b>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="l-unit__stat-cols clearfix last">
                                <div class="l-unit__stat-col l-unit__stat-col--left compact"><?= __('Home Page') ?>:
                                </div>
                                <div class="l-unit__stat-col l-unit__stat-col--right">
                                    <b><a href="<?= $plugin_homepage ?>" target="_blank"><?= $plugin_homepage ?></a></b>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div class="l-unit__stat-cols clearfix last">
                                <div class="l-unit__stat-col l-unit__stat-col--left compact"><?= __('Author') ?>:</div>
                                <div class="l-unit__stat-col l-unit__stat-col--right">
                                    <?php if (!empty($plugin_author_homepage)) { ?>
                                        <b><a href="<?= $plugin_author_homepage ?>" target="_blank"><?= $plugin_author_name ?></a></b>
                                    <?php } else { ?>
                                        <b><?= $plugin_author_name ?></b>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="l-unit__stat-cols clearfix last">
                                <div class="l-unit__stat-col l-unit__stat-col--left compact"><?= __('Email') ?>:</div>
                                <div class="l-unit__stat-col l-unit__stat-col--right">
                                    <b><?= $plugin_author_email ?></b>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="l-unit__stat-cols clearfix last">
                                <div class="l-unit__stat-col l-unit__stat-col--left compact"><?= __('Repository') ?>:
                                </div>
                                <div class="l-unit__stat-col l-unit__stat-col--right">
                                    <b><a href="<?= $plugin_repository ?>" target="_blank"><?= $plugin_repository ?></a></b>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- /.l-unit__stats -->
        </div>
        <!-- /.l-unit__col -->
    </div>
    <!-- /.l-unit -->
    <?php
    $i++;
}

if (isset($backbutton) && $backbutton !== false) {
    echo "<div style=\"margin: 60px 0 30px;\">" .
        "<button class=\"button cancel\" onclick=\"location.href='" . $backbutton . "'\">" . __('Back') . "</button>" .
        "</div>";
}

echo "</div>";

include_once($_SERVER['DOCUMENT_ROOT'] . '/templates/scripts.html');
include_once($_SERVER['DOCUMENT_ROOT'] . '/templates/footer.html');

