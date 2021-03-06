<?php

class Vesta {

    private static $filters = [];
    private static $actions = [];

    /**
     * Render template
     *
     * If using a template ".php" add "<?php if (!class_exists('Vesta')) exit; ?>" in the first line to prevent access directly in browser.
     *
     * @param string $template HTML or full path to the template file.
     * @param array $__args <p>
     *  Arguments.
     *
     *  * string plugin       - Plugin name to use as template directory.
     *  * string tab          - Tab name to top_panel function. If not defined use global $TAB.
     *  * string template_dir - Full path to directory. Default: /usr/local/vesta/web.
     *
     *  The rest of the arguments will be extracted
     * </p>
     */
    public static function render($template, $__args = []) {
        $user = $GLOBALS['user'];
        $TAB = (isset($__args['tab'])) ? $__args['tab'] : $GLOBALS['TAB'];

        // Header
        include($_SERVER['DOCUMENT_ROOT'] . '/templates/header.html');

        // Panel
        top_panel(empty($_SESSION['look']) ? $_SESSION['user'] : $_SESSION['look'], $TAB);

        if (isset($__args['template_dir']) && !empty($__args['template_dir'])) {
            // Use full path
            $template_dir = rtrim($__args['template_dir'], '/');
        } else if (isset($__args['plugin']) && !empty($__args['plugin'])) {
            // Use the plugin directory
            $template_dir = $_SERVER['DOCUMENT_ROOT'] . "/plugin/" . trim($__args['plugin'], '/');
        } else {
            // Use web root
            $template_dir = $_SERVER['DOCUMENT_ROOT'];
        }

        // Remove the arguments from the method and extract the rest
        if (isset($__args['template'])) unset($__args['template']);
        if (isset($__args['template_dir'])) unset($__args['template_dir']);
        if (isset($__args['tab'])) unset($__args['tab']);

        if (is_array($__args) && count($__args) > 0) {
            extract($__args, EXTR_SKIP);
        }

        // Body
        if (preg_match("/\.(html|php)$/", $template) && file_exists("$template_dir/" . ltrim($template))) {
            // Get template by path
            @include_once("$template_dir/" . ltrim($template));
        } else if (!preg_match("/\.(html|php)$/", $template)) {
            // Show HTML
            echo $template;
        }

        // Including common js files
        @include_once($_SERVER['DOCUMENT_ROOT'] . '/templates/scripts.html');

        // Footer
        include($_SERVER['DOCUMENT_ROOT'] . '/templates/footer.html');
    }

    /**
     * Layout to show an output from cmd
     *
     * @param string $output
     * @param string $title
     * @param string $backbutton
     */
    public static function render_cmd_output($output, $title = null, $backbutton = null) {
        $tpl = "<div class=\"l-center units vesta-cmd-output\">\n";

        if (!empty($title)) $tpl .= "<h1>$title</h1>\n";

        $tpl .= "<div class=\"output_console\">\n";
        $tpl .= "<pre>$output</pre>\n";
        $tpl .= "</div>\n";

        if (isset($backbutton) && !empty($backbutton)) {
            $tpl .= "<div class=\"backbutton\">\n" .
                "<button class=\"button cancel\" onclick=\"location.href='" . $backbutton . "'\">" . __('Back') . "</button>\n" .
                "</div>\n";
        }

        $tpl .= "</div>";

        Vesta::render($tpl);
    }

    /**
     * Hook to modify a filter
     *
     * @param string $tag
     * @param callable $callback
     * @param int $priority
     */
    public static function add_filter($tag, $callback, $priority = null) {
        if (!is_string($tag)) return;
        $priority = (is_int($priority) && $priority > 0) ? $priority : 10;

        if (!isset(self::$filters[$tag])) self::$filters[$tag] = [];
        if (!isset(self::$filters[$tag][$priority])) self::$filters[$tag][$priority] = [];

        if (is_callable($callback)) {
            self::$filters[$tag][$priority][] = $callback;
        }
    }

