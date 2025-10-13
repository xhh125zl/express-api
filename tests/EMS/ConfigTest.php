<?php

namespace Kode\ExpressApi\Tests\EMS;

use Kode\ExpressApi\EMS\Config;
use PHPUnit\Framework\TestCase;

/**
 * EMS配置类测试
 */
class ConfigTest extends TestCase
{
    /**
     * 测试配置初始化
     */
    public function testConfigInitialization()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
            'sandbox' => true,
            'timeout' => 60,
            'version' => 'v2',
        ]);

        $this->assertEquals('test_key', $config->getAppKey());
        $this->assertEquals('test_secret', $config->getAppSecret());
        $this->assertTrue($config->isSandbox());
        $this->assertEquals(60, $config->getTimeout());
        $this->assertEquals('v2', $config->getVersion());
    }

    /**
     * 测试默认配置值
     */
    public function testDefaultConfigValues()
    {
        $config = new Config();

        $this->assertEquals('', $config->getAppKey());
        $this->assertEquals('', $config->getAppSecret());
        $this->assertFalse($config->isSandbox());
        $this->assertEquals(30, $config->getTimeout());
        $this->assertEquals('v1', $config->getVersion());
    }

    /**
     * 测试设置器方法
     */
    public function testSetterMethods()
    {
        $config = new Config();

        $config->setAppKey('new_key')
               ->setAppSecret('new_secret')
               ->setSandbox(true)
               ->setTimeout(45)
               ->setVersion('v3');

        $this->assertEquals('new_key', $config->getAppKey());
        $this->assertEquals('new_secret', $config->getAppSecret());
        $this->assertTrue($config->isSandbox());
        $this->assertEquals(45, $config->getTimeout());
        $this->assertEquals('v3', $config->getVersion());
    }

    /**
     * 测试生产环境基础URL
     */
    public function testProductionBaseUrl()
    {
        $config = new Config([
            'version' => 'v1',
        ]);

        $this->assertEquals('https://api.ems.com.cn/v1', $config->getBaseUrl());
    }

    /**
     * 测试沙箱环境基础URL
     */
    public function testSandboxBaseUrl()
    {
        $config = new Config([
            'sandbox' => true,
            'version' => 'v2',
        ]);

        $this->assertEquals('https://api-sandbox.ems.com.cn/v2', $config->getBaseUrl());
    }
}
