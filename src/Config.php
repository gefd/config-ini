<?php
declare(strict_types=1);
/**
 * @author Geoff Davis <gef.davis@gmail.com>
 */

namespace ConfigIni;

use ArrayAccess;
use Countable;

class Config implements ArrayAccess, Countable
{
    private array $data;

    public function __construct(array $config = [])
    {
        $this->data = $config;
    }

    public function __get(string $key)
    {
        return $this->get($key);
    }

    public function merge(array|Config $config = [])
    {
        $config = \is_array($config) ? $config : $config->getArray();
        $this->data = \array_replace_recursive($this->data, $config);
    }

    public function getArray() : array
    {
        return $this->data;
    }

    public function get(string $key, $default = null)
    {
        $keys = \preg_split('#[/.]#', $key, -1, PREG_SPLIT_NO_EMPTY);
        $data = $this->data;
        while($keys) {
            $key = \array_shift($keys);
            if (!isset($data[$key])) {
                return $default ?? null;
            }
            $data = $data[$key];
        }
        return $this->parseValue($data);
    }

    public function parseValue($value): float|bool|int|string|array|Config
    {
        if ('false' === $value || 'FALSE' === $value) {
            return false;
        } else if ('true' === $value || 'TRUE' === $value) {
            return true;
        } else if (\is_numeric($value)) {
            return \intval($value) == \floatval($value)
                ? \intval($value)
                : \floatval($value);
        } else if (\is_array($value)) {
            if (\array_keys($value) === \array_keys(\array_fill(0, \count($value), 0))) {
                return $value;
            }
            return new self($value);
        }

        return $value;
    }

    public function count() : int
    {
        return \count($this->data);
    }

    public function offsetExists($offset) : bool
    {
        return (isset($this->data[$offset]));
    }

    public function offsetGet($offset) : mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) : void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset) : void
    {
        unset($this->data[$offset]);
    }
}
