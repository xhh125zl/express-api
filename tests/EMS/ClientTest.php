<?php

namespace Kode\ExpressApi\Tests\EMS;

use Kode\ExpressApi\EMS\Client;
use Kode\ExpressApi\EMS\Config;
use PHPUnit\Framework\TestCase;

/**
 * EMS客户端测试类
 */
class ClientTest extends TestCase
{
    /**
     * 测试客户端初始化
     */
    public function testClientInitializationWithArray()
    {
        $config = [
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
            'sandbox' => true,
        ];

        $client = new Client($config);

        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * 测试客户端初始化（使用Config对象）
     */
    public function testClientInitializationWithConfigObject()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
            'sandbox' => true,
        ]);

        $client = new Client($config);

        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * 测试无效配置抛出异常
     */
    public function testClientInitializationWithInvalidConfig()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = new Client('invalid_config');
    }

    /**
     * 测试发货通知方法存在
     */
    public function testSendShipmentMethodExists()
    {
        $client = new Client([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $this->assertTrue(method_exists($client, 'sendShipment'));
    }

    /**
     * 测试取件通知方法存在
     */
    public function testPickupNoticeMethodExists()
    {
        $client = new Client([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $this->assertTrue(method_exists($client, 'pickupNotice'));
    }

    /**
     * 测试查询订单方法存在
     */
    public function testQueryOrderMethodExists()
    {
        $client = new Client([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $this->assertTrue(method_exists($client, 'queryOrder'));
    }

    /**
     * 测试取消订单方法存在
     */
    public function testCancelOrderMethodExists()
    {
        $client = new Client([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $this->assertTrue(method_exists($client, 'cancelOrder'));
    }

    /**
     * 测试查询轨迹方法存在
     */
    public function testQueryTrackingMethodExists()
    {
        $client = new Client([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $this->assertTrue(method_exists($client, 'queryTracking'));
    }

    /**
     * 测试拦截件方法存在
     */
    public function testInterceptMethodExists()
    {
        $client = new Client([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $this->assertTrue(method_exists($client, 'intercept'));
    }

    /**
     * 测试改件信息方法存在
     */
    public function testModifyMethodExists()
    {
        $client = new Client([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $this->assertTrue(method_exists($client, 'modify'));
    }

    /**
     * 测试面单打印方法存在
     */
    public function testPrintLabelMethodExists()
    {
        $client = new Client([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $this->assertTrue(method_exists($client, 'printLabel'));
    }
}
