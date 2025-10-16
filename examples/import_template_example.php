<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kode\ExpressApi\Label\AdvancedLayoutManager;

/**
 * 模板导入示例
 */

try {
    // 初始化布局管理器
    $layoutManager = new AdvancedLayoutManager(__DIR__ . '/templates');
    
    echo "=== 模板导入示例 ===\n\n";
    
    // 1. 导出一个模板作为示例数据
    echo "1. 导出模板数据...\n";
    $exportedData = $layoutManager->exportTemplate('ems_advanced_001');
    if ($exportedData) {
        // 保存到临时文件
        file_put_contents(__DIR__ . '/tmp/import_template.json', $exportedData);
        echo "模板数据已保存到: " . __DIR__ . "/tmp/import_template.json\n\n";
    } else {
        echo "无法导出模板数据!\n\n";
        exit(1);
    }
    
    // 2. 修改导出的数据以创建新的模板
    $templateData = json_decode($exportedData, true);
    $templateData['id'] = 'imported_template_001';
    $templateData['name'] = '导入的模板';
    
    // 添加一个新的字段
    $templateData['fields']['imported_date'] = [
        'label' => '导入日期',
        'x' => 5,
        'y' => 90,
        'width' => 40,
        'height' => 8,
        'font_size' => 8,
        'align' => 'left',
        'font_family' => 'Arial'
    ];
    
    // 3. 导入修改后的模板
    echo "2. 导入修改后的模板...\n";
    $importedTemplate = $layoutManager->importTemplate(json_encode($templateData, JSON_UNESCAPED_UNICODE));
    if ($importedTemplate) {
        echo "模板导入成功! 模板ID: " . $importedTemplate->getId() . "\n";
        echo "模板名称: " . $importedTemplate->getName() . "\n\n";
        
        // 显示导入模板的字段
        echo "导入模板的字段:\n";
        foreach ($importedTemplate->getFields() as $field) {
            echo "- " . $field->getLabel() . " (" . $field->getId() . ")\n";
        }
        echo "\n";
    } else {
        echo "模板导入失败!\n\n";
    }
    
    // 4. 验证导入的模板
    echo "3. 验证导入的模板...\n";
    $validationErrors = $layoutManager->validateTemplate($importedTemplate);
    if (empty($validationErrors)) {
        echo "导入的模板验证通过!\n\n";
    } else {
        echo "导入的模板验证失败:\n";
        foreach ($validationErrors as $error) {
            echo "- $error\n";
        }
        echo "\n";
    }
    
    // 5. 列出所有模板，确认导入的模板存在
    echo "4. 当前所有模板:\n";
    $allTemplates = $layoutManager->listTemplates();
    foreach ($allTemplates as $template) {
        echo "- {$template->getName()} ({$template->getId()}, {$template->getCourier()})\n";
    }
    echo "\n";
    
    echo "模板导入示例执行完成!\n";
    
} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
    echo "错误跟踪: " . $e->getTraceAsString() . "\n";
}