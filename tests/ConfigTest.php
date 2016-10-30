<?php
namespace Opine\Config;

use PHPUnit_Framework_TestCase;
use Opine\Config\Service as Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    const ROOT = __DIR__.'/../public';

    public function testConfig()
    {
        $config = new Config(self::ROOT);
        $this->assertTrue($config->cacheSet());
        $config = $config->get('db');
        $this->assertTrue(is_array($config));
        $this->assertTrue('phpunit' === $config['name']);
    }

    public function testLayeredConfig()
    {
        putenv('OPINE_ENV=dev');
        $config = new Config(self::ROOT);
        $this->assertTrue($config->cacheSet());
        $config = $config->get('db');
        $this->assertTrue(is_array($config));
        $this->assertTrue('phpunit' === $config['name']);
        $this->assertTrue('yes' === $config['blended']);
    }
}
