#!/bin/bash

if [[ "$(id -u)" != "0" ]]; then
   echo "This script must be run as root"
   exit 1
fi

if [[ ! -d /usr/local/vesta ]]; then
    echo "Vesta is not installed."
    exit 1
fi

if [[ ! "$(command -v jq)" ]]; then
    echo "You must install the \"jq\" library before proceeding." >&2
    echo "sudo apt-get -y install jq" >&2
    echo "sudo yum -y install jq" >&2
    exit 1
fi

curl -L -J "https://github.com/jhmaverick/vestacp-plugins/archive/master.zip" -o "/tmp/vestacp-plugins.zip"

rm -rf "/tmp/vestacp-plugins-master"
unzip "/tmp/vestacp-plugins.zip" -d "/tmp"
rm -rf "/tmp/vestacp-plugins.zip"

chmod +x /tmp/vestacp-plugins-master/bin/*

cp -a /tmp/vestacp-plugins-master/bin/* /usr/local/vesta/bin
cp -a /tmp/vestacp-plugins-master/func/* /usr/local/vesta/func
cp -a /tmp/vestacp-plugins-master/web/* /usr/local/vesta/web

source /usr/local/vesta/func/helpers.sh
vcp_add_top_menu_item "Plugins" "/list/plugin/" "all"

rm -rf "/tmp/vestacp-plugins-master"

echo -e "\nInstallation completed"
