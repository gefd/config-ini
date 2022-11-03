# PHP Ini Configuration Loader/Class

Usage;
```php
<?php

    require_once('./src/Config.php');
    require_once('./src/IniLoader.php');

    use ConfigIni\IniLoader;
    use ConfigIni\Config;

    $config = IniLoader::Load('example.ini');

    // "ConfigIni\Config"
    if ($config->get('config.name') !== "ConfigIni\Config") {
        throw new \Exception('Failed.');
    }
    // "https://github.com/gefd/config-ini"
    if ($config->get('config.url') !== "https://github.com/gefd/config-ini") {
        throw new \Exception('Failed.');
    }
    // "example.ini"
    if ($config->get('config.example') !== "example.ini") {
        throw new \Exception('Failed.');
    }
    // true
    if ($config->get('config.boolean') !== true || !is_bool($config->get('config.boolean'))) {
        throw new \Exception('Failed.');
    }
    // 10
    if ($config->get('config.integer') !== 10 || !is_int($config->get('config.integer'))) {
        throw new \Exception('Failed.');
    }
    // 0.3
    if ($config->get('config.float') !== 0.3 || !is_float($config->get('config.float'))) {
        throw new \Exception('Failed.');
    }
    // "first"
    if ($config->get('config.child.first') !== 'first') {
        throw new \Exception('Failed.');
    }
    // "first child"
    if ($config->get('config.child.child') !== 'first child') {
        throw new \Exception('Failed.');
    }
    // Config([ first => 'first', 'child' => 'first child'])
    $child = $config->get('config.child');
    if (!($child instanceof Config)) {
        throw new \Exception('Failed.');
    }
    // "first"
    if ($child->get('first') !== 'first') {
        throw new \Exception('Failed.');
    }
    // "first child"
    if ($child->get('child') !== 'first child') {
        throw new \Exception('Failed.');
    }

    // missing configuration
    if ($child->get('missing') !== null) {
        throw new \Exception('Failed.');
    }
```

The static IniLoader::Load method accepts a file name as the first parameter. This will attempt to also load and merge configuration from
a configuration file named using the current host name as the prefix. For example, on a host named "db01", with the following configuration files;

file: config.ini
```ini
[db]
type=sqlite
name=example
```
file: db01.config.ini
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