    /**
     * Filter a value
     *
     * @param string $tag Name of the filter
     * @param mixed ...$init_value Value to filter and optional args
     * @return mixed
     */
    public static function apply_filters($tag, ...$init_value) {
        if (isset(self::$filters[$tag])) {
            $tag_filters = self::$filters[$tag];
            ksort($tag_filters);

            foreach ($tag_filters as $priority => $list) {
                foreach ($list as $i => $callback) {
                    $init_value[0] = call_user_func_array($callback, $init_value);
                }
            }
        }

        return $init_value[0];
    }

    /**
     * Add action to be called in specific point during execution
     *
     * @param string $tag
     * @param callable $callback
     * @param int $priority
     */
    public static function add_action($tag, $callback, $priority = null) {
        if (!is_string($tag)) return;
        $priority = (is_int($priority) && $priority > 0) ? $priority : 10;

        if (!isset(self::$actions[$tag])) self::$actions[$tag] = [];
        if (!isset(self::$actions[$tag][$priority])) self::$actions[$tag][$priority] = [];

        if (is_callable($callback)) {
            self::$actions[$tag][$priority][] = $callback;
        }
    }

    /**
     * Execute an action
     *
     * @param string $tag
     * @param mixed ...$args
     */
    public static function do_action($tag, ...$args) {
        $args = is_array($args) ? $args : [];

        if (isset(self::$actions[$tag])) {
            $tag_actions = self::$actions[$tag];
            ksort($tag_actions);

            foreach ($tag_actions as $priority => $list) {
                foreach ($list as $i => $callback) {
                    call_user_func_array($callback, $args);
                }
            }
        }
    }

    /**
     * Add CSS on head
     *
     * @param string $link
     * @param int $priority
     */
    public static function add_css($link, $priority = 10) {
        if (!is_string($link)) return;

        self::add_filter("css", function ($list) use ($link) {
            if (is_array($list)) $list[] = $link;
            return $list;
        }, $priority);
    }

    /**
     * Add JS on head
     *
     * @param string $link
     * @param int $priority
     */
    public static function add_js($link, $priority = 10) {
        if (!is_string($link)) return;

        self::add_filter("js", function ($list) use ($link) {
            if (is_array($list)) $list[] = $link;
            return $list;
        }, $priority);
    }

    /**
     * Add item on header menu
     *
     * @param string $name Name to display.
     * @param string $link
     * @param string $page_tab Used to marquee menu as active if the link is from a vesta page. Name will be used if not defined.
     * @param string $local The place where the menu will be displayed.
     * @param int $priority
     */
    public static function add_header_menu($name, $link = null, $page_tab = null, $local = 'all_users', $priority = 10) {
        if (!is_string($name) || empty($name)) return;

        $item = [];

        $item['name'] = $name;
        if (is_string($link) && !empty($link)) $item['link'] = $link;
        if (is_string($page_tab) && !empty($page_tab)) $item['page_tab'] = $page_tab;
        if (is_string($local) && !empty($local)) $item['local'] = $local;

        Vesta::add_filter("header_menu", function ($items) use ($item) {
            $items[] = $item;
            return $items;
        }, $priority);
    }

    /**
     * Add item on left menu(l-stat)
     *
     * Not displayed in default vesta theme.
     *
     * @param string $name Name to display.
     * @param string $link
     * @param string $page_tab Name will be used if not defined.
     * @param array $sub_items <p>
     *  Can be used to add a submenu or display an information.
     *  * name  - Name to display
     *  * value - (optional)
     *  * link  - (optional)
     * </p>
     * @param string $local The place where the menu will be displayed.
     * @param int $priority
     */
    public static function add_menu($name, $link, $page_tab = null, $sub_items = [], $local = 'all_users', $priority = 10) {
        if (!is_string($name) || empty($name)) return;
        if (!is_string($link) || empty($link)) return;

        $item = [];

        $item['name'] = $name;
        $item['link'] = $link;
        if (is_string($page_tab) && !empty($page_tab)) $item['page_tab'] = $page_tab;
        if (is_string($local) && !empty($local)) $item['local'] = $local;
        if (is_array($sub_items) && !empty($sub_items)) $item['sub_items'] = $sub_items;

        Vesta::add_filter("menu", function ($items) use ($item) {
            $items[] = $item;
            return $items;
        }, $priority);
    }

