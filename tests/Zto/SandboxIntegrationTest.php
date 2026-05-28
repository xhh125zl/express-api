<?php

namespace Kode\ExpressApi\Tests\Zto;

use Kode\ExpressApi\Zto\Client;
use Kode\ExpressApi\Zto\Config;
use Kode\ExpressApi\Zto\Auth;
use Kode\ExpressApi\Common\Exception\ExpressApiException;
use PHPUnit\Framework\TestCase;

/**
 * 中通快递 沙箱环境集成测试
 *
 * 测试与中通开放平台沙箱环境的真实网络交互。
 * 需要设置环境变量:
 *   ZTO_SANDBOX_APP_KEY    - 沙箱 AppKey
 *   ZTO_SANDBOX_APP_SECRET - 沙箱 AppSecret
 *
 * 如果未设置环境变量，API连通性测试会被标记为 skipped。
 *
 * 已验证的沙箱运单号: 73100227574514（可用于物流查询和订单查询）
 *
 * @group integration
 * @group zto
 */
class SandboxIntegrationTest extends TestCase
{
    /**
     * @var Client|null
     */
    private $client;

    /**
     * @var bool 是否配置了沙箱凭证
     */
    private $hasCredentials = false;

    protected function setUp(): void
    {
        $appKey    = getenv('ZTO_SANDBOX_APP_KEY') ?: '';
        $appSecret = getenv('ZTO_SANDBOX_APP_SECRET') ?: '';

        $this->hasCredentials = (!empty($appKey) && !empty($appSecret));

        if ($this->hasCredentials) {
            $this->client = new Client([
                'app_key'    => $appKey,
                'app_secret' => $appSecret,
                'sandbox'    => true,
                'timeout'    => 30,
            ]);
        }
    }

    // ============================================================
    // 签名算法单元测试（不需要真实API调用）
    // ============================================================

    public function testSignatureAlgorithmProducesConsistentResult()
    {
        $auth = new Auth(new Config([
            'app_key'    => 'testKey',
            'app_secret' => 'testSecret',
            'sandbox'    => false,
        ]));

        $body = '{"test":"data"}';
        $sign1 = $auth->generateDataDigest($body);
        $sign2 = $auth->generateDataDigest($body);

        // 相同输入必须产生相同签名
        $this->assertEquals($sign1, $sign2);
    }

    public function testSignatureIsBase64Encoded()
    {
        $auth = new Auth(new Config([
            'app_key'    => 'k',
            'app_secret' => 's',
        ]));

        $sign = $auth->generateDataDigest('{}');

        // Base64 编码的字符串应该可以安全解码
        $decoded = base64_decode($sign, true);
        $this->assertNotFalse($decoded, '签名应该是有效的Base64字符串');
        // MD5 输出16字节，Base64后约24字符
        $this->assertEquals(16, strlen($decoded), 'MD5签名原始长度应为16字节');
    }

    public function testSignatureChangesWithDifferentBody()
    {
        $auth = new Auth(new Config(['app_key' => 'k', 'app_secret' => 's']));

        $signA = $auth->generateDataDigest('{"a":1}');
        $signB = $auth->generateDataDigest('{"b":2}');

        $this->assertNotEquals($signA, $signB, '不同请求体应产生不同签名');
    }

    public function testSignatureChangesWithDifferentSecret()
    {
        $auth1 = new Auth(new Config(['app_key' => 'k', 'app_secret' => 'secret1']));
        $auth2 = new Auth(new Config(['app_key' => 'k', 'app_secret' => 'secret2']));

        $s1 = $auth1->generateDataDigest('sameBody');
        $s2 = $auth2->generateDataDigest('sameBody');

        $this->assertNotEquals($s1, $s2, '不同密钥应产生不同签名');
    }

    public function testAuthHeadersContainRequiredFields()
    {
        $config = new Config(['app_key' => 'myKey123', 'app_secret' => 'mySec']);
        $auth = new Auth($config);

        $headers = $auth->buildAuthHeaders('{}');

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('x-companyid', $headers);
        $this->assertArrayHasKey('x-datadigest', $headers);

        $this->assertEquals('application/json; charset=UTF-8', $headers['Content-Type']);
        $this->assertEquals('myKey123', $headers['x-companyid']);
        $this->assertNotEmpty($headers['x-datadigest']);
        $this->assertEquals(24, strlen($headers['x-datadigest']), 'MD5+Base64签名长度应为24');
    }

    // ============================================================
    // 真实 API 连通性测试（需要沙箱凭证）
    // ============================================================

    /**
     * 物流轨迹查询 - 使用已知的有效沙箱运单号
     */
    public function testSandboxTrackQueryWithValidBillCode()
    {
        if (!$this->hasCredentials) {
            $this->markTestSkipped('未设置 ZTO_SANDBOX_APP_KEY / ZTO_SANDBOX_APP_SECRET 环境变量');
        }

        // 使用官方提供的沙箱测试运单号
        $result = $this->client->queryTracking('73100227574514');

        // 返回值必须是数组（轨迹节点列表）
        $this->assertIsArray($result);
        // 应该有至少一条轨迹记录
        $this->assertNotEmpty($result, '应该返回轨迹数据');
        // 第一条记录应包含关键字段
        $firstNode = $result[0];
        $this->assertArrayHasKey('billCode', $firstNode, '轨迹节点应包含运单号');
        $this->assertArrayHasKey('scanType', $firstNode, '轨迹节点应包含扫描类型');
        $this->assertArrayHasKey('scanDate', $firstNode, '轨迹节点应包含扫描时间');
        $this->assertEquals('73100227574514', $firstNode['billCode'], '运单号应匹配');
    }

