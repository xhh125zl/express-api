<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kode\ExpressApi\Label\AdvancedLayoutManager;
use Kode\ExpressApi\Label\ConcreteTemplate;

/**
 * 综合示例：展示如何在实际项目中使用高级面单布局管理器
 */

try {
    echo "=== 综合示例：高级面单布局管理器 ===\n\n";
    
    // 1. 初始化布局管理器
    echo "1. 初始化布局管理器...\n";
    $layoutManager = new AdvancedLayoutManager(__DIR__ . '/templates');
    echo "布局管理器初始化成功!\n\n";
    
    // 2. 创建不同快递公司的模板
    echo "2. 创建不同快递公司的模板...\n";
    
    // 创建EMS模板
    $emsTemplate = $layoutManager->createTemplate([
        'id' => 'comprehensive_ems_001',
        'name' => '综合EMS模板',
        'courier' => 'ems',
        'size' => 'standard_100x150',
        'unit' => 'mm',
        'paper_type' => 'thermal',
        'fields' => [
            'sender' => [
                'label' => '发件人',
                'x' => 5,
                'y' => 5,
                'width' => 40,
                'height' => 10,
                'font_size' => 8,
                'align' => 'left',
                'font_family' => 'Arial'
            ],
            'recipient' => [
                'label' => '收件人',
                'x' => 5,
                'y' => 20,
                'width' => 40,
                'height' => 10,
                'font_size' => 8,
                'align' => 'left',
                'font_family' => 'Arial'
            ],
            'order_no' => [
                'label' => '订单号',
                'x' => 5,
                'y' => 35,
                'width' => 40,
                'height' => 8,
                'font_size' => 8,
                'align' => 'left',
                'font_family' => 'Arial'
            ],
            'tracking_no' => [
                'label' => '追踪号码',
                'x' => 5,
                'y' => 45,
                'width' => 60,
                'height' => 15,
                'font_size' => 12,
                'align' => 'left',
                'font_family' => 'Arial',
                'type' => 'barcode',
                'barcode_type' => 'code128'
            ]
        ]
    ]);
    
    // 保存EMS模板
    if ($layoutManager->saveTemplate($emsTemplate)) {
        echo "EMS模板创建并保存成功!\n";
    }
    
    // 创建顺丰模板
    $sfTemplate = $layoutManager->createTemplate([
        'id' => 'comprehensive_sf_001',
        'name' => '综合顺丰模板',
        'courier' => 'sf',
        'size' => ['width' => 100, 'height' => 100],
        'unit' => 'mm',
        'paper_type' => 'normal',
        'fields' => [
            'sender' => [
                'label' => '发件人',
                'x' => 5,
                'y' => 5,
                'width' => 40,
                'height' => 10,
                'font_size' => 8,
                'align' => 'left',
                'font_family' => 'Arial'
            ],
            'recipient' => [
                'label' => '收件人',
                'x' => 5,
                'y' => 20,
                'width' => 40,
                'height' => 10,
                'font_size' => 8,
                'align' => 'left',
                'font_family' => 'Arial'
            ],
            'order_no' => [
                'label' => '订单号',
                'x' => 5,
                'y' => 35,
                'width' => 40,
                'height' => 8,
                'font_size' => 8,
                'align' => 'left',
                'font_family' => 'Arial'
            ],
            'waybill_no' => [
                'label' => '运单号',
                'x' => 5,
                'y' => 45,
                'width' => 60,
                'height' => 15,
                'font_size' => 12,
                'align' => 'left',
                'font_family' => 'Arial',
                'type' => 'barcode',
                'barcode_type' => 'code128'
            ]
        ]
    ]);
    
    // 保存顺丰模板
    if ($layoutManager->saveTemplate($sfTemplate)) {
        echo "顺丰模板创建并保存成功!\n";
    }
    
    echo "\n";
    
    // 3. 列出所有模板
    echo "3. 当前所有模板:\n";
    $allTemplates = $layoutManager->listTemplates();
    foreach ($allTemplates as $template) {
        echo "- {$template->getName()} ({$template->getId()}, {$template->getCourier()})\n";
    }
    echo "\n";
    
    // 4. 根据快递公司列出模板
    echo "4. EMS模板:\n";
    $emsTemplates = $layoutManager->listTemplatesByCourier('ems');
    foreach ($emsTemplates as $template) {
        echo "- {$template->getName()} ({$template->getId()})\n";
    }
    echo "\n";
    
    // 5. 复制模板
    echo "5. 复制EMS模板...\n";
    $copiedTemplate = $layoutManager->copyTemplate(
        'comprehensive_ems_001', 
        'comprehensive_ems_001_backup', 
        '综合EMS模板(备份)'
    );
    if ($copiedTemplate) {
        echo "EMS模板复制成功! 新模板: " . $copiedTemplate->getName() . "\n\n";
    }
    
    // 6. 演示多语言字段
    echo "6. 演示多语言字段...\n";
    // 为顺丰模板添加一个多语言字段
    $sfTemplate->addField('product_name', [
        'labels' => [
            'zh' => '产品名称',
            'en' => 'Product Name'
        ],
        'x' => 10,
        'y' => 70,
        'width' => 50,
        'height' => 5,
        'font_size' => 8
    ]);
    
    // 获取多语言字段
    $multilingualField = $sfTemplate->getField('product_name');
    if ($multilingualField instanceof \Kode\ExpressApi\Label\MultilingualField) {
        echo "多语言字段创建成功!\n";
        echo "  中文标签: " . $multilingualField->getLabelByLanguage('zh') . "\n";
        echo "  英文标签: " . $multilingualField->getLabelByLanguage('en') . "\n";
        
        // 切换语言
        $multilingualField->setLanguage('en');
        echo "  当前语言标签: " . $multilingualField->getLabel() . "\n";
    }
    echo "\n";
    
    // 6. 修改模板并保存
    echo "6. 修改EMS模板...\n";
    $loadedTemplate = $layoutManager->getTemplate('comprehensive_ems_001');
    if ($loadedTemplate) {
        // 添加新字段
        $loadedTemplate->addField('insurance_amount', [
            'label' => '保价金额',
            'x' => 5,
            'y' => 65,
            'width' => 40,
            'height' => 8,
            'font_size' => 8,
            'align' => 'left',
            'font_family' => 'Arial'
        ]);
        
        // 保存修改后的模板
        if ($layoutManager->saveTemplate($loadedTemplate)) {
            echo "EMS模板修改并保存成功!\n\n";
        }
    }
    
    // 7. 验证模板
    echo "7. 验证EMS模板...\n";
    $validationErrors = $layoutManager->validateTemplate($loadedTemplate);
    if (empty($validationErrors)) {
        echo "EMS模板验证通过!\n\n";
    } else {
        echo "EMS模板验证失败:\n";
        foreach ($validationErrors as $error) {
            echo "- $error\n";
        }
        echo "\n";
    }
    
    // 8. 导出模板
    echo "8. 导出EMS模板...\n";
    $exportedData = $layoutManager->exportTemplate('comprehensive_ems_001');
    if ($exportedData) {
        // 保存导出的数据到文件
        file_put_contents(__DIR__ . '/exports/comprehensive_ems_export.json', $exportedData);
        echo "EMS模板导出成功! 文件保存在: " . __DIR__ . "/exports/comprehensive_ems_export.json\n\n";
    }
    
    // 9. 显示支持的快递公司和尺寸
    echo "9. 支持的快递公司:\n";
    $couriers = $layoutManager->getSupportedCouriers();
    foreach ($couriers as $courier) {
        echo "- $courier\n";
    }
    echo "\n";
    
    echo "10. 支持的面单尺寸:\n";
    $sizes = $layoutManager->getSupportedSizes();
    foreach ($sizes as $sizeKey => $size) {
        echo "- $sizeKey: {$size['width']} x {$size['height']}\n";
    }
    echo "\n";
    
    // 11. 演示模板使用
    echo "11. 演示模板使用...\n";
    $template = $layoutManager->getTemplate('comprehensive_ems_001');
    if ($template) {
        echo "模板名称: " . $template->getName() . "\n";
        echo "快递公司: " . $template->getCourier() . "\n";
        echo "面单尺寸: " . json_encode($template->getSize()) . "\n";
        echo "字段数量: " . count($template->getFields()) . "\n";
        
        echo "字段列表:\n";
        foreach ($template->getFields() as $field) {
            echo "- " . $field->getLabel() . " (" . $field->getId() . ")\n";
        }
    }
    echo "\n";
    
    echo "综合示例执行完成!\n";
    
} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
    echo "错误跟踪: " . $e->getTraceAsString() . "\n";
}