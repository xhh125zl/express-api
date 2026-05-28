<?php

namespace Kode\ExpressApi\Common;

use Kode\ExpressApi\Common\Exception\ExpressApiException;

/**
 * 通用HTTP客户端
 */
class HttpClient
{
    /**
     * 发送HTTP请求
     *
     * @param string $method HTTP方法
     * @param string $url 请求URL
     * @param array $data 请求数据（将被JSON编码为请求体）
     * @param array $headers 请求头
     * @param int $timeout 超时时间（秒）
     * @param string|null $rawBody 原始请求体字符串（设置后将忽略$data，直接发送此字符串）
     * @return array
     * @throws ExpressApiException
     */
    public static function request(
        string $method,
        string $url,
        array $data = [],
        array $headers = [],
        int $timeout = 30,
        ?string $rawBody = null
    ): array {
        $curl = curl_init();

        // 自动检测CA证书文件（解决SSL证书验证问题）
        $caCertPath = self::detectCaCertPath();
        if ($caCertPath !== null) {
            curl_setopt($curl, CURLOPT_CAINFO, $caCertPath);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => self::formatHeaders($headers),
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
        ]);

        // 设置请求体：优先使用 rawBody，否则使用 data 的 JSON 编码
        if ($rawBody !== null && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $rawBody);
        } elseif (!empty($data) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new ExpressApiException('请求失败: ' . $error);
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            throw new ExpressApiException(
                'HTTP请求失败: ' . ($result['message'] ?? '未知错误'),
                $httpCode,
                null,
                $result
            );
        }

        return $result ?: [];
    }

    /**
     * 格式化请求头
     *
     * @param array $headers 请求头数组
     * @return array
     */
    protected static function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }
        return $formatted;
    }

    /**
     * 自动检测CA证书文件路径
     *
     * 按优先级检测以下位置：
     * 1. PHP配置 curl.cainfo
     * 2. 项目根目录 cacert.pem
     * 3. 常见宝塔面板路径
     * 4. 系统常见路径
     *
     * @return string|null CA证书路径，未找到返回null
     */
    protected static function detectCaCertPath(): ?string
    {
        // 1. PHP 配置中的路径
        $iniPath = ini_get('curl.cainfo');
        if ($iniPath && file_exists($iniPath)) {
            return $iniPath;
        }

        // 2. 常见检测路径（按优先级排序）
        $candidatePaths = [
            // 宝塔面板 PHP 目录
            'D:/BtSoft/php/74/cacert.pem',
            'D:/BtSoft/php/80/cacert.pem',
            'C:/BtSoft/php/74/cacert.pem',
            // 项目目录
            dirname(__DIR__, 3) . '/cacert.pem', // 项目根目录
            // Windows 常见位置
            'C:/cacert.pem',
            'D:/cacert.pem',
        ];

        foreach ($candidatePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
