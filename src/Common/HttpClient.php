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
     * @param array $data 请求数据
     * @param array $headers 请求头
     * @param int $timeout 超时时间（秒）
     * @return array
     * @throws ExpressApiException
     */
    public static function request(
        string $method,
        string $url,
        array $data = [],
        array $headers = [],
        int $timeout = 30
    ): array {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => self::formatHeaders($headers),
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
        ]);

        if (!empty($data) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
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
}
