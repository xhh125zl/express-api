<?php

namespace Kode\ExpressApi\Sto;

use Kode\ExpressApi\Common\AuthInterface;
use Kode\ExpressApi\Common\Exception\ExpressApiException;
use Kode\ExpressApi\Common\HttpClient;

/**
 * 申通快递API 认证类
 */
class Auth implements AuthInterface
{
    /**
     * @var Config 配置信息
     */
    protected $config;

    /**
     * @var string 访问令牌
     */
    protected $accessToken;

    /**
     * @var int 令牌过期时间戳
     */
    protected $expiresAt;

    /**
     * 构造函数
     *
     * @param Config $config 配置信息
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->accessToken = $config->getAccessToken();
    }

    /**
     * 获取配置信息
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 获取访问令牌
     *
     * @return string
     * @throws ExpressApiException
     */
    public function getAccessToken(): string
    {
        // 如果令牌不存在或已过期，重新获取
        if (!$this->accessToken || $this->isExpired()) {
            $this->refreshToken();
        }

        return $this->accessToken;
    }

    /**
     * 刷新访问令牌
     *
     * @return string 新的访问令牌
     * @throws ExpressApiException
     */
    public function refreshToken(): string
    {
        try {
            $url = $this->config->getBaseUrl() . '/auth/token';
            
            // 准备请求数据
            $data = [
                'app_key' => $this->config->getAppKey(),
                'app_secret' => $this->config->getAppSecret()
            ];

            // 发送请求
            $response = HttpClient::request(
                'POST',
                $url,
                $data,
                ['Content-Type' => 'application/json'],
                $this->config->getTimeout()
            );

            // 检查响应
            if (!isset($response['data']['access_token'])) {
                throw new ExpressApiException('获取申通访问令牌失败: 无效的响应');
            }

            // 存储令牌和过期时间
            $this->accessToken = $response['data']['access_token'];
            $expiresIn = $response['data']['expires_in'] ?? 3600; // 默认1小时过期
            $this->expiresAt = time() + $expiresIn - 300; // 提前5分钟刷新

            // 更新配置中的令牌
            $this->config->setAccessToken($this->accessToken);

            return $this->accessToken;
        } catch (\Exception $e) {
            if ($e instanceof ExpressApiException) {
                throw $e;
            }
            throw new ExpressApiException('获取申通访问令牌失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 检查令牌是否已过期
     *
     * @return bool
     */
    protected function isExpired(): bool
    {
        return $this->expiresAt && time() >= $this->expiresAt;
    }

    /**
     * 清除当前令牌
     *
     * @return void
     */
    public function clearToken(): void
    {
        $this->accessToken = null;
        $this->expiresAt = null;
        $this->config->setAccessToken(null);
    }
}