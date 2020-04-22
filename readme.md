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
* **/bin/** - All files in this directory will be linked to vesta bin.
* **/web/** - A symbolic link will be created to this directory with the plugin name inside "vesta/web/plugins/".
* **/install.sh** - If it exists it will be executed after the standard installation.
* **/uninstall.sh** - If it exists it will be executed before the standard removal.
* **/plugin.json** - Information about the plugin.


## plugin.json

```json5
{
  "name": "plugin-name",
  "description": "Plugin description",
  "version": "0.0.1",
  "user-role": "all|admin",
  "homepage": "https://github.com/jhmaverick/plugin-name#readme",
  "author": {
    "name": "João Henrique",
    "email": "joao_henriquee@outlook.com",
    "homepage": "https://github.com/jhmaverick/"
  }
}
```

* **name:** The "name" parameter can be defined in JSON to be used in the application, but in Vesta the argument will be overridden by the name of the repository.\
If it is necessary to define the argument, it is recommended to use the same name as the repository.
* **user-role:** When defined as "admin" the plugin will be shown only for admin.

