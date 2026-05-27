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
            'app_key' => 'test_key_123',
            'app_secret' => 'test_secret',
            'sandbox' => true,
        ]);

        $this->assertEquals('test_key_123', $config->getAppKey());
    }

    public function testGetAppSecret()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret_456',
            'sandbox' => true,
        ]);

        $this->assertEquals('test_secret_456', $config->getAppSecret());
    }

    public function testIsSandbox()
    {
        // 沙箱模式
        $configSandbox = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
            'sandbox' => true,
        ]);
        $this->assertTrue($configSandbox->isSandbox());

        // 生产模式
        $configProd = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
            'sandbox' => false,
        ]);
        $this->assertFalse($configProd->isSandbox());
    }

    public function testGetSandboxUrl()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
            'sandbox' => true,
        ]);

        $this->assertEquals('https://japi-test.zto.com', $config->getSandboxUrl());
    }

    public function testGetProductionUrl()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
            'sandbox' => false,
        ]);

        $this->assertEquals('https://japi.zto.com', $config->getProductionUrl());
    }

    public function testGetBaseUrlReturnsSandboxWhenSandboxEnabled()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
            'sandbox' => true,
        ]);

        $this->assertEquals($config->getSandboxUrl(), $config->getBaseUrl());
    }

    public function testGetBaseUrlReturnsProductionWhenSandboxDisabled()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
            'sandbox' => false,
        ]);

        $this->assertEquals($config->getProductionUrl(), $config->getBaseUrl());
    }

    public function testDefaultTimeout()
    {
        $config = new Config(['app_key' => 'k', 'app_secret' => 's']);
        $this->assertEquals(30, $config->getTimeout());
    }

    public function testCustomTimeout()
    {
        $config = new Config([
            'app_key' => 'k',
            'app_secret' => 's',
            'timeout' => 60,
        ]);
        $this->assertEquals(60, $config->getTimeout());
    }
}
