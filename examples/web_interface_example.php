<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Kode\ExpressApi\Label\AdvancedLayoutManager;

// 初始化布局管理器
$layoutManager = new AdvancedLayoutManager(__DIR__ . '/templates');

// 获取所有模板
$templates = $layoutManager->listTemplates();

// 获取支持的快递公司
$couriers = $layoutManager->getSupportedCouriers();

// 获取支持的尺寸
$sizes = $layoutManager->getSupportedSizes();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>高级面单布局管理器 - Web界面示例</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .template-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .template-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .template-card h3 {
            margin-top: 0;
            color: #555;
        }
        .courier-list, .size-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .courier-tag, .size-tag {
            background-color: #e0e0e0;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .action-buttons {
            margin-top: 10px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        button.copy {
            background-color: #2196F3;
        }
        button.copy:hover {
            background-color: #0b7dda;
        }
        button.export {
            background-color: #ff9800;
        }
        button.export:hover {
            background-color: #e68a00;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>高级面单布局管理器 - Web界面示例</h1>
        
        <div class="section">
            <h2>支持的快递公司</h2>
            <div class="courier-list">
                <?php foreach ($couriers as $courier): ?>
                    <span class="courier-tag"><?= htmlspecialchars($courier) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="section">
            <h2>支持的面单尺寸</h2>
            <div class="size-list">
                <?php foreach ($sizes as $sizeKey => $size): ?>
                    <span class="size-tag"><?= htmlspecialchars($sizeKey) ?>: <?= $size['width'] ?> x <?= $size['height'] ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="section">
            <h2>所有模板</h2>
            <div class="template-list">
                <?php foreach ($templates as $template): ?>
                    <div class="template-card">
                        <h3><?= htmlspecialchars($template->getName()) ?></h3>
                        <p><strong>ID:</strong> <?= htmlspecialchars($template->getId()) ?></p>
                        <p><strong>快递公司:</strong> <?= htmlspecialchars($template->getCourier()) ?></p>
                        <p><strong>尺寸:</strong> 
                            <?php 
                                $size = $template->getSize();
                                if (is_array($size)) {
                                    echo isset($size['width']) ? $size['width'] . ' x ' . $size['height'] : '自定义';
                                } else {
                                    echo $size;
                                }
                            ?>
                        </p>
                        <p><strong>字段数量:</strong> <?= count($template->getFields()) ?></p>
                        
                        <div class="action-buttons">
                            <button onclick="viewTemplate('<?= $template->getId() ?>')">查看</button>
                            <button class="copy" onclick="copyTemplate('<?= $template->getId() ?>')">复制</button>
                            <button class="export" onclick="exportTemplate('<?= $template->getId() ?>')">导出</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="section">
            <h2>模板字段详情</h2>
            <?php foreach ($templates as $template): ?>
                <div id="fields-<?= $template->getId() ?>" style="display:none;">
                    <h3><?= htmlspecialchars($template->getName()) ?> - 字段列表</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>标签</th>
                                <th>位置(X,Y)</th>
                                <th>尺寸(宽x高)</th>
                                <th>字体大小</th>
                                <th>对齐方式</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($template->getFields() as $field): ?>
                                <tr>
                                    <td><?= htmlspecialchars($field->getId()) ?></td>
                                    <td><?= htmlspecialchars($field->getLabel()) ?></td>
                                    <td><?= $field->getX() ?>, <?= $field->getY() ?></td>
                                    <td><?= $field->getWidth() ?> x <?= $field->getHeight() ?></td>
                                    <td><?= $field->getFontSize() ?></td>
                                    <td><?= htmlspecialchars($field->getAlign()) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function viewTemplate(templateId) {
            // 隐藏所有字段详情
            const allFields = document.querySelectorAll('[id^="fields-"]');
            allFields.forEach(el => el.style.display = 'none');
            
            // 显示选中模板的字段详情
            const fieldsDiv = document.getElementById('fields-' + templateId);
            if (fieldsDiv) {
                fieldsDiv.style.display = 'block';
                
                // 滚动到字段详情区域
                fieldsDiv.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        function copyTemplate(templateId) {
            alert('复制模板功能: ' + templateId + '\n\n在实际应用中，这里会调用API复制模板并跳转到编辑页面。');
        }
        
        function exportTemplate(templateId) {
            alert('导出模板功能: ' + templateId + '\n\n在实际应用中，这里会调用API导出模板为JSON文件。');
        }
    </script>
</body>
</html>