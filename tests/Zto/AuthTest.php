<?php

namespace Kode\ExpressApi\Tests\Zto;

use Kode\ExpressApi\Zto\Auth;
use Kode\ExpressApi\Zto\Config;
use Kode\ExpressApi\Common\AuthInterface;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private $auth;
    private $config;

    protected function setUp(): void
    {
        // 使用沙箱环境凭证
        $this->config = new Config([
            'app_key' => 'app_key',
            'app_secret' => 'app_secret',
            'sandbox' => true,
        ]);

        $this->auth = new Auth($this->config);
    }

    public function testAuthCreation()
    {
        $this->assertInstanceOf(Auth::class, $this->auth);
        $this->assertInstanceOf(AuthInterface::class, $this->auth);
    }

    public function testGetConfig()
    {
        $this->assertSame($this->config, $this->auth->getConfig());
    }

    /**
     * 测试签名生成算法：Base64(MD5(body + appSecret))
     *
     * 使用固定输入验证签名结果的确定性和正确性
     */
    public function testGenerateDataDigestReturnsBase64Md5()
    {
        // 已知输入
        $body = '{"test":"data"}';
        $appSecret = 'app_secret';

        // 预期结果手动计算: MD5('{"test":"data"}app_secret') 的 Base64 编码
        $expectedStrToSign = $body . $appSecret;
        $expectedDigest = base64_encode(md5($expectedStrToSign, true));

        $actualDigest = $this->auth->generateDataDigest($body);

        $this->assertEquals($expectedDigest, $actualDigest);
    }

    /**
     * 测试签名结果是字符串且不为空
     */
    public function testGenerateDataDigestIsNonEmptyString()
    {
        $digest = $this->auth->generateDataDigest('{"billCode":"123"}');

        $this->assertIsString($digest);
        $this->assertNotEmpty($digest);
    }

    /**
     * 测试不同请求体产生不同的签名
     */
    public function testDifferentBodiesProduceDifferentDigests()
    {
        $digest1 = $this->auth->generateDataDigest('{"billCode":"111"}');
        $digest2 = $this->auth->generateDataDigest('{"billCode":"222"}');

        $this->assertNotEquals($digest1, $digest2);
    }

    /**
     * 测试构建认证头包含必要字段
     */
    public function testBuildAuthHeadersContainsRequiredFields()
    {
        $body = '{"billCode":"test001"}';
        $headers = $this->auth->buildAuthHeaders($body);

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('x-companyid', $headers);
        $this->assertArrayHasKey('x-datadigest', $headers);
    }

    /**
     * 测试 x-companyid 头的值是 AppKey
     */
    public function testCompanyIdHeaderMatchesAppKey()
    {
        $headers = $this->auth->buildAuthHeaders('{}');

        $this->assertEquals('app_key', $headers['x-companyid']);
    }

    /**
     * 测试 Content-Type 头格式
     */
    public function testContentTypeHeaderFormat()
    {
        $headers = $this->auth->buildAuthHeaders('{}');

        $this->assertEquals('application/json; charset=UTF-8', $headers['Content-Type']);
    }

    /**
     * 测试 x-datadigest 头是有效的 Base64 字符串
     */
    public function testDataDigestIsValidBase64()
    {
        $headers = $this->auth->buildAuthHeaders('{"test":1}');

        $decoded = base64_decode($headers['x-datadigest'], true);

        $this->assertFalse($decoded === false, 'x-datadigest 应该是有效的 Base64 字符串');
    }

    /**
     * 兼容接口测试：getAccessToken 返回空字符串（中通无Token机制）
     */
    public function testGetAccessTokenReturnsEmptyString()
    {
        $this->assertSame('', $this->auth->getAccessToken());
    }

    /**
     * 兼容接口测试：clearToken 不抛异常（中通无Token机制）
     */
    public function testClearTokenDoesNotThrowException()
    {
        $this->auth->clearToken();
        $this->assertTrue(true); // 无状态操作，不抛异常即通过
    }
}
