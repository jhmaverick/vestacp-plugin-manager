#!/bin/bash

# Delete Plugin Manager as remove changes in the VestaCP web dir
#
# The script will try remove changes in the web even the plugin manages is not installed.

if [[ "$(id -u)" != "0" ]]; then
   echo "This script must be run as root"
   exit 1
fi

escape() {
    content=$(echo "$1" | sed "s|[\`~!@#$%^&*()_=+{}\|;:\"',./?-]|\\\&|g")
    content=${content//[/\\[}
    content=${content//]/\\]}

    echo "$content"
}

# Remove changes in vesta web files
remove_from_vesta_web() {
    # Backup Web directory
    backup="/backup/vesta-web_$(date +'%Y-%m-%d_%H-%M-%S').tar.gz"
    echo "Backing up /usr/local/vesta/web"
    mkdir -p /backup
    bash -c "cd /usr/local/vesta && tar -zvcf $backup ./web"
    echo -e "Backup saved to $backup\n"

    echo "Removing from /usr/local/vesta/web/inc/main.php"
    sed -Ei "/$(escape '@include_once($_SERVER["DOCUMENT_ROOT"] . "/plugin-manager/inc/main.php");')/d" /usr/local/vesta/web/inc/main.php

    echo "Removing from /usr/local/vesta/web/templates/header.html"
    sed -Ei "/$(escape '<?php Vesta::do_action("init"); ?>')/d" /usr/local/vesta/web/templates/header.html
    sed -Ei "/$(escape '<?php Vesta::do_action("head"); ?>')/d" /usr/local/vesta/web/templates/header.html
    sed -Ei "s|$(escape ' <?php Vesta::do_action("body_class"); ?>')||" /usr/local/vesta/web/templates/header.html

    echo "Removing from /usr/local/vesta/web/templates/admin/panel.html"
    sed -Ei "/$(escape '<?php Vesta::do_action("panel_init"); ?>')/d" /usr/local/vesta/web/templates/admin/panel.html
    sed -Ei "/$(escape '<?php Vesta::do_action("header_menu"); ?>')/d" /usr/local/vesta/web/templates/admin/panel.html
    sed -Ei "/$(escape '<?php Vesta::do_action("header_tray"); ?>')/d" /usr/local/vesta/web/templates/admin/panel.html
    sed -Ei "/$(escape '<?php Vesta::do_action("menu"); ?>')/d" /usr/local/vesta/web/templates/admin/panel.html
    sed -Ei "/$(escape '<?php Vesta::do_action("pre_load_template"); ?>')/d" /usr/local/vesta/web/templates/admin/panel.html

    echo "Removing from /usr/local/vesta/web/templates/user/panel.html"
    sed -Ei "/$(escape '<?php Vesta::do_action("panel_init"); ?>')/d" /usr/local/vesta/web/templates/user/panel.html
    sed -Ei "/$(escape '<?php Vesta::do_action("header_menu"); ?>')/d" /usr/local/vesta/web/templates/user/panel.html
    sed -Ei "/$(escape '<?php Vesta::do_action("header_tray"); ?>')/d" /usr/local/vesta/web/templates/user/panel.html
    sed -Ei "/$(escape '<?php Vesta::do_action("menu"); ?>')/d" /usr/local/vesta/web/templates/user/panel.html
    sed -Ei "/$(escape '<?php Vesta::do_action("pre_load_template"); ?>')/d" /usr/local/vesta/web/templates/user/panel.html

    echo "Removing from /usr/local/vesta/web/templates/admin/list_server_info.html"
    sed -Ei "/$(escape '<?php Vesta::do_action("init"); ?>')/d" /usr/local/vesta/web/templates/admin/list_server_info.html
    sed -Ei "/$(escape '<?php Vesta::do_action("head"); ?>')/d" /usr/local/vesta/web/templates/admin/list_server_info.html
    sed -Ei "s| class=\"$(escape '<?php Vesta::do_action("body_class"); ?>')\"||" /usr/local/vesta/web/templates/admin/list_server_info.html

    echo "Removing from /usr/local/vesta/web/templates/footer.html"
    sed -Ei "/$(escape '<?php Vesta::do_action("footer"); ?>')/d" /usr/local/vesta/web/templates/footer.html

    echo -e "\nAll changes in the VestaCP web have been removed"
}

if [[ -d /usr/local/vesta/plugin-manager ]]; then
    # Removal process for when the plugin manager is installed

    # Disable all plugins
    if [[ -d /usr/local/vesta/plugins ]]; then
        echo "Disabling plugins"

        for f in /usr/local/vesta/plugins/*; do
            plugin="$(basename -- "$f")"
            /usr/local/vesta/plugin-manager/bin/v-disable-plugin "$plugin"
        done
    fi

    # Remove vesta web changes
    remove_from_vesta_web
    echo

    # Remove web link
    rm -rf /usr/local/vesta/web/plugin-manager

    # Remove from bin
    for f in /usr/local/vesta/plugin-manager/bin/*; do
        bin_name="$(basename -- "$f")"

        if [[ -L "/usr/local/vesta/bin/$bin_name" ]]; then
            unlink "/usr/local/vesta/bin/$bin_name"
        fi
    done

    # Remove plugin manager files
    rm -rf /usr/local/vesta/plugin-manager

    echo -e "\nPlugin manager has been uninstalled"
else
    # Removing rest of an old installation

    # Remove vesta web changes
    remove_from_vesta_web
fi
