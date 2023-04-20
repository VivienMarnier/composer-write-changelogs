Configuration
===============

<br/>

## Location
You can setup the configuration by adding parameters in `extra` section of your `composer` configuration file.
```json
{
    "extra": {
        "composer-write-changelogs": {
          "write-summary-file": "false",
          "changelogs-dir-path": "my/custom/path",
          "output-file-format": "json",
          "gitlab-hosts": [
            "gitlab.my-company.org"
          ]
        }
    }
}
```

### Locally
The configuration file is the `composer.json` of your project.<br/>
By configuring this file, you configure only composer for the __local__ project

### General
The configuration file is the `.composer` file of your computer.<br/>
This one allows to configure your composer on __all__ your computer

<br/>

## Gitlab hosts
Unlike Github or Bitbucket that have fixed domains, Gitlab instances are
self-hosted so there is no way to automatically detects that a domain
correspond to a Gitlab instance.

The `gitlab-hosts` option can be setup to inform the plugin about the hosts it
should consider as Gitlab instance.

```json
{
    "extra": {
        "composer-write-changelogs": {
            "gitlab-hosts": [
                "gitlab.my-company.org"
            ]
        }
    }
}
```

<br/>

## Folder path
This option should contain the path of the directory where write summary feature will write changelogs files.
The path can be either absolute or relative to the `composer.json` file containing the plugin
configuration.

By default, the changelogs directory will be created from the directory of the `composer.json` file containing the plugin
configuration.

```json
{
    "extra": {
        "composer-write-changelogs": {
          "changelogs-dir-path": "my/custom/path"
        }
    }
}
```

<br/>

## Output file format
By default, the file format for summaries is `.txt`, but you can export it to a `JSON` file instead.<br/>
The only 2 possible variables are `json` and `text`.

```json
{
    "extra": {
        "composer-write-changelogs": {
          "output-file-format": "json"
        }
    }
}
```

<br/>

### Write Summary File
You can disable the plugin by adding `write-summary-file` in the configuration extra.<br/>
```json
{
    "extra": {
        "composer-write-changelogs": {
          "write-summary-file": "false"
        }
    }
}
```



