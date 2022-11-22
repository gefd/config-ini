# PHP Ini Configuration Loader/Class

[![Build Status](https://img.shields.io/github/workflow/status/gefd/config-ini/Tests/main.svg)](https://github.com/gefd/config-ini/actions?query=branch:main)

Usage given the contents of the example.ini file;
```php
<?php

    require_once('./src/Config.php');
    require_once('./src/IniLoader.php');

    use ConfigIni\IniLoader;
    use ConfigIni\Config;

    $config = IniLoader::Load('example.ini');

    // "ConfigIni\Config"
    $config->get('config.name');
    // "https://github.com/gefd/config-ini"
    $config->get('config.url');
    // "example.ini"
    $config->get('config.example');
    // true
    $config->get('config.boolean');
    // 10
    $config->get('config.integer');
    // 0.3
    $config->get('config.float');
    // "first"
    $config->get('config.child.first');
    // "first child"
    $config->get('config.child.child');
    // Config instance containing configuration from the 'config.child' path
    $config->get('config.child');
    // Config instance containing [ 'one' = 'first', 'two' => 'second' ]
    $childConfig = $config->get('config.child.children');
    // 'first'
    $childConfig->get('one');
    // 'second'
    $childConfig->get('two');

    // ['first','second','third']
    $config->get('config.child.second.children');
    // 'first'
    $config->get('config.child.second.children.0');


    // missing configuration values return null
    $config->get('missing');
```

The static IniLoader::Load method accepts a file name as the first parameter. This will attempt to also load and merge configuration from
a configuration file named using the current host name as the prefix. For example, on a host named "db01", with the following configuration files;

Primary configuration file: config.ini
```ini
[db]
type=sqlite
name=example
```
Host specific configuration file: db01.config.ini
```ini
[db]
type=postgresql
```

When the second argument to IniLoader::Load is passed as `true`, this will produce a Config instance with the following;
```php
/*
    [
       'db' => [
           'type' => 'postgresql',
           'name' => 'example'
       ]
    ]
*/
```

An optional additional "local.config.ini" file will also be loaded if it exists in this case. This can be used as an alternative to a host name
prefixed configuration file.

The default behaviour is to only load configuration from the file name given as the first parameter.

Separating configuration into default and host specific files allows the base config.ini file to be committed to source 
control, and a host specific or local configuration that may include application secrets and passwords to be excluded from source control. 
With a .gitignore entry like the following example;

```
/*.config.ini
```

