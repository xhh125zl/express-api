<?php

namespace Kode\ExpressApi\Zto;

use Kode\ExpressApi\Common\AuthInterface;

/**
 * 中通快递开放平台 签名认证类
 *
 * 基于中通官方SDK（ZopClient）的签名规范：
 * - 请求头 x-companyid: AppKey
 * - 请求头 x-datadigest: 签名值
 * - 请求头 x-timestamp: 时间戳（可选，部分接口要求）
 * - 签名算法：MD5(body+AppSecret) 或 SHA256(body+AppSecret)，结果可选Base64编码
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
     * 签名算法：
     *   - MD5 模式（默认）：Base64( MD5( 请求body + AppSecret ) )
     *   - SHA256 模式：Base64( HMAC-SHA256( 请求body + AppSecret, AppSecret ) )
     *
     * @param string $body JSON格式的请求体字符串
     * @param string $algorithm 签名算法：md5 或 sha256
     * @param bool $base64Encode 是否对签名结果进行Base64编码（默认true）
     * @return string 签名值
     */
    public function generateDataDigest(string $body, string $algorithm = 'md5', bool $base64Encode = true): string
    {
        $secret = $this->config->getAppSecret();
        $strToSign = $body . $secret;

        if (strtolower($algorithm) === 'sha256') {
            // HMAC-SHA256 签名
            $signature = hash_hmac('sha256', $strToSign, $secret, true);
        } else {
            // MD5 签名（默认）
            $signature = md5($strToSign, true);
        }

        return $base64Encode ? base64_encode($signature) : bin2hex($signature);
    }

    /**
     * 构建认证请求头
     *
     * 返回中通开放平台所需的认证头：
     * - Content-Type: application/json; charset=UTF-8
     * - x-companyid: AppKey
     * - x-datadigest: 签名值
     * - x-timestamp: 时间戳（可选，当 $timestamp 非 null 时添加）
     *
     * @param string $body JSON格式的请求体字符串
     * @param string $algorithm 签名算法：md5 或 sha256
     * @param int|null $timestamp 时间戳（可选）
     * @return array 认证请求头数组
     */
    public function buildAuthHeaders(string $body, string $algorithm = 'md5', ?int $timestamp = null): array
    {
        $headers = [
            'Content-Type'  => 'application/json; charset=UTF-8',
            'x-companyid'  => $this->config->getAppKey(),
            'x-datadigest' => $this->generateDataDigest($body, $algorithm, true),
        ];

        if ($timestamp !== null) {
            $headers['x-timestamp'] = (string) $timestamp;
        }

        return $headers;
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
