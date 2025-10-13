<?php

namespace Kode\ExpressApi;

use Kode\ExpressApi\Common\ClientInterface;
use Kode\ExpressApi\EMS\Client as EMSClient;
use Kode\ExpressApi\EMS\Config as EMSConfig;

/**
 * 通用快递API客户端
 */
class ExpressApiClient
{
    /**
     * 创建快递客户端实例
     *
     * @param string $courier 快递公司标识 (如: 'ems', 'sf', 'yt', 'zt')
     * @param array|EMSConfig $config 配置信息
     * @return ClientInterface
     * @throws \InvalidArgumentException
     */
    public static function create(string $courier, $config): ClientInterface
    {
        switch (strtolower($courier)) {
            case 'ems':
                // 确保EMS配置是正确类型
                if (is_array($config)) {
                    $config = new EMSConfig($config);
                }

                if (!$config instanceof EMSConfig) {
                    throw new \InvalidArgumentException('EMS客户端需要有效的EMS配置');
                }

                return new EMSClient($config);

            // 后续可以添加其他快递公司的case
            // case 'sf':
            //     return new SFClient($config);
            // case 'yt':
            //     return new YTClient($config);

            default:
                throw new \InvalidArgumentException("不支持的快递公司: {$courier}");
        }
    }
}