    /**
     * Return the place where the script is running.
     *
     * @return string
     */
    public static function current_panel() {
        if (isset($_SESSION['user']) && ($_SESSION['user'] != 'admin' || isset($_SESSION['look']) && !empty($_SESSION['look']))) {
            return 'user_panel';
        } else if (isset($_SESSION['user']) && $_SESSION['user'] == 'admin') {
            return 'admin_panel';
        } else {
            return 'external';
        }
    }

    /**
     * Exec command
     *
     * @param string $cmd
     * @param mixed ...$args
     * @return string|array
     */
    public static function exec($cmd, ...$args) {
        if (file_exists("/usr/local/vesta/bin/$cmd")) $cmd = VESTA_CMD . "$cmd";

        $final_args = [];
        foreach ($args as $arg) {
            if (!empty($arg) && (is_string($arg) || is_numeric($arg))) {
                $final_args[] = escapeshellarg($arg);
            }
        }

        $result = shell_exec($cmd . ' ' . implode(' ', $final_args));

        if (trim(end($final_args), "\"'") == 'json') {
            return json_decode($result, true);
        }

        return $result;
    }

    /**
     * Get information about plugin manager
     *
     * @param string $index
     * @return mixed|null
     */
    public static function plugin_manager_info($index = null) {
        $pm_data = file_get_contents('/usr/local/vesta/plugin-manager/vestacp.json');
        $pm_data = json_decode($pm_data, true);

        if (!empty($index)) {
            return (isset($pm_data[$index])) ? $pm_data[$index] : null;
        } else {
            return $pm_data;
        }
    }

    /**
     * Checks if it's a plugin page
     *
     * @param string $plugin_name
     * @return bool
     */
    public static function is_plugin_page($plugin_name) {
        return (preg_match("/^\/plugin\/$plugin_name($|\/*)/", $_SERVER['REQUEST_URI']) != false);
    }
}

// Insert additional elements in the head
Vesta::add_action('head', function () {
    $list_css = Vesta::apply_filters('css', []);

    foreach ($list_css as $link) {
        echo "<link rel=\"stylesheet\" href=\"$link\" />\n";
    }

    $list_js = Vesta::apply_filters('js', []);

    foreach ($list_js as $link) {
        echo "<script type=\"application/javascript\" src=\"$link\"></script>\n";
    }
});

// Insert menus from "header_menu" filter in l-stat
// Action called in the header menu append
Vesta::add_action('header_menu', function () {
    global $TAB;

    $current_panel = Vesta::current_panel();
    $list_header_menu = Vesta::apply_filters('header_menu', []);

    foreach ($list_header_menu as $item) {
        if (is_array($item)) {
            if (!isset($item['name']) || !is_string($item['name']) || empty($item['name'])) continue;
            // all_users|admin_panel|user_panel
            $local = (isset($item['local']) && is_string($item['local'])) ? $item['local'] : "all_users";
            if (!($current_panel == $local || ($local == 'all_users' && in_array($current_panel, ['admin_panel', 'user_panel'])))) continue;

            $name = $item['name'];
            $link = (isset($item['link']) && is_string($item['link']) && !empty($item['link'])) ? $item['link'] : "javascript:void(0);";
            $page_tab = (isset($item['page_tab']) && is_string($item['page_tab'])) ? $item['page_tab'] : $name;

            $classes = "l-menu__item l-menu__item--show" . ((!empty($TAB) && strtoupper($TAB) == strtoupper($page_tab)) ? " l-menu__item--active" : "");
            $classes .= (isset($item['classes']) && is_string($item['classes']) && !empty($item['classes'])) ? " " . $item['classes'] : "";

            echo "<div class=\"$classes\"><a href=\"$link\">" . __($name) . "</a></div>\n";
        }
    }
});

