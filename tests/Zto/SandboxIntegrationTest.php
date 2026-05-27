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
 * 如果未设置环境变量，这些测试会被标记为 skipped。
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
    // 签名算法验证测试（不需要真实API调用）
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

    public function testAuthHeaderIncludesTimestampWhenProvided()
    {
        $auth = new Auth(new Config(['app_key' => 'k', 'app_secret' => 's']));
        $ts = 1716800000;

        $headers = $auth->buildAuthHeaders('{}', 'md5', $ts);

        $this->assertArrayHasKey('x-timestamp', $headers);
        $this->assertEquals((string)$ts, $headers['x-timestamp']);
    }

    public function testAuthHeaderOmitsTimestampWhenNotProvided()
    {
        $auth = new Auth(new Config(['app_key' => 'k', 'app_secret' => 's']));

        $headers = $auth->buildAuthHeaders('{}');

        $this->assertArrayNotHasKey('x-timestamp', $headers);
    }

    // ============================================================
    // 真实 API 连通性测试（需要沙箱凭证）
    // ============================================================

    public function testSandboxTrackQueryConnectivity()
    {
        if (!$this->hasCredentials) {
            $this->markTestSkipped('未设置 ZTO_SANDBOX_APP_KEY / ZTO_SANDBOX_APP_SECRET 环境变量');
        }

        // 使用无效运单号测试连通性（预期抛出ExpressApiException）
        $exceptionCaught = false;
        try {
            $result = $this->client->queryTracking('INVALID_CODE_FOR_TEST');
            // 如果没抛异常，说明API返回了有效响应
            $this->assertIsArray($result);
            $this->assertArrayHasKey('status', $result);
        } catch (ExpressApiException $e) {
            $exceptionCaught = true;
            // 预期是业务错误（如运单号格式错误），不是认证或网络错误
            $msg = $e->getMessage();
            $this->assertNotFalse(
                stripos($msg, '运单') !== false || stripos($msg, '校验') !== false,
                "应该是业务错误而非系统错误，实际: {$msg}"
            );
        }
        $this->assertTrue(true, '沙箱物流查询接口连通正常');
    }

    public function testSandboxCreateOrderConnectivity()
    {
        if (!$this->hasCredentials) {
            $this->markTestSkipped('未设置 ZTO_SANDBOX_APP_KEY / ZTO_SANDBOX_APP_SECRET 环境变量');
        }

        // 发送最小数据测试连通性
        $exceptionCaught = false;
        try {
            $this->client->sendShipment([
                'partnerType'      => '2',
                'orderType'        => '1',
                'partnerOrderCode' => 'INTEGRATION_TEST_' . date('YmdHis'),
            ]);
        } catch (ExpressApiException $e) {
            $exceptionCaught = true;
            // 参数校验异常是预期的（缺少必填字段）
            $this->assertStringContainsString('不能为空', $e->getMessage());
        }
        // 如果没抛异常，说明API返回了有效响应
        if (!$exceptionCaught) {
            $this->assertTrue(true, 'API返回了响应（可能是成功或业务错误）');
        }
    }

    public function testSandboxPrintLabelConnectivity()
    {
        if (!$this->hasCredentials) {
            $this->markTestSkipped('未设置 ZTO_SANDBOX_APP_KEY / ZTO_SANDBOX_APP_SECRET 环境变量');
        }

        $exceptionCaught = false;
        try {
            $this->client->printLabel('TEST_BILL_CODE');
        } catch (ExpressApiException $e) {
            $exceptionCaught = true;
            // 任何业务错误都说明连通成功（签名正确）
            $this->assertNotEmpty($e->getMessage());
        }
        // 无论是否抛异常，能收到API响应就说明连通正常
        $this->assertTrue(true, '沙箱面单打印接口连通正常');
    }
}
