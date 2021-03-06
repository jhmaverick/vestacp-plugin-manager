<?php if (!class_exists('Vesta')) exit; ?>
<div class="l-center">
    <div class="l-sort clearfix noselect">
        <a class="l-sort__create-btn" href="/plugin-manager/add/" title="<?= __('Install plugin') ?>"></a>

        <div class="l-sort-toolbar clearfix" style="min-height: 30px;">
            <table>
                <tr>
                    <td class="step-right">
                        <a class="vst" href="/plugin-manager/update-sys-plugins/"><?=__('Update Plugin Manager')?> <i></i></a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="l-separator"></div>

<div class="l-center units vesta-plugins">
    <form action="/plugin-manager/actions/" method="post" id="objects">
        <?php
        $i = 0;
        $plugins = Vesta::exec('v-list-plugins', 'json');
        ksort($plugins);
        foreach ($plugins as $plugin) {
            $plugin_name = $plugin['name'];
            $plugin_version = (isset($plugin['version']) && is_string($plugin['version'])) ? $plugin['version'] : "";
            $plugin_desc = (isset($plugin['description']) && is_string($plugin['description'])) ? $plugin['description'] : "";
            $plugin_license = (isset($plugin['license']) && is_string($plugin['license'])) ? $plugin['license'] : "";
            $plugin_homepage = (isset($plugin['homepage']) && is_string($plugin['homepage'])) ? $plugin['homepage'] : "";
            $plugin_repository = (isset($plugin['repository']) && is_string($plugin['repository'])) ? $plugin['repository'] : "";
            $plugin_author_name = (isset($plugin['author']['name']) && is_string($plugin['author']['name'])) ? $plugin['author']['name'] : "";
            $plugin_author_email = (isset($plugin['author']['email']) && is_string($plugin['author']['email'])) ? $plugin['author']['email'] : "";
            $plugin_author_homepage = (isset($plugin['author']['homepage']) && is_string($plugin['author']['homepage'])) ? $plugin['author']['homepage'] : "";

            if (isset($plugin['enabled']) && $plugin['enabled'] == true) {
                $status = "enabled";
                $status_action = "disable";
                $status_tags = 'suspend';
                $status_confirmation = "Are you sure you want to disable %s?";
            } else {
                $status = "disabled";
                $status_action = "enable";
                $status_tags = 'unsuspend';
                $status_confirmation = "Are you sure you want to enable %s?";
            }

            // Check if plugins has a page
            if (file_exists("/usr/local/vesta/web/plugin/$plugin_name/index.php")) {
                $plugin_web = "/plugin/$plugin_name/";
            } else {
                $plugin_web = "";
            }
            ?>

            <div class="l-unit <?php if ($status == 'disabled') echo 'l-unit--suspended'; ?>" v_unit_id="<?= $plugin ?>"
                 v_section="plugin">
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
                                if (!empty($plugin_repository)) {
                                    ?>
                                    <div class="actions-panel__col actions-panel__restart" key-action="href"><a
                                                href="/plugin-manager/add/?action=update&plugin=<?= urlencode($plugin_name) ?>"><?= __('Update') ?>
                                            <i></i></a></div>
                                <?php } ?>

                                <div class="actions-panel__col actions-panel__suspend shortcut-s" key-action="js">
                                    <a id="<?= $status_tags ?>_link_<?= $i ?>"
                                       class="data-controls do_<?= $status_tags ?>">
                                        <?= __($status_action) ?> <i class="do_<?= $status_tags ?>"></i>
                                        <input type="hidden" name="<?= $status_tags ?>_url"
                                               value="/plugin-manager/actions/?action=<?= $status_action ?>&plugin=<?= urlencode($plugin_name) ?>&token=<?= $_SESSION['token'] ?>"/>
                                        <div id="<?= $status_tags ?>_dialog_<?= $i ?>"
                                             class="confirmation-text-suspention hidden"
                                             title="<?= __('Confirmation') ?>">
                                            <p class="confirmation"><?= __($status_confirmation, $plugin_name) ?></p>
                                        </div>
                                    </a>
                                    <span class="shortcut">&nbsp;S</span>
                                </div>

                                <div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
                                    <a id="delete_link_<?= $i ?>" class="data-controls do_delete">
                                        <?= __('delete') ?> <i class="do_delete"></i>
                                        <input type="hidden" name="delete_url"
                                               value="/plugin-manager/actions/?action=delete&plugin=<?= urlencode($plugin_name) ?>&token=<?= $_SESSION['token'] ?>"/>
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
                        <?= $plugin_name ?>
                    </div>

                    <div class="l-unit__desc">
                        <?= $plugin_desc ?>
                    </div>

                    <div class="l-unit__stats">
                        <table>
                            <tr>
                                <td>
                                    <div class="l-unit__stat-cols clearfix last">
                                        <div class="l-unit__stat-col l-unit__stat-col--left compact">
                                            <?= __('Status') ?>:
                                        </div>
                                        <div class="l-unit__stat-col l-unit__stat-col--right">
                                            <b><?= $status ?></b>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="l-unit__stat-cols clearfix last">
                                        <div class="l-unit__stat-col l-unit__stat-col--left compact">
                                            <?= __('Version') ?>:
                                        </div>
                                        <div class="l-unit__stat-col l-unit__stat-col--right">
                                            <b><?= $plugin_version ?></b>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="l-unit__stat-cols clearfix last">
                                        <div class="l-unit__stat-col l-unit__stat-col--left compact">
                                            <?= __('Home Page') ?>:
                                        </div>
                                        <div class="l-unit__stat-col l-unit__stat-col--right">
                                            <b><a href="<?= $plugin_homepage ?>"
                                                  target="_blank"><?= $plugin_homepage ?></a></b>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="l-unit__stat-cols clearfix last">
                                        <div class="l-unit__stat-col l-unit__stat-col--left compact">
                                            <?= __('Author') ?>:
                                        </div>
                                        <div class="l-unit__stat-col l-unit__stat-col--right">
                                            <?php if (!empty($plugin_author_homepage)) { ?>
                                                <b><a href="<?= $plugin_author_homepage ?>"
                                                      target="_blank"><?= $plugin_author_name ?></a></b>
                                            <?php } else { ?>
                                                <b><?= $plugin_author_name ?></b>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="l-unit__stat-cols clearfix last">
                                        <div class="l-unit__stat-col l-unit__stat-col--left compact">
                                            <?= __('Email') ?>:
                                        </div>
                                        <div class="l-unit__stat-col l-unit__stat-col--right">
                                            <b><?= $plugin_author_email ?></b>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="l-unit__stat-cols clearfix last">
                                        <div class="l-unit__stat-col l-unit__stat-col--left compact">
                                            <?= __('Repository') ?>:
                                        </div>
                                        <div class="l-unit__stat-col l-unit__stat-col--right">
                                            <b><a href="<?= $plugin_repository ?>"
                                                  target="_blank"><?= $plugin_repository ?></a></b>
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
        ?>
    </form>

    <?php
    if (isset($backbutton) && $backbutton !== false) {
        echo "<div style=\"margin: 60px 0 30px;\">" .
            "<button class=\"button cancel\" onclick=\"location.href='" . $backbutton . "'\">" . __('Back') . "</button>" .
            "</div>";
    }
    ?>
</div>

<div id="vstobjects">
    <div class="l-separator"></div>
    <div class="l-center">
        <div class="l-unit-ft">
            <table class="data"></table>

            <div class="l-unit__col l-unit__col--left clearfix"></div>
            <div class="data-count l-unit__col l-unit__col--right clearfix">
                <?php
                if (count($plugins) == 1) {
                    echo "1 " . __("plugin");
                } else {
                    echo count($plugins) . " " . __("plugins");
                }
                ?>
                <br><br>
                <a href="https://github.com/jhmaverick/vestacp-plugin-manager" target="_blank">
                    VestaCP Plugin Manager <?= Vesta::plugin_manager_info('version') ?>
                </a>
            </div>
        </div>
    </div>
</div>