// Insert menus from "menu" filter in l-stat
// Has a display none by default to prevent break vesta layout
// To display this items you need apply a different stylesheet in vesta
Vesta::add_action('menu', function () {
    global $TAB;

    $current_panel = Vesta::current_panel();
    $list_header_menu = Vesta::apply_filters('menu', []);

    foreach ($list_header_menu as $item) {
        if (is_array($item)) {
            if (!isset($item['name']) || !is_string($item['name']) || empty($item['name'])) continue;
            // all_users|admin_panel|user_panel
            $local = (isset($item['local']) && is_string($item['local'])) ? $item['local'] : "all_users";
            if (!($current_panel == $local || ($local == 'all_users' && in_array($current_panel, ['admin_panel', 'user_panel'])))) continue;

            $name = $item['name'];
            $link = (isset($item['link']) && is_string($item['link']) && !empty($item['link'])) ? $item['link'] : "javascript:void(0);";
            $page_tab = (isset($item['page_tab']) && is_string($item['page_tab'])) ? $item['page_tab'] : $name;

            $classes = "l-stat__col l-stat__col--show" . ((!empty($TAB) && strtoupper($TAB) == strtoupper($page_tab)) ? " l-stat__col--active" : "");
            $classes .= (isset($item['classes']) && is_string($item['classes']) && !empty($item['classes'])) ? " " . $item['classes'] : "";

            echo "<div class=\"$classes\">";
            echo "<a href=\"$link\">";
            echo "<div class=\"l-stat__col-title\">" . __($name) . "</div>";
            echo "</a>";

            if (isset($item['sub_items']) && is_array($item['sub_items'])) {
                echo "<ul>";

                foreach ($item['sub_items'] as $sub_item) {
                    if (isset($sub_item['name']) && is_string($sub_item['name']) && !empty($sub_item['name'])) {
                        $sub_item_value = (isset($sub_item['value'])) ? ": <span>{$sub_item['value']}</span>" : "";

                        if (isset($sub_item['link']) && is_string($sub_item['link']) && !empty($sub_item['link'])) {
                            echo "<li><a href=\"{$sub_item['link']}\">" . __($sub_item['name']) . "$sub_item_value</a></li>";
                        } else {
                            echo "<li>" . __($sub_item['name']) . "$sub_item_value</li>";
                        }
                    } else if (is_string($sub_item) && !empty($sub_item)) {
                        echo "<li>" . __($sub_item) . "</li>";
                    }
                }

                echo "</ul>";
            }

            echo "</div>\n";
        }
    }
});

// Run before header.html
Vesta::add_action('init', function () {
    Vesta::add_css('/plugin-manager/css/style.css', 5);
}, 5);

