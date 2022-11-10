<?php
declare(strict_types=1);
/**
 * @author Geoff Davis <gef.davis@gmail.com>
 */

namespace ConfigIni;

class IniLoader
{
    public static function Load(string $filename, bool $includeLocal = false) : Config
    {
        if (!\file_exists($filename)) {
            throw new \RuntimeException('Configuration file not found: ' . $filename);
        }

        $fileData = \parse_ini_file($filename, true, \INI_SCANNER_RAW);
        $config = self::FromArray($fileData);
        // try {hostname}.config.ini then local.config.ini
        $local = [\gethostname(), 'local'];
        foreach ($local as $prefix) {
            $localFile = \dirname($filename) . \DIRECTORY_SEPARATOR . $prefix . '.' . \basename($filename);
            if ($includeLocal && \file_exists($localFile)) {
                $config->merge(self::Load($localFile));
                break;
            }
        }

        return $config;
    }

    public static function FromString(string $iniString) : Config
    {
        $iniData = \parse_ini_string($iniString, true, INI_SCANNER_RAW);
        return self::FromArray($iniData);
    }

    public static function FromArray(array $array) : Config
    {
        $data = [];
        foreach ($array as $k => $v) {
            self::parseArrayKeys($k, $v, $data);
        }
        return new Config($data);
    }

    private static function parseArrayKeys(string|int $key, mixed $value, &$data) : void
    {
        if (\is_numeric($key) || !self::hasSeparator($key)) {
            if (!\is_array($value)) {
                if (\is_string($data)) {
                    throw new \ErrorException("Configuration key '$key' overlaps with an existing key");
                }
                $data[$key] = $value;
            } else {
                foreach ($value as $k => $v) {
                    self::parseArrayKeys($k, $v, $data[$key]);
                }
            }
        } else {
            $keyParts = self::splitKey($key);
            $first = \array_shift($keyParts);
            $subKey = \implode('.', $keyParts);
            self::parseArrayKeys($subKey, $value, $data[$first]);
        }
    }

    private static function hasSeparator(string $key) : bool
    {
        return \str_contains($key, '.') || \str_contains($key, '/');
    }

    private static function splitKey(string $key) : array
    {
        return \preg_split('#[/.]#', $key, 2, PREG_SPLIT_NO_EMPTY);
    }
}
