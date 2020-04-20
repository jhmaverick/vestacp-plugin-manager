#!/usr/bin/env bash

curl -L -J "https://github.com/jhmaverick/vestacp-plugins/archive/master.zip" -o "/tmp/vestacp-plugins.zip"

rm -rf "/tmp/vestacp-plugins-master"
unzip "/tmp/vestacp-plugins.zip" -d "/tmp"
rm -rf "/tmp/vestacp-plugins.zip"

chmod +x /tmp/vestacp-plugins-master/bin/*

cp -a /tmp/vestacp-plugins-master/bin/* /usr/local/vesta/bin
cp -a /tmp/vestacp-plugins-master/func/* /usr/local/vesta/func
cp -a /tmp/vestacp-plugins-master/web/* /usr/local/vesta/web

source /usr/local/vesta/func/plugins.sh
add_menu "Plugins" "/list/plugins/" "all"

rm -rf "/tmp/vestacp-plugins-master"
