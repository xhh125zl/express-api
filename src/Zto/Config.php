<?php

namespace Kode\ExpressApi\Zto;

use Kode\ExpressApi\Common\AbstractConfig;

/**
 * 中通快递API 配置类
 */
class Config extends AbstractConfig
{
    /**
     * 默认配置
     *
     * @var array
     */
    protected $defaults = [
        'app_key' => '',
        'app_secret' => '',
        'access_token' => '',
        'sandbox' => false,
        'timeout' => 30,
        'version' => 'v1',
    ];

    /**
     * 获取API版本
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->get('version', 'v1');
    }

    /**
     * 获取基础URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->isSandbox() ?
            'https://api-zto-sandbox.kdniao.com/' . $this->getVersion() :
            'https://api-zto.kdniao.com/' . $this->getVersion();
    }

    /**
     * 获取沙箱环境URL
     *
     * @return string
     */
    public function getSandboxUrl(): string
    {
        return 'https://api-zto-sandbox.kdniao.com/' . $this->getVersion() . '/';
    }

    /**
     * 获取生产环境URL
     *
     * @return string
     */
    public function getProductionUrl(): string
    {
        return 'https://api-zto.kdniao.com/' . $this->getVersion() . '/';
    }

    /**
     * 获取应用密钥
     *
     * @return string
     */
    public function getAppKey(): string
    {
        return $this->get('app_key', '');
    }

    /**
     * 获取应用密钥
     *
     * @return string
     */
    public function getAppSecret(): string
    {
        return $this->get('app_secret', '');
    }

    /**
     * 获取访问令牌
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->get('access_token', '');
    }

    /**
     * 设置访问令牌
     *
     * @param string|null $accessToken
     * @return $this
     */
    public function setAccessToken(?string $accessToken): self
    {
        $this->set('access_token', $accessToken ?? '');
        return $this;
    }
}