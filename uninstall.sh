#!/bin/bash

if [[ "$(id -u)" != "0" ]]; then
   echo "This script must be run as root"
   exit 1
elif [[ ! -d /usr/local/vesta/web/plugin-manager ]]; then
    echo "Plugin manager is not installed."
    exit 1
fi

escape() {
    content=$(echo "$1" | sed "s|[\`~!@#$%^&*()_=+{}\|;:\"',./?-]|\\\&|g")
    content=${content//[/\\[}
    content=${content//]/\\]}

    echo "$content"
}

# Remove all plugins
if [[ -d /usr/local/vesta/plugins ]]; then
    for f in /usr/local/vesta/plugins/*; do
        plugin="$(basename -- "$f")"
        /opt/nucleocp/bin/v-delete-plugin "$plugin"
    done

    rm -rf /usr/local/vesta/plugins
fi

# Remove changes in vesta files
sed -Ei "/$(escape '@include_once($_SERVER["DOCUMENT_ROOT"] . "/plugin-manager/inc/main.php");')/d" /usr/local/vesta/web/inc/main.php

sed -Ei "/$(escape '<?php Vesta::do_action("init"); ?>')/d" /usr/local/vesta/web/templates/header.html
sed -Ei "/$(escape '<?php Vesta::do_action("head"); ?>')/d" /usr/local/vesta/web/templates/header.html
sed -Ei "/$(escape '<?php Vesta::do_action("body"); ?>')/d" /usr/local/vesta/web/templates/header.html

sed -Ei "/$(escape '<?php Vesta::do_action("panel_init"); ?>')/d" /usr/local/vesta/web/templates/admin/panel.html
sed -Ei "/$(escape '<?php Vesta::do_action("header_menu"); ?>')/d" /usr/local/vesta/web/templates/admin/panel.html
sed -Ei "/$(escape '<?php Vesta::do_action("header_tray"); ?>')/d" /usr/local/vesta/web/templates/admin/panel.html
sed -Ei "/$(escape '<?php Vesta::do_action("menu"); ?>')/d" /usr/local/vesta/web/templates/admin/panel.html

sed -Ei "/$(escape '<?php Vesta::do_action("panel_init"); ?>')/d" /usr/local/vesta/web/templates/user/panel.html
sed -Ei "/$(escape '<?php Vesta::do_action("header_menu"); ?>')/d" /usr/local/vesta/web/templates/user/panel.html
sed -Ei "/$(escape '<?php Vesta::do_action("header_tray"); ?>')/d" /usr/local/vesta/web/templates/user/panel.html
sed -Ei "/$(escape '<?php Vesta::do_action("menu"); ?>')/d" /usr/local/vesta/web/templates/user/panel.html

sed -Ei "/$(escape '<?php Vesta::do_action("init"); ?>')/d" /usr/local/vesta/web/templates/admin/list_server_info.html
sed -Ei "/$(escape '<?php Vesta::do_action("head"); ?>')/d" /usr/local/vesta/web/templates/admin/list_server_info.html
sed -Ei "/$(escape '<?php Vesta::do_action("body"); ?>')/d" /usr/local/vesta/web/templates/admin/list_server_info.html

sed -Ei "/$(escape '<?php Vesta::do_action("footer"); ?>')/d" /usr/local/vesta/web/templates/footer.html

# Remove from conf
rm -f /usr/local/vesta/conf/plugins.json

# Remove from bin
rm -f /usr/local/vesta/bin/v-add-plugin
rm -f /usr/local/vesta/bin/v-delete-plugin
rm -f /usr/local/vesta/bin/v-disable-plugin
rm -f /usr/local/vesta/bin/v-enable-plugin
rm -f /usr/local/vesta/bin/v-list-plugin
rm -f /usr/local/vesta/bin/v-list-plugins
rm -f /usr/local/vesta/bin/v-rebuild-plugin
rm -f /usr/local/vesta/bin/v-rebuild-plugins
rm -f /usr/local/vesta/bin/v-update-plugin

# Remove from func
rm -f /usr/local/vesta/func/helpers.sh

# Remove from web
rm -rf /usr/local/vesta/web/plugin-manager

echo -e "\nPlugin manager has been uninstalled"
