<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kode\ExpressApi\Label\AdvancedLayoutManager;
use Kode\ExpressApi\Label\BaseTemplate;

/**
 * 高级面单布局管理器使用示例
 */

try {
    // 初始化布局管理器
    $layoutManager = new AdvancedLayoutManager(__DIR__ . '/templates');

    echo "=== 高级面单布局管理器示例 ===\n\n";

    // 1. 创建EMS模板
    echo "1. 创建EMS模板...\n";
    $emsConfig = [
        'id' => 'ems_advanced_001',
        'name' => 'EMS高级模板',
        'courier' => 'ems',
        'size' => 'standard_100x150',
        'unit' => 'mm',
        'paper_type' => 'thermal'
    ];

    $emsTemplate = $layoutManager->createTemplate($emsConfig);
    
    // 修改一些字段属性
    $trackingField = $emsTemplate->getField('tracking_no');
    if ($trackingField) {
        $trackingField->setFontSize(14);
        $trackingField->setWidth(70);
    }
    
    // 添加自定义字段
    $emsTemplate->addField('custom_note', [
        'label' => '备注',
        'x' => 5,
        'y' => 65,
        'width' => 90,
        'height' => 10,
        'font_size' => 8,
        'align' => 'left',
        'font_family' => 'Arial'
    ]);
    
    // 保存模板
    if ($layoutManager->saveTemplate($emsTemplate)) {
        echo "EMS模板创建并保存成功!\n\n";
    } else {
        echo "EMS模板保存失败!\n\n";
    }

    // 2. 创建顺丰模板
    echo "2. 创建顺丰模板...\n";
    $sfConfig = [
        'id' => 'sf_advanced_001',
        'name' => '顺丰高级模板',
        'courier' => 'sf',
        'size' => ['width' => 100, 'height' => 100], // 自定义尺寸
        'unit' => 'mm',
        'paper_type' => 'normal'
    ];

    $sfTemplate = $layoutManager->createTemplate($sfConfig);
    
    // 添加自定义字段
    $sfTemplate->addField('service_type', [
        'label' => '服务类型',
        'x' => 5,
        'y' => 65,
        'width' => 40,
        'height' => 8,
        'font_size' => 8,
        'align' => 'left',
        'font_family' => 'Arial'
    ]);
    
    // 保存模板
    if ($layoutManager->saveTemplate($sfTemplate)) {
        echo "顺丰模板创建并保存成功!\n\n";
    } else {
        echo "顺丰模板保存失败!\n\n";
    }

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

    // 5. 获取并修改模板
    echo "5. 获取并修改EMS模板...\n";
    $loadedTemplate = $layoutManager->getTemplate('ems_advanced_001');
    if ($loadedTemplate) {
        // 修改字段
        $recipientField = $loadedTemplate->getField('recipient');
        if ($recipientField) {
            $recipientField->setHeight(15); // 增加收件人字段高度
        }
        
        // 添加新字段
        $loadedTemplate->addField('insurance_value', [
            'label' => '保价金额',
            'x' => 5,
            'y' => 80,
            'width' => 40,
            'height' => 8,
            'font_size' => 8,
            'align' => 'left',
            'font_family' => 'Arial'
        ]);
        
        // 保存修改后的模板
        if ($layoutManager->saveTemplate($loadedTemplate)) {
            echo "EMS模板修改并保存成功!\n\n";
        } else {
            echo "EMS模板保存失败!\n\n";
        }
    } else {
        echo "无法加载EMS模板!\n\n";
    }

    // 6. 验证模板
    echo "6. 验证EMS模板...\n";
    $validationErrors = $layoutManager->validateTemplate($emsTemplate);
    if (empty($validationErrors)) {
        echo "模板验证通过!\n\n";
    } else {
        echo "模板验证失败:\n";
        foreach ($validationErrors as $error) {
            echo "- $error\n";
        }
        echo "\n";
    }

    // 7. 复制模板
    echo "7. 复制EMS模板...\n";
    $copiedTemplate = $layoutManager->copyTemplate('ems_advanced_001', 'ems_advanced_001_copy', 'EMS高级模板副本');
    if ($copiedTemplate) {
        echo "模板复制成功! 新模板ID: " . $copiedTemplate->getId() . "\n\n";
    } else {
        echo "模板复制失败!\n\n";
    }

    // 8. 导出模板
    echo "8. 导出EMS模板...\n";
    $exportedData = $layoutManager->exportTemplate('ems_advanced_001');
    if ($exportedData) {
        // 保存导出的数据到文件
        file_put_contents(__DIR__ . '/exports/ems_advanced_001_export.json', $exportedData);
        echo "模板导出成功! 文件保存在: " . __DIR__ . "/exports/ems_advanced_001_export.json\n\n";
    } else {
        echo "模板导出失败!\n\n";
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

    echo "示例执行完成!\n";

} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
    echo "错误跟踪: " . $e->getTraceAsString() . "\n";
}