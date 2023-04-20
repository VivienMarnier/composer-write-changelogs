Getting started
===============

<br/>

## Prerequisites
The plugin requires PHP 7.4+ and **Composer**

<br/>

## Installation
For the installation of the plugin, you have 2 choices.

### Global installation
You can install the plugin on your computer with the following command
```shell
composer global require "spiriit/composer-write-changelogs"
```

### Local installation
You can also install the plugin in a single project with this command
```shell
composer require --dev "spiriit/composer-write-changelogs"
```

<br/>

## Usage
Once the plugin is installed, it will be launched automatically when the dependency is updated by Composer<br/>
You can find all of your updates summary on you changelogs folder with name `changelogs-<date>-<hour>.<txt/json>`

If you no longer want to display summary, you can either:
- Update the [configuration](configuration.md#write-summary-file) of the plugin
- Uninstall the package