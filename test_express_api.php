<?php

/**
 * 快递API集成包测试脚本
 * 测试EMS、顺丰、韵达、中通、申通和菜鸟网络的基本功能
 */

require_once __DIR__ . '/vendor/autoload.php';

use Kode\ExpressApi\ExpressApiClient;

// 测试配置
$configs = [
    'ems' => [
        'app_key' => 'test_app_key',
        'app_secret' => 'test_app_secret',
        'sandbox' => true
    ],
    'sf' => [
        'app_key' => 'test_app_key',
        'app_secret' => 'test_app_secret',
        'sandbox' => true
    ],
    'yunda' => [
        'app_key' => 'test_app_key',
        'app_secret' => 'test_app_secret',
        'sandbox' => true
    ],
    'zto' => [
        'app_key' => 'test_app_key',
        'app_secret' => 'test_app_secret',
        'sandbox' => true
    ],
    'sto' => [
        'app_key' => 'test_app_key',
        'app_secret' => 'test_app_secret',
        'sandbox' => true
    ],
    'cainiao' => [
        'app_key' => 'test_app_key',
        'app_secret' => 'test_app_secret',
        'partner_id' => 'test_partner_id',
        'sandbox' => true
    ]
];

// 测试数据
$testShipmentData = [
    'order_id' => 'TEST' . date('YmdHis'),
    'sender' => [
        'name' => '张三',
        'phone' => '13800138000',
        'province' => '广东省',
        'city' => '深圳市',
        'district' => '南山区',
        'address' => '科技园路88号'
    ],
    'receiver' => [
        'name' => '李四',
        'phone' => '13900139000',
        'province' => '北京市',
        'city' => '北京市',
        'district' => '海淀区',
        'address' => '中关村南大街5号'
    ],
    'items' => [
        [
            'name' => '测试产品',
            'quantity' => 1,
            'weight' => 0.5
        ]
    ],
    'total_weight' => 0.5,
    'express_type' => '标准快递'
];

// 初始化并测试
try {
    echo "测试ExpressApiClient功能...\n";
    
    // 获取支持的快递公司列表
    echo "\n获取支持的快递公司列表：\n";
    $supportedCouriers = ExpressApiClient::getSupportedCouriers();
    foreach ($supportedCouriers as $code => $name) {
        echo "- {$code}: {$name}\n";
    }
    
    // 测试各快递公司客户端创建
    echo "\n测试各快递公司客户端创建：\n";
    $couriers = ['ems', 'sf', 'yunda', 'zto', 'sto', 'cainiao'];
    
    foreach ($couriers as $courier) {
        try {
            // 使用静态create方法创建客户端
            $courierClient = ExpressApiClient::create($courier, $configs[$courier]);
            echo "- {$courier} 客户端创建成功\n";
            
            // 检查客户端类型
            $clientType = get_class($courierClient);
            echo "  客户端类型: {$clientType}\n";
            
        } catch (\Exception $e) {
            echo "- {$courier} 客户端创建失败: " . $e->getMessage() . "\n";
        }
    }
    
    // 显示使用示例
    echo "\n使用示例：\n";
    echo "\n// 方式1：创建并使用EMS客户端\n";
    echo "\$emsClient = ExpressApiClient::create('ems', \$emsConfig);\n";
    echo "\$response = \$emsClient->sendShipment(\$shipmentData);\n\n";
    
    echo "// 方式2：创建并使用韵达客户端\n";
    echo "\$yundaClient = ExpressApiClient::create('yunda', \$yundaConfig);\n";
    echo "\$response = \$yundaClient->sendShipment(\$shipmentData);\n\n";
    
    echo "// 方式3：创建并使用菜鸟网络客户端（需要额外的partner_id）\n";
    echo "\$cainiaoConfig = [\n";
    echo "    'app_key' => 'your_app_key',\n";
    echo "    'app_secret' => 'your_app_secret',\n";
    echo "    'partner_id' => 'your_partner_id'\n";
    echo "];\n";
    echo "\$cainiaoClient = ExpressApiClient::create('cainiao', \$cainiaoConfig);\n";
    
    echo "\n// 检查快递公司是否支持\n";
    echo "if (ExpressApiClient::isCourierSupported('jd')) {\n";
    echo "    echo '京东快递支持';\n";
    echo "} else {\n";
    echo "    echo '京东快递暂不支持';\n";
    echo "}\n";
    
    echo "\n测试脚本执行完成！\n";
    
} catch (\Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
}