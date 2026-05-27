<?php

namespace Kode\ExpressApi\Zto;

use Kode\ExpressApi\Common\AuthInterface;

/**
 * 中通快递开放平台 签名认证类
 *
 * 基于中通官方SDK（ZopClient）的签名规范：
 * - 请求头 x-companyid: AppKey
 * - 请求头 x-datadigest: Base64(MD5(请求体 + AppSecret))
 */
class Auth implements AuthInterface
{
    /**
     * @var Config 配置信息
     */
    protected $config;

    /**
     * 构造函数
     *
     * @param Config $config 配置信息
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
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
     * 生成数据摘要签名（x-datadigest）
     *
     * 签名算法：Base64( MD5( 请求body + AppSecret ) )
     *
     * @param string $body JSON格式的请求体字符串
     * @return string Base64编码的MD5签名值
     */
    public function generateDataDigest(string $body): string
    {
        $strToSign = $body . $this->config->getAppSecret();
        return base64_encode(md5($strToSign, true));
    }

    /**
     * 构建认证请求头
     *
     * 返回中通开放平台所需的认证头：
     * - Content-Type: application/json; charset=UTF-8
     * - x-companyid: AppKey
     * - x-datadigest: 签名值
     *
     * @param string $body JSON格式的请求体字符串
     * @return array 认证请求头数组
     */
    public function buildAuthHeaders(string $body): array
    {
        return [
            'Content-Type' => 'application/json; charset=UTF-8',
            'x-companyid' => $this->config->getAppKey(),
            'x-datadigest' => $this->generateDataDigest($body),
        ];
    }

    /**
     * 兼容 AuthInterface：获取访问令牌
     *
     * 中通开放平台使用签名认证，不使用Token。
     * 此方法返回空字符串以保持接口兼容性。
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return '';
    }

    /**
     * 兼容 AuthInterface：清除当前令牌
     *
     * 中通开放平台无Token概念，此方法为空操作。
     *
     * @return void
     */
    public function clearToken(): void
    {
        // 无状态签名，无需清除
    }
}
