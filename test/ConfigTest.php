<?php

namespace Test;

require_once(dirname(__DIR__) . '/src/Config.php');

use PHPUnit\Framework\TestCase;
use ConfigIni\Config;

class ConfigTest extends TestCase
{
    private Config $config;

    public function setUp() : void
    {
        $this->config = new Config([
            'one' => 1,
            'two' => '2',
            'three' => [ 'four' => 4 ],
            'five' => 5,
            'six' => ['seven' => [ 'eight' => 8 ]],
            'true' => 'true',
            'false' => false,
        ]);
    }

    public function testSimpleValue()
    {
        $this->assertEquals(5, $this->config->get('five'));
    }
    public function testCorrectSimpleTypes()
    {
        $this->assertIsInt($this->config->get('one'));
        $this->assertIsInt($this->config->get('two'));
        $this->assertIsBool($this->config->get('true'));
        $this->assertIsBool($this->config->get('false'));
    }
    public function testNestedConfigInstance()
    {
        $this->assertInstanceOf(Config::class, $this->config->get('three'));
        $this->assertInstanceOf(Config::class, $this->config->get('six'));
    }
    public function testGetConfigPath()
    {
        $this->assertEquals(4, $this->config->get('three.four'));
        $this->assertEquals(4, $this->config->get('three/four'));
        $this->assertEquals(8, $this->config->get('six.seven.eight'));
        $this->assertEquals(8, $this->config->get('six/seven.eight'));
        $this->assertEquals(8, $this->config->get('six/seven/eight'));
        $this->assertEquals(8, $this->config->get('six.seven/eight'));
    }
}
