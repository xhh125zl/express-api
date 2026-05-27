<?php

namespace Kode\ExpressApi\Zto;

use Kode\ExpressApi\Common\AbstractConfig;

/**
 * 中通快递开放平台 配置类
 *
 * 基于中通官方开放平台（open.zto.com）SDK规范实现
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
        'sandbox' => false,
        'timeout' => 30,
    ];

    /**
     * 获取基础URL（根据沙箱环境自动选择）
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->isSandbox() ? $this->getSandboxUrl() : $this->getProductionUrl();
    }

    /**
     * 获取沙箱/测试环境URL
     *
     * @return string
     */
    public function getSandboxUrl(): string
    {
        return 'https://japi-test.zto.com';
    }

    /**
     * 获取生产环境URL
     *
     * @return string
     */
    public function getProductionUrl(): string
    {
        return 'https://api.zto.com';
    }

    /**
     * 获取应用Key（即中通开放平台的 companyId / AppKey）
     *
     * @return string
     */
    public function getAppKey(): string
    {
        return $this->get('app_key', '');
    }

    /**
     * 获取应用密钥（即中通开放平台的 key / AppSecret）
     *
     * @return string
     */
    public function getAppSecret(): string
    {
        return $this->get('app_secret', '');
    }
}
