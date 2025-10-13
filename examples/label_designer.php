<?php

/**
 * 面单设计器示例
 * 展示如何使用面单系统创建和预览面单模板
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Kode\ExpressApi\Label\Template;
use Kode\ExpressApi\Label\Field;
use Kode\ExpressApi\Label\MultilingualField;
use Kode\ExpressApi\Label\LabelPreview;
use Kode\ExpressApi\Label\LayoutManager;

try {
    // 创建一个新的面单模板配置
    $config = [
        'id' => 'ems_template_001',
        'name' => 'EMS标准面单模板',
        'courier' => 'EMS',
        'size' => ['width' => 100, 'height' => 150],
        'unit' => 'mm'
    ];
    
    $template = new Template($config);
    
    // 添加发件人信息字段
    $senderConfig = [
        'label' => '发件人',
        'x' => 5,
        'y' => 5,
        'width' => 40,
        'height' => 10,
        'font_size' => 8
    ];
    $senderField = new MultilingualField('sender', $senderConfig);
    $senderField->addLabel('zh', '发件人');
    $senderField->addLabel('en', 'Sender');
    $template->addField('sender', $senderField);
    
    // 添加收件人信息字段
    $recipientConfig = [
        'label' => '收件人',
        'x' => 5,
        'y' => 20,
        'width' => 40,
        'height' => 10,
        'font_size' => 8
    ];
    $recipientField = new MultilingualField('recipient', $recipientConfig);
    $recipientField->addLabel('zh', '收件人');
    $recipientField->addLabel('en', 'Recipient');
    $template->addField('recipient', $recipientField);
    
    // 添加订单号字段
    $orderNoConfig = [
        'label' => '订单号',
        'x' => 5,
        'y' => 35,
        'width' => 40,
        'height' => 8,
        'font_size' => 8
    ];
    $orderNoField = new Field('order_no', $orderNoConfig);
    $template->addField('order_no', $orderNoField);
    
    // 添加条形码字段
    $barcodeConfig = [
        'label' => '追踪号码',
        'x' => 5,
        'y' => 45,
        'width' => 60,
        'height' => 15,
        'type' => 'barcode'
    ];
    $barcodeField = new Field('tracking_no', $barcodeConfig);
    $template->addField('tracking_no', $barcodeField);
    
    // 添加二维码字段
    $qrcodeConfig = [
        'label' => '二维码',
        'x' => 70,
        'y' => 45,
        'width' => 25,
        'height' => 25,
        'type' => 'qrcode'
    ];
    $qrcodeField = new Field('qrcode', $qrcodeConfig);
    $template->addField('qrcode', $qrcodeField);
    
    // 确保模板目录存在
    $templatesDir = __DIR__ . '/templates';
    if (!is_dir($templatesDir)) {
        mkdir($templatesDir, 0755, true);
    }
    
    // 使用布局管理器保存模板
    $layoutManager = new LayoutManager($templatesDir);
    $layoutManager->saveTemplate($template);
    
    echo "模板已保存成功！\n";
    echo "模板ID: " . $template->getId() . "\n";
    echo "模板名称: " . $template->getName() . "\n";
    echo "快递公司: " . $template->getCourier() . "\n";
    
    // 创建预览实例
    $preview = new LabelPreview($template);
    
    // 设置字段值
    $preview->setValues([
        'sender' => '张三\n北京市朝阳区xxx街道xx号',
        'recipient' => '李四\n上海市浦东新区xxx路xx号',
        'order_no' => 'ORD20230401001',
        'tracking_no' => 'EE123456789CN'
    ]);
    
    // 生成HTML预览
    $htmlPreview = $preview->generateHtmlPreview();
    
    // 保存预览到文件
    $previewFile = __DIR__ . '/label_preview.html';
    file_put_contents($previewFile, $htmlPreview);
    
    echo "面单预览已生成: $previewFile\n";
    
    // 生成JSON数据用于前端渲染
    $jsonData = $preview->generateJsonData();
    $jsonFile = __DIR__ . '/label_data.json';
    file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo "面单数据已保存: $jsonFile\n";
    
    // 列出所有模板
    $templates = $layoutManager->listTemplates();
    echo "\n现有模板列表:\n";
    foreach ($templates as $template) {
        echo "- " . $template->getId() . " (" . $template->getName() . ")\n";
    }
    
} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
    echo "错误追踪: " . $e->getTraceAsString() . "\n";
}