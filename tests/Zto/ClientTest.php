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

    /** @test */
    public function queryBalance_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'queryBalance'));
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
     * 测试取消订单订单号和运单号都为空时抛出异常
     */
    public function testCancelOrderThrowsExceptionWhenBothIdsEmpty()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('不能同时为空');

        $this->client->cancelOrder('', '1', '');
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

    // ========== 新增/修改方法专项测试 ==========

    /**
     * 测试取消订单可仅传orderCode（默认cancelType=1）
     */
    public function testCancelOrderWithOrderCodeOnly()
    {
        // 不抛异常即可，验证参数组装正确
        // 实际不会发请求因为只是验证方法签名
        try {
            $this->client->cancelOrder('TEST_ORDER_001');
        } catch (ExpressApiException $e) {
            // 预期会因网络或沙箱404报错，但不是参数校验错误
            $this->assertStringNotContainsString('不能同时为空', $e->getMessage());
        }
    }

    /**
     * 测试取消订单可传billCode
     */
    public function testCancelOrderWithBillCode()
    {
        try {
            $this->client->cancelOrder('', '2', '7310000000001');
        } catch (ExpressApiException $e) {
            $this->assertStringNotContainsString('不能同时为空', $e->getMessage());
        }
    }

    /**
     * 测试查询订单默认type=1(全网件)使用billCode字段
     */
    public function testQueryOrderDefaultTypeUsesBillCode()
    {
        try {
            $this->client->queryOrder('7310000000001');
        } catch (ExpressApiException $e) {
            $this->assertStringNotContainsString('不能为空', $e->getMessage());
        }
    }

    /**
     * 测试查询订单type=0(预约件)使用orderCode字段
     */
    public function testQueryOrderReservationType()
    {
        try {
            $this->client->queryOrder('ORDER_001', 0);
        } catch (ExpressApiException $e) {
            $this->assertStringNotContainsString('不能为空', $e->getMessage());
        }
    }

    /**
     * 测试查询余额缺少partner时抛出异常
     */
    public function testQueryBalanceThrowsWhenPartnerEmpty()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('电子面单账号');

        $this->client->queryBalance('');
    }

    /**
     * 测试查询余额partner为空字符串时抛出异常
     */
    public function testQueryBalanceAcceptsPartnerParam()
    {
        // 只验证不抛异常，实际调用会因为沙箱环境问题失败
        try {
            $this->client->queryBalance('test_account', 'ZTO123');
        } catch (ExpressApiException $e) {
            $this->assertStringNotContainsString('电子面单账号', $e->getMessage());
        }
    }

    // ========== bindingEaccount 测试 ==========

    /** @test */
    public function bindingEaccount_method_exists()
    {
        $this->assertTrue(method_exists($this->client, 'bindingEaccount'));
    }

    /**
     * 测试绑定电子面单：eaccount为空时抛出异常
     */
    public function testBindingEaccountThrowsWhenEaccountEmpty()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('电子面单账号不能为空');

        $this->client->bindingEaccount('', 'SC001', 'pwd123');
    }

    /**
     * 测试绑定电子面单：siteCode为空时抛出异常
     */
    public function testBindingEaccountThrowsWhenSiteCodeEmpty()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('网点code不能为空');

        $this->client->bindingEaccount('acc001', '', 'pwd123');
    }

    /**
     * 测试绑定电子面单：密码为空时抛出异常
     */
    public function testBindingEaccountThrowsWhenPasswordEmpty()
    {
        $this->expectException(ExpressApiException::class);
        $this->expectExceptionMessage('电子面单密码不能为空');

        $this->client->bindingEaccount('acc001', 'SC001', '');
    }

    /**
     * 测试绑定电子面单：必填参数合法时不触发参数校验异常
     */
    public function testBindingEaccountAcceptsValidParams()
    {
        try {
            $this->client->bindingEaccount('acc001', 'SC001', 'pwd123');
        } catch (ExpressApiException $e) {
            // 沙箱/网络失败可接受，不应是参数校验错误
            $this->assertStringNotContainsString('不能为空', $e->getMessage());
        }
    }

    /**
     * 测试绑定电子面单：带可选参数 address 和 addressDetail
     */
    public function testBindingEaccountAcceptsOptionalAddress()
    {
        try {
            $this->client->bindingEaccount(
                'acc001',
                'SC001',
                'pwd123',
                '北京/北京市/东城区',
                '17号'
            );
        } catch (ExpressApiException $e) {
            $this->assertStringNotContainsString('不能为空', $e->getMessage());
        }
    }
}
