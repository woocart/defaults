[![Coverage Status](https://coveralls.io/repos/github/woocart/defaults/badge.svg?branch=master)](https://coveralls.io/github/woocart/defaults?branch=master)

# Manage and deploy WooCommerce configuration changes

WooCart-Defaults lets you copy database configuration to / from the filesystem.


If you want to develop on this plugin check the [Development docs](docs/development.md).


### Plugins Denylist


The denylist contains a list of plugins in the file [denylist.php](https://github.com/woocart/defaults/blob/master/src/classes/class-denylist.php) and the same list also exists on the `minisites` repository in [denylist.json](https://github.com/niteoweb/minisites/blob/master/src/minisites/sites/woocart_com/static/json/denylist.json). Any changes to the denylist needs to be updated in both the files.

On minisites, all plugins on the list need to have one of the below reasons noted:


|  Shortened                           |  Reason                                                         | 
|-----------------------------|-----------------------------------------------------------| 
| security                    | Unnecessary, WooCart takes care of security.              | 
| speed                       | Unnecessary, WooCart takes care of performance.           | 
| backup/duplicate/migrate | Unnecessary, WooCart takes care of backups and transfers. | 
| legal                       | Unnecessary, WooCart takes care of legal.                 | 
| use case              | Unsupported use case.                                     | 
| issues                      | Causes conflicts or issues.                               | 
| unavailable          | Outdated or unsupported plugin.                           | 
| outdated                    | Outdated or unsupported plugin.                           | 
| vulnerability               | Security vulnerability.                                   | 
