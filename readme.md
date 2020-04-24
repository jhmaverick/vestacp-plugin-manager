# VestaCP Plugins

This script adds to vesta the ability to work with plugins.\
The plugins will be installed by Github repository.

## Installation

#### Dependencies

* jq library
  * Debian: `sudo apt-get -y install jq`
  * CentOS: `sudo yum -y install jq`

```bash
curl -sL https://raw.githubusercontent.com/jhmaverick/vestacp-plugins/master/install.sh | bash -
```

After installation a menu will be added to the vesta panel.


## Plugins structure

Plugins will be installed in /usr/local/vesta/plugins

Structure:
* **/vestacp.json** - Information about the plugin.
* **/bin/** - All files in this directory will be linked to vesta "/usr/local/vesta/bin".
* **/web/** - A symbolic link will be created to this directory with the plugin name inside "/usr/local/vesta/web/plugin/".
* **/hook/** - Hooks to the plugin life cycle.

## Hooks

* **/hook/post_install** - Executed after the plugin installation and before create the symlinks.
* **/hook/post_enable** - Executed when plugin status change to enabled.
* **/hook/pre_disable** - Executed when plugin status change to disabled.
* **/hook/pre_uninstall** - Executed after delete the symlinks and before delete plugin files.

## vestacp.json

```json5
{
  "name": "plugin-name",
  "description": "Plugin description",
  "version": "0.0.1",
  "min-vesta": "0.9.8",
  "user-role": "all|admin",
  "repository": "https://github.com/jhmaverick/plugin-name",
  "homepage": "https://github.com/jhmaverick/plugin-name#readme",
  "author": {
    "name": "João Henrique",
    "email": "joao_henriquee@outlook.com",
    "homepage": "https://github.com/jhmaverick/"
  }
}
```

* **name:** The plugin name. Will be used in the plugin directory and the plugin identification. 
* **user-role:** When defined as "admin" the plugin will be shown only for admin.
* **repository:** The plugin repository. Used to download updates in `v-update-plugin`.
* **min-vesta:** Minimum vesta version.

