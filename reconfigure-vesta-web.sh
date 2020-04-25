#!/usr/bin/env bash

# Use this script to re-configure vesta web if any updates remove changes

escape() {
    echo "$1" | sed "s|[\`~!@#$%^&*()_=+{}\|;:\"',<.>/?-]|\\\&|g"
}

file_prepend() {
    local content="$1"
    local file="$2"

    if [[ ! "$(grep -F "$content" "$file")" ]]; then
        content="$content\n$(cat "$file")"
        echo -en "$content" > "$file"
    fi
}

file_append() {
    local content="$1"
    local file="$2"

    if [[ ! "$(grep -F "$content" "$file")" ]]; then
        content="$(cat "$file")\n$content"
        echo -en "$content" > "$file"
    fi
}

# Backup Web directory
backup="/backup/vesta-web_$(date +'%Y-%m-%d_%H-%M-%S').tar.gz"
echo "Backing up /usr/local/vesta/web"
mkdir -p /backup
bash -c "cd /usr/local/vesta && tar -zvcf $backup ./web"
echo -e "Backup saved to $backup\n"

echo "Applying in /usr/local/vesta/web/inc/main.php"
file_append '@include_once($_SERVER["DOCUMENT_ROOT"] . "/plugin-manager/inc/main.php");' /usr/local/vesta/web/inc/main.php

echo "Applying in /usr/local/vesta/web/templates/header.html"
file_prepend '<?php Vesta::do_action("init"); ?>' /usr/local/vesta/web/templates/header.html
[[ ! "$(grep -F '<?php Vesta::do_action("head"); ?>' /usr/local/vesta/web/templates/header.html)" ]] \
    && sed -Ei "s|(</head>)|$(escape '<?php Vesta::do_action("head"); ?>')\n\1|" /usr/local/vesta/web/templates/header.html
[[ ! "$(grep -F '<?php Vesta::do_action("body_class"); ?>' /usr/local/vesta/web/templates/header.html)" ]] \
    && sed -Ee "s|(<body class=\".*)(\">)|\1 $(escape '<?php Vesta::do_action("body_class"); ?>')\2|" /usr/local/vesta/web/templates/header.html

echo "Applying in /usr/local/vesta/web/templates/admin/panel.html"
file_prepend '<?php Vesta::do_action("panel_init"); ?>' /usr/local/vesta/web/templates/admin/panel.html
[[ ! "$(grep -F '<?php Vesta::do_action("header_menu"); ?>' /usr/local/vesta/web/templates/admin/panel.html)" ]] \
    && sed -Ezi "s|(.*)(</div>.*<\!\-\- /.l-menu \-\->.*)|\1$(escape '<?php Vesta::do_action("header_menu"); ?>')\n\2|" /usr/local/vesta/web/templates/admin/panel.html
[[ ! "$(grep -F '<?php Vesta::do_action("header_tray"); ?>' /usr/local/vesta/web/templates/admin/panel.html)" ]] \
    && sed -Ei "s|(<div class=\"l-profile noselect\">)|\1\n$(escape '<?php Vesta::do_action("header_tray"); ?>')|" /usr/local/vesta/web/templates/admin/panel.html
[[ ! "$(grep -F '<?php Vesta::do_action("menu"); ?>' /usr/local/vesta/web/templates/admin/panel.html)" ]] \
    && sed -Ezi "s|(.*)(</div>.*<\!\-\- /.l-stats \-\->.*)|\1$(escape '<?php Vesta::do_action("menu"); ?>')\n\2|" /usr/local/vesta/web/templates/admin/panel.html
file_append '<?php Vesta::do_action("pre_load_template"); ?>' /usr/local/vesta/web/templates/admin/panel.html

echo "Applying in /usr/local/vesta/web/templates/user/panel.html"
file_prepend '<?php Vesta::do_action("panel_init"); ?>' /usr/local/vesta/web/templates/user/panel.html
[[ ! "$(grep -F '<?php Vesta::do_action("header_menu"); ?>' /usr/local/vesta/web/templates/user/panel.html)" ]] \
    && sed -Ezi "s|(.*)(</div>.*<\!\-\- /.l-menu \-\->.*)|\1$(escape '<?php Vesta::do_action("header_menu"); ?>')\n\2|" /usr/local/vesta/web/templates/user/panel.html
[[ ! "$(grep -F '<?php Vesta::do_action("header_tray"); ?>' /usr/local/vesta/web/templates/user/panel.html)" ]] \
    && sed -Ei "s|(<div class=\"l-profile\">)|\1\n$(escape '<?php Vesta::do_action("header_tray"); ?>')|" /usr/local/vesta/web/templates/user/panel.html
[[ ! "$(grep -F '<?php Vesta::do_action("menu"); ?>' /usr/local/vesta/web/templates/user/panel.html)" ]] \
    && sed -Ezi "s|(.*)(</div>.*<\!\-\- /.l-stats \-\->.*)|\1$(escape '<?php Vesta::do_action("menu"); ?>')\n\2|" /usr/local/vesta/web/templates/user/panel.html
file_append '<?php Vesta::do_action("pre_load_template"); ?>' /usr/local/vesta/web/templates/user/panel.html

echo "Applying in /usr/local/vesta/web/templates/admin/list_server_info.html"
file_prepend '<?php Vesta::do_action("init"); ?>' /usr/local/vesta/web/templates/admin/list_server_info.html
[[ ! "$(grep -F '<?php Vesta::do_action("head"); ?>' /usr/local/vesta/web/templates/admin/list_server_info.html)" ]] \
    && sed -Ei "s|(</head>)|$(escape '<?php Vesta::do_action("head"); ?>')\n\1|" /usr/local/vesta/web/templates/admin/list_server_info.html
[[ ! "$(grep -F '<?php Vesta::do_action("body_class"); ?>' /usr/local/vesta/web/templates/admin/list_server_info.html)" ]] \
    && sed -Ee "s|(<body>)|<body class=\"$(escape '<?php Vesta::do_action("body_class"); ?>')\">|" /usr/local/vesta/web/templates/admin/list_server_info.html

echo "Applying in /usr/local/vesta/web/templates/footer.html"
file_prepend '<?php Vesta::do_action("footer"); ?>' /usr/local/vesta/web/templates/footer.html
