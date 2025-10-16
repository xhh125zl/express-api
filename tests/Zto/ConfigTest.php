<?php

namespace Kode\ExpressApi\Tests\Zto;

use Kode\ExpressApi\Zto\Config;
use Kode\ExpressApi\Common\AbstractConfig;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfigCreation()
    {
        $config = new Config([
            'app_key' => 'test_app_key',
            'app_secret' => 'test_app_secret',
            'sandbox' => true,
        ]);
        
        $this->assertInstanceOf(Config::class, $config);
        $this->assertInstanceOf(AbstractConfig::class, $config);
    }

    public function testGetAppKey()
    {
        $config = new Config([
            'app_key' => 'test_app_key',
            'app_secret' => 'test_app_secret',
            'sandbox' => true,
        ]);
        
        $this->assertEquals('test_app_key', $config->getAppKey());
    }

    public function testGetAppSecret()
    {
        $config = new Config([
            'app_key' => 'test_app_key',
            'app_secret' => 'test_app_secret',
            'sandbox' => true,
        ]);
        
        $this->assertEquals('test_app_secret', $config->getAppSecret());
    }

    public function testIsSandbox()
    {
        $config = new Config([
            'app_key' => 'test_app_key',
            'app_secret' => 'test_app_secret',
            'sandbox' => true,
        ]);
        
        $this->assertTrue($config->isSandbox());
    }
}