// Run before panels (admin/panel.html and user/panel.html)
Vesta::add_action('panel_init', function () {
    global $panel, $user;

    // Add Default header menus
    Vesta::add_header_menu('Packages', '/list/package/', 'PACKAGE', 'admin_panel', 5);
    Vesta::add_header_menu('IP', '/list/ip/', 'IP', 'admin_panel', 5);
    Vesta::add_header_menu('Graphs', '/list/rrd/', 'RRD', 'admin_panel', 5);
    Vesta::add_header_menu('Statistics', '/list/stats/', 'STATS', 'all_users', 5);
    Vesta::add_header_menu('Log', '/list/log/', 'LOG', 'all_users', 5);
    Vesta::add_header_menu('Updates', '/list/updates/', 'UPDATES', 'admin_panel', 5);
    if ((isset($_SESSION['FIREWALL_SYSTEM'])) && (!empty($_SESSION['FIREWALL_SYSTEM'])))
        Vesta::add_header_menu('Firewall', '/list/firewall/', 'FIREWALL', 'admin_panel', 5);
    if ((isset($_SESSION['FILEMANAGER_KEY'])) && (!empty($_SESSION['FILEMANAGER_KEY'])))
        Vesta::add_header_menu('File Manager', '/list/directory/', 'FILEMANAGER', 'all_users', 5);
    if ($_SESSION['SOFTACULOUS'] == 'yes')
        Vesta::add_header_menu('Apps', '/softaculous/', 'all_users', 5);
    Vesta::add_header_menu('Plugins', '/plugin-manager/', 'PLUGINS', 'admin_panel', 5);
    Vesta::add_header_menu('Server', '/list/server/', 'SERVER', 'admin_panel', 5);

    // Default l-stats menus
    if (Vesta::current_panel() == 'user_panel') {
        $sub_items = [
            ['name' => 'Disk', 'value' => humanize_usage_size($panel[$user]['U_DISK']) . " " . humanize_usage_measure($panel[$user]['U_DISK'])],
            ['name' => 'Bandwidth', 'value' => humanize_usage_size($panel[$user]['U_BANDWIDTH']) . ' ' . humanize_usage_measure($panel[$user]['U_BANDWIDTH'])],
        ];
    } else {
        $sub_items = [
            ['name' => 'users', 'value' => $panel[$user]['U_USERS']],
            ['name' => 'spnd', 'value' => $panel[$user]['SUSPENDED_USERS']],
        ];
    }
    Vesta::add_menu('USER', '/list/user/', 'USER', $sub_items, 'all_users', 5);

    if ($panel[$user]['WEB_DOMAINS'] != "0") {
        $sub_items = [
            ['name' => 'domains', 'value' => $panel[$user]['U_WEB_DOMAINS']],
            ['name' => 'aliases', 'value' => $panel[$user]['U_WEB_ALIASES']],
            ['name' => 'spnd', 'value' => $panel[$user]['SUSPENDED_WEB']],
        ];

        Vesta::add_menu('WEB', '/list/web/', 'WEB', $sub_items, 'all_users', 5);
    }

    if ($panel[$user]['DNS_DOMAINS'] != "0") {
        $sub_items = [
            ['name' => 'domains', 'value' => $panel[$user]['U_DNS_DOMAINS']],
            ['name' => 'records', 'value' => $panel[$user]['U_DNS_RECORDS']],
            ['name' => 'spnd', 'value' => $panel[$user]['SUSPENDED_DNS']],
        ];

        Vesta::add_menu('DNS', '/list/dns/', 'DNS', $sub_items, 'all_users', 5);
    }

    if ($panel[$user]['MAIL_DOMAINS'] != "0") {
        $sub_items = [
            ['name' => 'domains', 'value' => $panel[$user]['U_MAIL_DOMAINS']],
            ['name' => 'accounts', 'value' => $panel[$user]['U_MAIL_ACCOUNTS']],
            ['name' => 'spnd', 'value' => $panel[$user]['SUSPENDED_MAIL']],
        ];

        Vesta::add_menu('MAIL', '/list/mail/', 'MAIL', $sub_items, 'all_users', 5);
    }

    if ($panel[$user]['DATABASES'] != "0") {
        $sub_items = [
            ['name' => 'databases', 'value' => $panel[$user]['U_DATABASES']],
            ['name' => 'spnd', 'value' => $panel[$user]['SUSPENDED_DB']],
        ];

        Vesta::add_menu('DB', '/list/db/', 'DB', $sub_items, 'all_users', 5);
    }

    if ($panel[$user]['CRON_JOBS'] != "0") {
        $sub_items = [
            ['name' => 'jobs', 'value' => $panel[$user]['U_CRON_JOBS']],
            ['name' => 'spnd', 'value' => $panel[$user]['SUSPENDED_CRON']],
        ];

        Vesta::add_menu('CRON', '/list/cron/', 'CRON', $sub_items, 'all_users', 5);
    }

    if ($panel[$user]['BACKUPS'] != "0") {
        $sub_items = [
            ['name' => 'backups', 'value' => $panel[$user]['U_BACKUPS']],
        ];

        Vesta::add_menu('BACKUP', '/list/backup/', 'BACKUP', $sub_items, 'all_users', 5);
    }

    // Add plugins
    $plugins_list = $plugins = Vesta::exec('v-list-plugins', 'json');
    $total_enabled = 0;
    $total_disabled = 0;

    foreach ($plugins_list as $plugin) {
        if (isset($plugin['enabled']) && $plugin['enabled'] == true) {
            $total_enabled++;
        } else {
            $total_disabled++;
        }
    }

    Vesta::add_menu('Plugins', '/plugin-manager/', 'Plugins', [
        ['name' => 'Installed', 'value' => count($plugins_list)],
        ['name' => 'Enabled', 'value' => $total_enabled],
        ['name' => 'Disabled', 'value' => $total_disabled],
    ], 'admin_panel', 5);
}, 5);

// Include each plugin functions in an isolated scope
function load_plugin($plugin_name) {
    if (file_exists("/usr/local/vesta/web/plugin/$plugin_name/functions.php")) {
        try {
            include_once "/usr/local/vesta/web/plugin/$plugin_name/functions.php";
        } catch (Exception $e) {
        }
    }
}

$plugins = Vesta::exec('v-list-plugins', 'json');
foreach ($plugins as $plugin) {
    load_plugin($plugin['name']);
}

