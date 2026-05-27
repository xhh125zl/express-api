<?php

namespace Kode\ExpressApi\Common;

use Kode\ExpressApi\Common\Exception\ExpressApiException;

/**
 * 通用响应处理器
 */
class ResponseHandler
{
    /**
     * 处理API响应
     *
     * @param array $response 原始响应数据
     * @param string $courier 快递公司标识
     * @return array 标准化后的响应数据
     * @throws ExpressApiException
     */
    public static function handle(array $response, string $courier): array
    {
        // 检查是否有错误
        if (self::hasError($response, $courier)) {
            throw self::createException($response, $courier);
        }

        // 标准化响应数据
        return self::normalize($response, $courier);
    }

    /**
     * 检查响应是否包含错误
     *
     * @param array $response 响应数据
     * @param string $courier 快递公司标识
     * @return bool
     */
    protected static function hasError(array $response, string $courier): bool
    {
        switch (strtolower($courier)) {
            case 'ems':
                return isset($response['success']) && !$response['success'];

            case 'zto':
                // 中通开放平台: status=false 表示失败
                return isset($response['status']) && $response['status'] === false;

            // 其他快递公司的错误检查逻辑可以在这里添加
            default:
                return isset($response['error']) || isset($response['code']) && $response['code'] != 200;
        }
    }

    /**
     * 创建异常对象
     *
     * @param array $response 响应数据
     * @param string $courier 快递公司标识
     * @return ExpressApiException
     */
    protected static function createException(array $response, string $courier): ExpressApiException
    {
        $message = 'API调用失败';
        $code = 0;
        $details = $response;

        switch (strtolower($courier)) {
            case 'ems':
                if (isset($response['error'])) {
                    $message = $response['error']['message'] ?? $message;
                    $code = $response['error']['code'] ?? $code;
                }
                break;
            case 'sf':
                if (isset($response['error'])) {
                    $message = $response['error']['message'] ?? $message;
                    $code = $response['error']['code'] ?? $code;
                } elseif (isset($response['status']) && $response['status'] !== 'success') {
                    $message = $response['message'] ?? $message;
                    $code = $response['code'] ?? $code;
                }
                break;
            case 'zto':
                // 中通开放平台错误格式
                if (isset($response['status']) && $response['status'] === false) {
                    $message = $response['message'] ?? $message;
                    $code = $response['code'] ?? 0;
                }
                break;

            // 其他快递公司的异常处理逻辑可以在这里添加
        }

        return new ExpressApiException($message, $code, null, $details);
    }

    /**
     * 标准化响应数据
     *
     * @param array $response 响应数据
     * @param string $courier 快递公司标识
     * @return array
     */
    protected static function normalize(array $response, string $courier): array
    {
        switch (strtolower($courier)) {
            case 'ems':
                // 移除EMS特有的包装层
                if (isset($response['data'])) {
                    return $response['data'];
                }
                break;
            case 'sf':
                // 处理SF特有的响应格式
                if (isset($response['data']) && isset($response['status']) && $response['status'] === 'success') {
                    return $response['data'];
                }
                break;
            case 'zto':
                // 中通开放平台: 返回result或data字段作为业务数据
                if (isset($response['result'])) {
                    return $response['result'];
                } elseif (isset($response['data'])) {
                    return $response['data'];
                }
                break;
        }

        return $response;
    }
}
