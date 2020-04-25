#!/bin/bash

action="$1"

if [[ "$(id -u)" != "0" ]]; then
   echo "This script must be run as root"
   exit 1
elif [[ ! -d /usr/local/vesta ]]; then
    echo "Vesta is not installed."
    exit 1
elif [[ -d /usr/local/vesta/plugin-manager && "${action,,}" != "update" ]]; then
    echo "Plugin manager is already installed."
    exit 1
elif [[ ! "$(command -v jq)" ]]; then
    echo -e "You must install the \"jq\" library before proceeding.\n"
    echo "Debian: sudo apt-get -y install jq"
    echo "CentOS: sudo yum -y install jq"
    exit 1
fi

mkdir -p /usr/local/vesta/plugins

rm -rf "/tmp/vestacp-plugin-manager-master"
curl -L -J "https://github.com/jhmaverick/vestacp-plugin-manager/archive/master.zip" -o "/tmp/vestacp-plugin-manager.zip"
unzip "/tmp/vestacp-plugin-manager.zip" -d "/tmp"
rm -rf "/tmp/vestacp-plugin-manager.zip"

# Check Vesta version
vesta_version="$(sed -En "s/^VERSION='(.*)'/\1/p" /usr/local/vesta/conf/vesta.conf)"
min_vesta="$(jq -r '."min-vesta"' /tmp/vestacp-plugin-manager-master/vestacp.json)"
if [[ ! "$min_vesta" || "$min_vesta" == "null" || "$(php -r "echo version_compare(\"$vesta_version\", \"$min_vesta\", '<') ? '1' : '';")" ]]; then
    echo "The Plugin Manager needs VestaCP version $min_vesta or higher."
    exit 1
fi

# Check update
if [[ "${action,,}" == "update" && -d /usr/local/vesta/plugin-manager ]]; then
    current_version="$(jq -r '.version' /usr/local/vesta/plugin-manager/vestacp.json)"
    new_version="$(jq -r '.version' /tmp/vestacp-plugin-manager-master/vestacp.json)"

    if [[ ! "$new_version" || "$new_version" == "null" \
        || ! "$current_version" || "$current_version" == "null" \
    ]]; then
        echo "Update failed."
        exit 1
    fi

    if [[ "$(php -r "echo version_compare(\"$new_version\", \"$current_version\", '<=') ? '1' : '';")" ]]; then
        echo "The Plugin Manager is already updated."
        exit 1
    fi
fi

if [[ -d /usr/local/vesta/plugin-manager ]]; then
    rm -rf /usr/local/vesta/plugin-manager
fi

# Move to vesta directory
mv /tmp/vestacp-plugin-manager-master /usr/local/vesta/plugin-manager

chmod +x /usr/local/vesta/plugin-manager/bin/*
ln -sf /usr/local/vesta/plugin-manager/bin/* /usr/local/vesta/bin
ln -sf /usr/local/vesta/plugin-manager/web /usr/local/vesta/web/plugin-manager

bash /usr/local/vesta/plugin-manager/reconfigure-vesta-web.sh

# Install plugin modern theme
/usr/local/vesta/plugin-manager/bin/v-add-plugin "https://github.com/jhmaverick/vestoid-theme"

echo -e "\nInstallation completed"
