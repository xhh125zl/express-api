<?php

/**
 * 多快递公司处理示例
 */

// 引入Composer自动加载文件
require_once '../vendor/autoload.php';

use Kode\ExpressApi\ExpressApiClient;
use Kode\ExpressApi\EMS\Config as EMSConfig;

try {
    // 配置不同快递公司的客户端
    $couriers = [
        'ems' => [
            'config' => [
                'app_key' => 'your_ems_app_key',
                'app_secret' => 'your_ems_app_secret',
                'sandbox' => true,
            ],
            'name' => '邮政EMS'
        ],
        // 未来可以添加其他快递公司
        // 'sf' => [
        //     'config' => [
        //         'app_key' => 'your_sf_app_key',
        //         'app_secret' => 'your_sf_app_secret',
        //         'sandbox' => true,
        //     ],
        //     'name' => '顺丰快递'
        // ],
    ];

    // 为每个快递公司创建客户端
    $clients = [];
    foreach ($couriers as $courierCode => $courierInfo) {
        echo "初始化 {$courierInfo['name']} 客户端...\n";
        $clients[$courierCode] = ExpressApiClient::create($courierCode, $courierInfo['config']);
    }

    // 发货数据
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

    // 在每个快递公司上执行发货操作
    foreach ($clients as $courierCode => $client) {
        $courierName = $couriers[$courierCode]['name'];
        echo "\n=== 使用 {$courierName} 发货 ===\n";
        
        try {
            $result = $client->sendShipment($shipmentData);
            echo "{$courierName} 发货成功!\n";
            echo "订单ID: " . ($result['order_id'] ?? '未知') . "\n";
            
            // 查询订单
            if (isset($result['order_id'])) {
                echo "查询订单信息...\n";
                $orderInfo = $client->queryOrder($result['order_id']);
                echo "订单状态: " . ($orderInfo['status'] ?? '未知') . "\n";
            }
        } catch (\Kode\ExpressApi\Common\Exception\ExpressApiException $e) {
            echo "{$courierName} 发货失败: " . $e->getMessage() . "\n";
        }
    }

} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . "\n";
}