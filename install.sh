#!/bin/bash

if [[ "$(id -u)" != "0" ]]; then
   echo "This script must be run as root"
   exit 1
elif [[ ! -d /usr/local/vesta ]]; then
    echo "Vesta is not installed."
    exit 1
elif [[ -d /usr/local/vesta/web/plugin-manager ]]; then
    echo "Plugin manager is already installed."
    exit 1
fi

if [[ ! "$(command -v jq)" ]]; then
    echo "You must install the \"jq\" library before proceeding." >&2
    echo "sudo apt-get -y install jq" >&2
    echo "sudo yum -y install jq" >&2
    exit 1
fi

if [[ "$VESTACP_PLUGIN_MANAGER_DEV_MODE" != "yes" ]]; then
    backup="/backup/vesta-web_$(date +'%Y-%m-%d_%H-%M-%S').tar.gz"
    echo "Backing up /usr/local/vesta/web"
    mkdir -p /backup
    bash -c "cd /usr/local/vesta && tar -zvcf $backup ./web"
    echo "Backup saved to $backup"

    curl -L -J "https://github.com/jhmaverick/vestacp-plugins/archive/master.zip" -o "/tmp/vestacp-plugins.zip"

    rm -rf "/tmp/vestacp-plugins-master"
    unzip "/tmp/vestacp-plugins.zip" -d "/tmp"
    rm -rf "/tmp/vestacp-plugins.zip"

    chmod +x /tmp/vestacp-plugins-master/bin/*

    cp -a /tmp/vestacp-plugins-master/bin/* /usr/local/vesta/bin
    cp -a /tmp/vestacp-plugins-master/func/* /usr/local/vesta/func
    cp -a /tmp/vestacp-plugins-master/plugin-manager /usr/local/vesta/web

    rm -rf "/tmp/vestacp-plugins-master"
fi

mkdir -p /usr/local/vesta/plugins

file_prepend() {
    local content="$1"
    local file="$2"

    content="$content\n$(cat "$file")"
    echo -en "$content" > "$file"
}

file_append() {
    local content="$1"
    local file="$2"

    content="$(cat "$file")\n$content"
    echo -en "$content" > "$file"
}

escape() {
    echo "$1" | sed "s|[\`~!@#$%^&*()_=+{}\|;:\"',<.>/?-]|\\\&|g"
}

file_append 'include_once($_SERVER["DOCUMENT_ROOT"] . "/plugin-manager/inc/main.php");' /usr/local/vesta/web/inc/main.php

file_prepend '<?php Vesta::do_action("init"); ?>' /usr/local/vesta/web/templates/header.html
sed -Ei "s|(</head>)|$(escape '<?php Vesta::do_action("head"); ?>')\n\1|" /usr/local/vesta/web/templates/header.html
file_append '<?php Vesta::do_action("body"); ?>' /usr/local/vesta/web/templates/header.html

file_prepend '<?php Vesta::do_action("panel_init"); ?>' /usr/local/vesta/web/templates/admin/panel.html
sed -Ezi "s|(.*)(</div>.*<\!\-\- /.l-menu \-\->.*)|\1$(escape '<?php Vesta::do_action("header_menu"); ?>')\n\2|" /usr/local/vesta/web/templates/admin/panel.html
sed -Ei "s|(<div class=\"l-profile noselect\">)|\1\n$(escape '<?php Vesta::do_action("header_tray"); ?>')|" /usr/local/vesta/web/templates/admin/panel.html
sed -Ezi "s|(.*)(</div>.*<\!\-\- /.l-stats \-\->.*)|\1$(escape '<?php Vesta::do_action("menu"); ?>')\n\2|" /usr/local/vesta/web/templates/admin/panel.html

file_prepend '<?php Vesta::do_action("panel_init"); ?>' /usr/local/vesta/web/templates/user/panel.html
sed -Ezi "s|(.*)(</div>.*<\!\-\- /.l-menu \-\->.*)|\1$(escape '<?php Vesta::do_action("header_menu"); ?>')\n\2|" /usr/local/vesta/web/templates/user/panel.html
sed -Ei "s|(<div class=\"l-profile\">)|\1\n$(escape '<?php Vesta::do_action("header_tray"); ?>')|" /usr/local/vesta/web/templates/user/panel.html
sed -Ezi "s|(.*)(</div>.*<\!\-\- /.l-stats \-\->.*)|\1$(escape '<?php Vesta::do_action("menu"); ?>')\n\2|" /usr/local/vesta/web/templates/user/panel.html

file_prepend '<?php Vesta::do_action("init"); ?>' /usr/local/vesta/web/templates/admin/list_server_info.html
sed -Ei "s|(</head>)|$(escape '<?php Vesta::do_action("head"); ?>')\n\1|" /usr/local/vesta/web/templates/admin/list_server_info.html
sed -Ei "s|(<body>)|\1\n$(escape '<?php Vesta::do_action("body"); ?>')|" /usr/local/vesta/web/templates/admin/list_server_info.html

file_prepend '<?php Vesta::do_action("footer"); ?>' /usr/local/vesta/web/templates/footer.html

echo -e "\nInstallation completed"
