<?php

/**
 * @param string $template Full path to the template file.
 * @param array $vars Variables to extract.
 * @param array $args <p>Extra arguments.
 *  * tab - Tab name to top_panel function.
 * </p>
 */
function render_template($template, $vars = [], $args = []) {
    global $user, $TAB;

    $tab_name = (isset($args['tab'])) ? $args['tab'] : $TAB;

    // Header
    include($_SERVER['DOCUMENT_ROOT'] . '/templates/header.html');

    // Panel
    top_panel(empty($_SESSION['look']) ? $_SESSION['user'] : $_SESSION['look'], $tab_name);

    // Extract variables
    if (is_array($vars)) {
        extract($vars, EXTR_SKIP);
    }

    // Body
    if (preg_match("/\.(html|php)$/", $template) && file_exists($template)) {
        @include($template);
    }

    // Including common js files
    @include_once($_SERVER['DOCUMENT_ROOT'] . '/templates/scripts.html');

    // Footer
    include($_SERVER['DOCUMENT_ROOT'] . '/templates/footer.html');
}

/**
 * Get all plugins installed
 */
function get_plugins() {
    exec(VESTA_CMD . "v-list-plugins json", $output);
    return json_decode(implode('', $output), true);

}

/**
 *  Get plugin data
 *
 * @param string $plugin Plugin name.
 */
function get_plugin_data($plugin) {
    exec(VESTA_CMD . "v-list-plugin \"$plugin\" json", $output);
    return json_decode(implode('', $output), true);

}

