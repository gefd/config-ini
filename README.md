# PHP Ini Configuration Loader/Class

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
    // "first"
    $child->get('first') !== 'first');
    // "first child"
    $child->get('child');
    // ['first','second','third']
    $child->get('config.child.second.children');
    // 'first'
    $child->get('config.child.second.children.0');


    // missing configuration values return null
    $child->get('missing');
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

This will produce a Config instance with the following;
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

The IniLoader::Load method also accepts a second boolean parameter that will enable using a "local.[config-file-name].ini" configuration file that will
be merged with the base configuration file.

For example, using the IniLoader like the following;
```php
$config = IniLoader::Load("config.ini", true);
```

Will result in the loader loading and parsing the contents of `config.ini` if it exists, a host prefixed configuration file (eg; `db01.config.ini`)
followed by adding `local.config.ini` if it exists.

Local and hostname configuration files should be excluded from source control. 

