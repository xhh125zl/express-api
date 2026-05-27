<?php

namespace Kode\ExpressApi\Tests\Zto;

use Kode\ExpressApi\Zto\Client;
use Kode\ExpressApi\Zto\Config;
use Kode\ExpressApi\Common\Exception\ExpressApiException;
use PHPUnit\Framework\TestCase;

/**
 * 中通快递客户端测试类（基于沙箱环境）
 */
class ClientTest extends TestCase
{
    private $client;
    private $config;

    protected function setUp(): void
    {
        // 使用沙箱环境凭证初始化客户端
        $this->config = new Config([
            'app_key' => 'app_key',
            'app_secret' => 'app_secret',
            'sandbox' => true,
            'timeout' => 30,
        ]);

        $this->client = new Client($this->config);
    }

    public function testClientCreation()
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function testGetConfig()
    {
        $this->assertSame($this->config, $this->client->getConfig());
    }

    public function testGetAuthReturnsAuthInstance()
    {
        $auth = $this->client->getAuth();
        $this->assertInstanceOf(\Kode\ExpressApi\Zto\Auth::class, $auth);
    }

    // ========== 方法存在性测试 ==========

    /** @test */
    public function sendShipment_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'sendShipment'));
    }

    /** @test */
    public function batchSendShipment_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'batchSendShipment'));
    }

    /** @test */
    public function pickupNotice_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'pickupNotice'));
    }

    /** @test */
    public function queryOrder_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'queryOrder'));
    }

    /** @test */
    public function batchQueryOrders_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'batchQueryOrders'));
    }

    /** @test */
    public function cancelOrder_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'cancelOrder'));
    }

    /** @test */
    public function queryTracking_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'queryTracking'));
    }

    /** @test */
    public function interceptOrder_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'interceptOrder'));
    }

    /** @test */
    public function updateOrderInfo_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'updateOrderInfo'));
    }

    /** @test */
    public function intercept_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'intercept'));
    }

    /** @test */
    public function modify_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'modify'));
    }

    /** @test */
    public function printLabel_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'printLabel'));
    }

    // ========== 参数校验测试 ==========

    /**
     * 测试下单缺少必填字段时抛出异常
     */
    public function testSendShipmentThrowsExceptionWhenMissingRequiredFields()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('下单数据');

        $this->client->sendShipment([]); // 缺少必填字段
    }

    /**
     * 测试取消订单缺少订单号时抛出异常
     */
    public function testCancelOrderThrowsExceptionWhenOrderIdEmpty()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('不能为空');

        $this->client->cancelOrder('');
    }

    /**
     * 测试查询轨迹运单号为空时抛出异常
     */
    public function testQueryTrackingThrowsExceptionWhenBillCodeEmpty()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('运单号');

        $this->client->queryTracking('');
    }

    /**
     * 测试拦截缺少原因时抛出异常
     */
    public function testInterceptThrowsExceptionWhenMissingReason()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('拦截原因');

        $this->client->intercept('order123', []);
    }

    /**
     * 测试改件数据为空时抛出异常
     */
    public function testModifyThrowsExceptionWhenDataEmpty()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('更新数据');

        $this->client->modify('order123', []);
    }

    /**
     * 测试面单打印订单号为空时抛出异常
     */
    public function testPrintLabelThrowsExceptionWhenOrderIdEmpty()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('运单号');

        $this->client->printLabel('');
    }

    // ========== 构造函数参数类型校验测试 ==========

    public function testConstructorThrowsOnInvalidConfigType()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Client('invalid_config_string');
    }

    public function testConstructorAcceptsArray()
    {
        $client = new Client([
            'app_key' => 'k',
            'app_secret' => 's',
            'sandbox' => true,
        ]);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testConstructorAcceptsConfigObject()
    {
        $config = new Config(['app_key' => 'k', 'app_secret' => 's', 'sandbox' => true]);
        $client = new Client($config);
        $this->assertInstanceOf(Client::class, $client);
    }

    // ========== 签名机制集成测试 ==========

    /**
     * 测试签名认证头在请求中正确生成
     *
     * 通过验证 auth 对象的签名方法来间接确认 Client 使用了正确的认证方式
     */
    public function testClientUsesCorrectAuthenticationMechanism()
    {
        $auth = $this->client->getAuth();

        // 验证 auth 能正确生成 x-companyid 头
        $headers = $auth->buildAuthHeaders('{}');
        $this->assertEquals('app_key', $headers['x-companyid']);
        $this->assertNotEmpty($headers['x-datadigest']);
    }
}
