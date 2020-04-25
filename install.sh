#!/bin/bash

if [[ "$(id -u)" != "0" ]]; then
   echo "This script must be run as root"
   exit 1
elif [[ ! -d /usr/local/vesta ]]; then
    echo "Vesta is not installed."
    exit 1
elif [[ -d /usr/local/vesta/plugin-manager ]]; then
    echo "Plugin manager is already installed."
    exit 1
elif [[ ! "$(command -v jq)" ]]; then
    echo -e "You must install the \"jq\" library before proceeding.\n" >&2
    echo "Debian: sudo apt-get -y install jq" >&2
    echo "CentOS: sudo yum -y install jq" >&2
    exit 1
fi

mkdir -p /usr/local/vesta/plugins

rm -rf "/tmp/vestacp-plugin-manager-master"
curl -L -J "https://github.com/jhmaverick/vestacp-plugin-manager/archive/master.zip" -o "/tmp/vestacp-plugin-manager.zip"
unzip "/tmp/vestacp-plugin-manager.zip" -d "/tmp"
rm -rf "/tmp/vestacp-plugin-manager.zip"

# Move to vesta directory
mv /tmp/vestacp-plugin-manager-master /usr/local/vesta/plugin-manager

chmod +x /usr/local/vesta/plugin-manager/bin/*
ln -sf /usr/local/vesta/plugin-manager/bin/* /usr/local/vesta/bin
ln -sf /usr/local/vesta/plugin-manager/web /usr/local/vesta/web/plugin-manager

bash /usr/local/vesta/plugin-manager/reconfigure-vesta-web.sh

# Install plugin modern theme
/usr/local/vesta/plugin-manager/bin/v-add-plugin "https://github.com/jhmaverick/vestoid-theme"

echo -e "\nInstallation completed"
