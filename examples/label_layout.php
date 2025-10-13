<?php

/**
 * 面单布局功能使用示例
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Kode\ExpressApi\Label\LayoutManager;
use Kode\ExpressApi\Label\Template;
use Kode\ExpressApi\Label\Field;

// 创建布局管理器
$layoutManager = new LayoutManager(__DIR__ . '/../templates');

// 创建EMS标准面单模板
$template = $layoutManager->createTemplate([
    'id' => 'ems_standard_100x150',
    'name' => 'EMS标准面单(100mm×150mm)',
    'courier' => 'EMS',
    'size' => [
        'width' => 100,   // 宽度(mm)
        'height' => 150,  // 高度(mm)
    ],
]);

// 方式1: 使用数组配置添加字段
$template->addField('sender_name', [
    'label' => '发件人姓名',
    'x' => 10,
    'y' => 20,
    'width' => 50,
    'height' => 5,
    'font_size' => 12,
    'align' => 'left',
]);

// 方式2: 使用Field对象添加字段
$receiverNameField = new Field('receiver_name', [
    'label' => '收件人姓名',
    'x' => 10,
    'y' => 40,
    'width' => 50,
    'height' => 5,
    'font_size' => 12,
    'align' => 'left',
]);
$template->addField('receiver_name', $receiverNameField);

// 添加更多字段
$template->addField('order_id', [
    'label' => '订单号',
    'x' => 10,
    'y' => 60,
    'width' => 50,
    'height' => 5,
    'font_size' => 10,
    'align' => 'left',
]);

$template->addField('barcode', [
    'label' => '条形码',
    'x' => 10,
    'y' => 80,
    'width' => 80,
    'height' => 15,
    'font_size' => 10,
    'align' => 'center',
]);

// 保存模板
$layoutManager->saveTemplate($template);
echo "模板已保存\n";

// 读取模板
$loadedTemplate = $layoutManager->getTemplate('ems_standard_100x150');
if ($loadedTemplate) {
    echo "模板加载成功: " . $loadedTemplate->getName() . "\n";
    
    // 显示字段信息
    echo "字段列表:\n";
    foreach ($loadedTemplate->getFields() as $name => $field) {
        echo "- {$name}: {$field->getLabel()} ({$field->getX()}, {$field->getY()})\n";
    }
}

// 使用模板生成面单数据
$orderData = [
    'order_id' => 'ORD20231001001',
    'sender' => [
        'name' => '张三',
        'address' => '北京市朝阳区xxx街道',
    ],
    'receiver' => [
        'name' => '李四',
        'address' => '上海市浦东新区xxx街道',
    ],
];

// 为字段设置实际值
$fields = $loadedTemplate->getFields();
if (isset($fields['sender_name'])) {
    $fields['sender_name']->setValue($orderData['sender']['name']);
}

if (isset($fields['receiver_name'])) {
    $fields['receiver_name']->setValue($orderData['receiver']['name']);
}

if (isset($fields['order_id'])) {
    $fields['order_id']->setValue($orderData['order_id']);
}

echo "\n面单数据:\n";
foreach ($fields as $name => $field) {
    echo "{$field->getLabel()}: {$field->getValue()}\n";
}

echo "\n示例完成!\n";