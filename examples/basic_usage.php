<?php

/**
 * 通用快递API使用示例
 */

// 引入Composer自动加载文件
require_once '../vendor/autoload.php';

use Kode\ExpressApi\ExpressApiClient;
use Kode\ExpressApi\EMS\Config as EMSConfig;

try {
    // 配置EMS客户端
    $emsConfig = [
        'app_key' => 'your_ems_app_key',
        'app_secret' => 'your_ems_app_secret',
        'sandbox' => true, // 使用沙箱环境
    ];

    // 创建EMS客户端（通用方式）
    $emsClient = ExpressApiClient::create('ems', $emsConfig);

    // 发货通知示例
    $shipmentData = [
        'order_id' => 'ORDER' . time(),
        'sender' => [
            'name' => '张三',
            'phone' => '13800138000',
            'address' => '北京市朝阳区xxx街道'
        ],
        'receiver' => [
            'name' => '李四',
            'phone' => '13900139000',
            'address' => '上海市浦东新区xxx街道'
        ]
    ];

    echo "发送发货通知...\n";
    $result = $emsClient->sendShipment($shipmentData);
    echo "发货成功，订单ID: " . ($result['order_id'] ?? '未知') . "\n";
    print_r($result);

    // 查询订单示例
    if (isset($result['order_id'])) {
        echo "\n查询订单信息...\n";
        $orderInfo = $emsClient->queryOrder($result['order_id']);
        echo "订单查询成功:\n";
        print_r($orderInfo);
    }

} catch (\Kode\ExpressApi\Common\Exception\ExpressApiException $e) {
    echo "API调用失败: " . $e->getMessage() . "\n";
    echo "错误码: " . $e->getCode() . "\n";
    echo "错误详情: ";
    print_r($e->getDetails());
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . "\n";
}