    /**
     * 创建订单 - 完整数据测试
     */
    public function testSandboxCreateOrderWithFullData()
    {
        if (!$this->hasCredentials) {
            $this->markTestSkipped('未设置 ZTO_SANDBOX_APP_KEY / ZTO_SANDBOX_APP_SECRET 环境变量');
        }

        $result = $this->client->sendShipment([
            'partnerType'      => '2',
            'orderType'        => '1',
            'partnerOrderCode' => 'PHPUNIT_' . date('YmdHis') . '_' . mt_rand(1000, 9999),
            'senderInfo'       => [
                'senderName'     => '测试发件人',
                'senderMobile'   => '13800000001',
                'senderProvince' => '上海',
                'senderCity'     => '上海市',
                'senderDistrict' => '青浦区',
                'senderAddress'  => '测试地址123号',
            ],
            'receiveInfo'      => [
                'receiverName'     => '测试收件人',
                'receiverMobile'   => '13900000002',
                'receiverProvince' => '北京',
                'receiverCity'     => '北京市',
                'receiverDistrict' => '朝阳区',
                'receiverAddress'  => '测试收件地址456号',
            ],
            'accountInfo'      => [
                'accountId'      => 'test',
                'accountPassword' => 'ZTO123',
            ],
        ]);

        // 创建订单成功时应返回结果数据
        $this->assertIsArray($result);
        $this->assertArrayHasKey('billCode', $result, '应返回运单号');
        $this->assertArrayHasKey('orderCode', $result, '应返回订单号');
        $this->assertNotEmpty($result['billCode'], '运单号不应为空');
        $this->assertNotEmpty($result['orderCode'], '订单号不应为空');
        // billCode 应该是14位数字
        $this->assertMatchesRegularExpression('/^\d{14}$/', $result['billCode'], '运单号格式应为14位数字');
    }

    /**
     * 电子面单打印 - 沙箱可能不支持(E405)
     */
    public function testSandboxPrintLabelMayFailInSandbox()
    {
        if (!$this->hasCredentials) {
            $this->markTestSkipped('未设置 ZTO_SANDBOX_APP_KEY / ZTO_SANDBOX_APP_SECRET 环境变量');
        }

        try {
            $result = $this->client->printLabel('73100227574514');
            // 如果沙箱配置了此接口，应该正常返回
            $this->assertIsArray($result);
        } catch (ExpressApiException $e) {
            // 沙箱环境经常返回 E405 "不存在对应的API配置"，这是预期行为
            $msg = $e->getMessage();
            $isExpectedError = (
                stripos($msg, 'E405') !== false ||
                stripos($msg, '不存在对应的API配置') !== false
            );
            $this->assertTrue(
                $isExpectedError,
                "面单打印错误应该是E405或配置问题，实际: {$msg}"
            );
        }
    }

    /**
     * 获取打单余额 - 查询接口连通性
     */
    public function testSandboxQueryBalance()
    {
        if (!$this->hasCredentials) {
            $this->markTestSkipped('未设置 ZTO_SANDBOX_APP_KEY / ZTO_SANDBOX_APP_SECRET 环境变量');
        }

        $result = $this->client->queryBalance('test', 'ZTO123', '1');

        $this->assertIsArray($result);
        // 余额接口应返回余额字段
        $this->assertArrayHasKey('available', $result, '应返回可用数量');
        $this->assertArrayHasKey('recharge', $result, '应返回充值数量');
    }

    /**
     * 查询订单信息 - 使用已知的有效运单号
     */
    public function testSandboxGetOrderInfoWithValidBillCode()
    {
        if (!$this->hasCredentials) {
            $this->markTestSkipped('未设置 ZTO_SANDBOX_APP_KEY / ZTO_SANDBOX_APP_SECRET 环境变量');
        }

        // type=1 全网件，使用运单号查询
        $result = $this->client->queryOrder('73100227574514', 1);

        $this->assertIsArray($result);
        // 应返回订单数组
        $this->assertNotEmpty($result, '应返回订单数据');
        // 第一条订单应包含关键字段
        $firstOrder = $result[0];
        $this->assertArrayHasKey('billCode', $firstOrder, '订单应包含运单号');
        $this->assertArrayHasKey('orderStatus', $firstOrder, '订单应包含状态');
        $this->assertArrayHasKey('sendName', $firstOrder, '订单应包含发件人姓名');
        $this->assertArrayHasKey('receivName', $firstOrder, '订单应包含收件人姓名');
    }

    /**
     * 取消订单 - 接口路径正确性验证
     */
    public function testSandboxCancelOrderApiPathCorrectness()
    {
        if (!$this->hasCredentials) {
            $this->markTestSkipped('未设置 ZTO_SANDBOX_APP_KEY / ZTO_SANDBOX_APP_SECRET 环境变量');
        }

        // 沙箱中可能查不到订单，但能收到API响应就说明路径和签名正确
        try {
            $result = $this->client->cancelOrder('NONEXIST_ORDER_001', '1');
            $this->assertIsArray($result);
        } catch (ExpressApiException $e) {
            $msg = $e->getMessage();
            // 业务错误（如"查询不到该订单"）说明API路径和签名都正确
            $isBusinessError = (
                stripos($msg, '查询不到') !== false ||
                stripos($msg, '不存在') !== false ||
                stripos($msg, '订单') !== false
            );
            $this->assertTrue(
                $isBusinessError || stripos($msg, '取消类型') !== false,
                "应该是业务错误，实际: {$msg}"
            );
        }
    }
}
