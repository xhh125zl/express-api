<?php

namespace Kode\ExpressApi\Tests\EMS;

use Kode\ExpressApi\EMS\Auth;
use Kode\ExpressApi\EMS\Config;
use PHPUnit\Framework\TestCase;

/**
 * EMS认证类测试
 */
class AuthTest extends TestCase
{
    /**
     * 测试认证对象初始化
     */
    public function testAuthInitialization()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $auth = new Auth($config);

        $this->assertInstanceOf(Auth::class, $auth);
    }

    /**
     * 测试令牌清除方法存在
     */
    public function testClearTokenMethodExists()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $auth = new Auth($config);

        $this->assertTrue(method_exists($auth, 'clearToken'));
    }

    /**
     * 测试清除令牌功能
     */
    public function testClearToken()
    {
        $config = new Config([
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $auth = new Auth($config);
        
        // 测试清除令牌功能
        $auth->clearToken();
        // 验证令牌已被清除的逻辑
        $this->assertSame('', $config->getAccessToken());
    }
}
