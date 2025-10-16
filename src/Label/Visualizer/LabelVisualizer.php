<?php

namespace Kode\ExpressApi\Label\Visualizer;

use Kode\ExpressApi\Label\Template;
use Kode\ExpressApi\Label\Field;
use Kode\ExpressApi\Label\AdvancedLayoutManager;

/**
 * 面单可视化渲染器
 * 提供面单布局的可视化预览、拖拽、缩放等交互功能支持
 */
class LabelVisualizer
{
    /**
     * @var Template 模板实例
     */
    protected $template;
    
    /**
     * @var AdvancedLayoutManager 布局管理器
     */
    protected $layoutManager;
    
    /**
     * @var float 缩放比例
     */
    protected $scale = 1.0;
    
    /**
     * @var array 面单尺寸配置
     */
    protected $sizeConfigurations = [
        'A4' => ['width' => 210, 'height' => 297],
        'A5' => ['width' => 148, 'height' => 210],
        '10x15' => ['width' => 100, 'height' => 150],
        '8x10' => ['width' => 80, 'height' => 100],
        'ems_default' => ['width' => 100, 'height' => 140],
    ];
    
    /**
     * @var array 支持的条形码类型
     */
    protected $supportedBarcodeTypes = [
        'code128' => 'Code 128',
        'code39' => 'Code 39',
        'ean13' => 'EAN-13',
        'ean8' => 'EAN-8',
        'upca' => 'UPC-A',
        'itf14' => 'ITF-14',
    ];
    
    /**
     * @var array 支持的二维码纠错级别
     */
    protected $supportedQRCodeECLevels = [
        'L' => '低 (7%)',
        'M' => '中 (15%)',
        'Q' => '中高 (25%)',
        'H' => '高 (30%)',
    ];
    
    /**
     * 构造函数
     *
     * @param Template $template 模板实例
     * @param AdvancedLayoutManager $layoutManager 布局管理器
     */
    public function __construct(Template $template, AdvancedLayoutManager $layoutManager)
    {
        $this->template = $template;
        $this->layoutManager = $layoutManager;
    }
    
    /**
     * 获取缩放后的模板尺寸
     *
     * @return array ['width' => float, 'height' => float]
     */
    public function getScaledDimensions(): array
    {
        $size = $this->template->getSize();
        $config = $this->sizeConfigurations[$size] ?? $this->sizeConfigurations['ems_default'];
        
        return [
            'width' => $config['width'] * $this->scale,
            'height' => $config['height'] * $this->scale
        ];
    }
    
    /**
     * 设置缩放比例
     *
     * @param float $scale 缩放比例
     * @return self
     */
    public function setScale(float $scale): self
    {
        if ($scale > 0) {
            $this->scale = $scale;
        }
        return $this;
    }
    
    /**
     * 获取缩放比例
     *
     * @return float
     */
    public function getScale(): float
    {
        return $this->scale;
    }
    
    /**
     * 将坐标从屏幕转换为模板坐标
     *
     * @param float $screenX 屏幕X坐标
     * @param float $screenY 屏幕Y坐标
     * @return array ['x' => float, 'y' => float]
     */
    public function screenToTemplateCoordinates(float $screenX, float $screenY): array
    {
        return [
            'x' => $screenX / $this->scale,
            'y' => $screenY / $this->scale
        ];
    }
    
    /**
     * 将坐标从模板转换为屏幕坐标
     *
     * @param float $templateX 模板X坐标
     * @param float $templateY 模板Y坐标
     * @return array ['x' => float, 'y' => float]
     */
    public function templateToScreenCoordinates(float $templateX, float $templateY): array
    {
        return [
            'x' => $templateX * $this->scale,
            'y' => $templateY * $this->scale
        ];
    }
    
    /**
     * 移动字段到指定位置
     *
     * @param string $fieldId 字段ID
     * @param float $x 新X坐标
     * @param float $y 新Y坐标
     * @return bool 是否成功
     */
    public function moveField(string $fieldId, float $x, float $y): bool
    {
        $field = $this->template->getField($fieldId);
        if (!$field) {
            return false;
        }
        
        // 验证坐标范围
        $size = $this->template->getSize();
        $config = $this->sizeConfigurations[$size] ?? $this->sizeConfigurations['ems_default'];
        
        if ($x < 0 || $y < 0 || 
            $x + $field->getWidth() > $config['width'] || 
            $y + $field->getHeight() > $config['height']) {
            return false;
        }
        
        $field->setX($x);
        $field->setY($y);
        return true;
    }
    
    /**
     * 调整字段大小
     *
     * @param string $fieldId 字段ID
     * @param float $width 新宽度
     * @param float $height 新高度
     * @return bool 是否成功
     */
    public function resizeField(string $fieldId, float $width, float $height): bool
    {
        if ($width <= 0 || $height <= 0) {
            return false;
        }
        
        $field = $this->template->getField($fieldId);
        if (!$field) {
            return false;
        }
        
        // 验证尺寸范围
        $size = $this->template->getSize();
        $config = $this->sizeConfigurations[$size] ?? $this->sizeConfigurations['ems_default'];
        
        if ($field->getX() + $width > $config['width'] || 
            $field->getY() + $height > $config['height']) {
            return false;
        }
        
        $field->setWidth($width);
        $field->setHeight($height);
        return true;
    }
    
    /**
     * 检查点是否在字段内
     *
     * @param string $fieldId 字段ID
     * @param float $x X坐标
     * @param float $y Y坐标
     * @return bool 是否在字段内
     */
    public function isPointInField(string $fieldId, float $x, float $y): bool
    {
        $field = $this->template->getField($fieldId);
        if (!$field) {
            return false;
        }
        
        return $x >= $field->getX() && 
               $x <= $field->getX() + $field->getWidth() && 
               $y >= $field->getY() && 
               $y <= $field->getY() + $field->getHeight();
    }
    
    /**
     * 获取点击位置的字段
     *
     * @param float $x X坐标
     * @param float $y Y坐标
     * @return Field|null 字段对象或null
     */
    public function getFieldAtPosition(float $x, float $y): ?Field
    {
        foreach ($this->template->getFields() as $field) {
            if ($this->isPointInField($field->getId(), $x, $y)) {
                return $field;
            }
        }
        return null;
    }
    
    /**
     * 获取模板的JSON配置（用于前端渲染）
     *
     * @return array JSON配置
     */
    public function getTemplateConfig(): array
    {
        $size = $this->template->getSize();
        $dimensions = $this->sizeConfigurations[$size] ?? $this->sizeConfigurations['ems_default'];
        
        $fields = [];
        foreach ($this->template->getFields() as $field) {
            $fields[] = [
                'id' => $field->getId(),
                'label' => $field->getLabel(),
                'x' => $field->getX(),
                'y' => $field->getY(),
                'width' => $field->getWidth(),
                'height' => $field->getHeight(),
                'type' => $field->getType() ?? 'text',
                'fontSize' => $field->getFontSize() ?? 8,
                'fontFamily' => $field->getFontFamily() ?? 'Arial',
                'fontWeight' => $field->getFontWeight() ?? 'normal',
                'align' => $field->getAlign() ?? 'left',
                'textColor' => $field->getTextColor() ?? '#000000',
                'labelFontSize' => $field->getLabelFontSize() ?? 7,
                'labelColor' => $field->getLabelColor() ?? '#666666',
                'borderWidth' => $field->getBorderWidth() ?? 0,
                'showLabel' => $field->getShowLabel() ?? true,
                'labelPosition' => $field->getLabelPosition() ?? 'top',
                'padding' => $field->getPadding() ?? ['top' => 0, 'left' => 0],
                'barcodeType' => $field->getBarcodeType() ?? null,
                'barcodeModuleWidth' => $field->getBarcodeModuleWidth() ?? 0.25,
                'barcodeHeight' => $field->getBarcodeHeight() ?? 15,
                'qrcodeSize' => $field->getQrcodeSize() ?? 100,
                'qrcodeErrorCorrection' => $field->getQrcodeErrorCorrection() ?? 'M',
            ];
        }
        
        return [
            'id' => $this->template->getId(),
            'name' => $this->template->getName(),
            'courier' => $this->template->getCourier(),
            'size' => $size,
            'dimensions' => $dimensions,
            'fields' => $fields,
            'supportedBarcodeTypes' => $this->supportedBarcodeTypes,
            'supportedQRCodeECLevels' => $this->supportedQRCodeECLevels,
        ];
    }
    
    /**
     * 生成HTML预览
     *
     * @return string HTML代码
     */
    public function generateHtmlPreview(): string
    {
        $config = $this->getTemplateConfig();
        $scaledDimensions = $this->getScaledDimensions();
        
        $html = '<div class="label-preview" style="';
        $html .= 'position: relative; ';
        $html .= 'width: ' . $scaledDimensions['width'] . 'px; ';
        $html .= 'height: ' . $scaledDimensions['height'] . 'px; ';
        $html .= 'border: 1px solid #ccc; ';
        $html .= 'background-color: #fff; ';
        $html .= 'margin: 20px auto; ';
        $html .= '">';
        
        foreach ($config['fields'] as $field) {
            $x = $field['x'] * $this->scale;
            $y = $field['y'] * $this->scale;
            $width = $field['width'] * $this->scale;
            $height = $field['height'] * $this->scale;
            
            // 获取字体粗细
            $fontWeight = isset($field['bold']) ? ($field['bold'] ? 'bold' : 'normal') : $field['fontWeight'] ?? 'normal';
            $fontFamily = isset($field['font_family']) ? $field['font_family'] : $field['fontFamily'] ?? 'Arial';
            $fontSize = isset($field['font_size']) ? ($field['font_size'] * $this->scale) : ($field['fontSize'] ?? 8) * $this->scale;
            $textColor = isset($field['text_color']) ? $field['text_color'] : $field['textColor'] ?? '#000000';
            $align = $field['align'] ?? 'left';
            $borderWidth = isset($field['border_width']) ? ($field['border_width'] * $this->scale) : ($field['borderWidth'] ?? 0) * $this->scale;
            $borderColor = isset($field['border_color']) ? $field['border_color'] : '#000000';
            $backgroundColor = isset($field['background_color']) ? $field['background_color'] : '#ffffff';
            
            $html .= '<div class="field" data-field-id="' . $field['id'] . '" style="';
            $html .= 'position: absolute; ';
            $html .= 'left: ' . $x . 'px; ';
            $html .= 'top: ' . $y . 'px; ';
            $html .= 'width: ' . $width . 'px; ';
            $html .= 'height: ' . $height . 'px; ';
            $html .= 'font-family: ' . $fontFamily . '; ';
            $html .= 'font-size: ' . $fontSize . 'px; ';
            $html .= 'font-weight: ' . $fontWeight . '; ';
            $html .= 'text-align: ' . $align . '; ';
            $html .= 'color: ' . $textColor . '; ';
            $html .= 'border: ' . $borderWidth . 'px solid ' . $borderColor . '; ';
            $html .= 'background-color: ' . $backgroundColor . '; ';
            $html .= 'box-sizing: border-box; ';
            $html .= 'cursor: move; ';
            $html .= '">';
            
            // 显示标签
            if (isset($field['show_label']) && $field['show_label']) {
                $labelFontSize = isset($field['label_font_size']) ? ($field['label_font_size'] * $this->scale) : ($field['labelFontSize'] ?? 7) * $this->scale;
                $labelColor = isset($field['label_color']) ? $field['label_color'] : $field['labelColor'] ?? '#666666';
                $labelFontFamily = isset($field['label_font_family']) ? $field['label_font_family'] : $fontFamily;
                $labelAlign = isset($field['label_align']) ? $field['label_align'] : 'left';
                
                $labelStyle = 'font-size: ' . $labelFontSize . 'px; ';
                $labelStyle .= 'color: ' . $labelColor . '; ';
                $labelStyle .= 'font-family: ' . $labelFontFamily . '; ';
                $labelStyle .= 'text-align: ' . $labelAlign . '; ';
                $labelStyle .= 'display: block; ';
                
                $html .= '<div class="field-label" style="' . $labelStyle . '">';
                $html .= $field['label'];
                $html .= '</div>';
            }
            
            // 根据字段类型显示内容
            if ($field['type'] === 'barcode') {
                $html .= '<div class="barcode-placeholder" style="';
                $html .= 'margin-top: ' . ($field['borderWidth'] ?? 0) * $this->scale . 'px; ';
                $html .= 'text-align: center; ';
                $html .= '">';
                $html .= '[条形码] ' . $field['id'];
                $html .= '</div>';
            } elseif ($field['type'] === 'qrcode') {
                $html .= '<div class="qrcode-placeholder" style="';
                $html .= 'margin: auto; ';
                $html .= 'width: 80%; ';
                $html .= 'height: 80%; ';
                $html .= 'background-color: #f0f0f0; ';
                $html .= 'display: flex; ';
                $html .= 'align-items: center; ';
                $html .= 'justify-content: center; ';
                $html .= 'text-align: center; ';
                $html .= '">';
                $html .= '[二维码]';
                $html .= '</div>';
            } else {
                $paddingTop = isset($field['padding']['top']) ? ($field['padding']['top'] * $this->scale) : 0;
                $paddingLeft = isset($field['padding']['left']) ? ($field['padding']['left'] * $this->scale) : 0;
                
                $html .= '<div class="field-content" style="';
                $html .= 'padding: ' . $paddingTop . 'px ' . $paddingLeft . 'px; ';
                $html .= '">';
                $html .= '{{' . $field['id'] . '}}';
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * 生成完整的HTML页面，包含预览和导出功能
     *
     * @return string 完整的HTML页面
     */
    public function generateFullHtmlPage(): string
    {
        $previewHtml = $this->generateHtmlPreview();
        $templateConfig = json_encode($this->getTemplateConfig(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>面单预览与编辑</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .preview-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        .controls {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 20px;
        }
        .field-controls {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: none;
        }
        .field-controls h3 {
            margin-top: 0;
            color: #333;
        }
        .control-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        .control-item {
            flex: 1;
            min-width: 150px;
            display: flex;
            flex-direction: column;
        }
        .control-item label {
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        .control-item input, .control-item select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 14px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        button.secondary {
            background-color: #6c757d;
        }
        button.secondary:hover {
            background-color: #545b62;
        }
        button.danger {
            background-color: #dc3545;
        }
        button.danger:hover {
            background-color: #c82333;
        }
        .scale-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        select, input {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        #config-output {
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            display: none;
        }
        .code-title {
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        .label-preview {
            position: relative;
            background-color: #fff;
            margin: 20px auto;
            user-select: none;
            border: 1px dashed #ccc;
        }
        .field {
            position: absolute;
            box-sizing: border-box;
            cursor: move;
            transition: box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }
        .field.selected {
            box-shadow: 0 0 0 2px #007bff;
            z-index: 10;
        }
        .resize-handle {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #007bff;
            opacity: 0;
            transition: opacity 0.2s;
            border-radius: 50%;
        }
        .field:hover .resize-handle,
        .field.selected .resize-handle {
            opacity: 1;
        }
        .resize-handle.top-left {
            top: -5px;
            left: -5px;
            cursor: nwse-resize;
        }
        .resize-handle.top-right {
            top: -5px;
            right: -5px;
            cursor: nesw-resize;
        }
        .resize-handle.bottom-left {
            bottom: -5px;
            left: -5px;
            cursor: nesw-resize;
        }
        .resize-handle.bottom-right {
            bottom: -5px;
            right: -5px;
            cursor: nwse-resize;
        }
        .field-label {
            display: block;
            font-size: 7px;
            color: #666;
            padding: 2px;
        }
        .field-content {
            word-break: break-all;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .add-field-panel {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: none;
        }
        .field-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .field-type-selector button {
            flex: 1;
        }
        .size-selector {
            margin-bottom: 15px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <h2>{$this->template->getName()} - 面单设计器</h2>
        <div class="scale-control">
            <label for="scale-select">缩放比例：</label>
            <select id="scale-select" onchange="changeScale(this.value)">
                <option value="0.5">50%</option>
                <option value="0.75">75%</option>
                <option value="1.0" selected>100%</option>
                <option value="1.5">150%</option>
                <option value="2.0">200%</option>
            </select>
            <label for="size-select" style="margin-left: 20px;">面单规格：</label>
            <select id="size-select" onchange="changeSize(this.value)">
                <option value="A4">A4 (210x297mm)</option>
                <option value="A5">A5 (148x210mm)</option>
                <option value="10x15">10x15cm</option>
                <option value="8x10">8x10cm</option>
                <option value="ems_default" selected>EMS标准 (100x140mm)</option>
            </select>
        </div>
        
        <div class="label-preview" id="label-preview">
            $previewHtml
        </div>
    </div>
    
    <div class="controls">
        <button id="toggle-field-panel-btn" onclick="toggleFieldPanel()">添加元素</button>
        <button id="toggle-config-btn" onclick="toggleConfigOutput()">查看配置</button>
        <button id="export-pdf-btn" onclick="exportToPdf()">导出PDF</button>
        <button id="print-btn" onclick="window.print()">打印面单</button>
    </div>
    
    <!-- 元素控制面板 -->
    <div id="field-controls" class="field-controls">
        <h3>元素属性</h3>
        <div class="success-message" id="success-message">修改成功！</div>
        <div id="no-selection-message">请先选择一个元素</div>
        <div id="field-properties" style="display: none;">
            <input type="hidden" id="selected-field-id">
            
            <div class="control-group">
                <div class="control-item">
                    <label for="field-label">标签</label>
                    <input type="text" id="field-label" onchange="updateFieldProperty('label', this.value)">
                </div>
                <div class="control-item">
                    <label for="field-x">X坐标 (mm)</label>
                    <input type="number" id="field-x" step="0.1" onchange="updateFieldPosition('x', this.value)">
                </div>
                <div class="control-item">
                    <label for="field-y">Y坐标 (mm)</label>
                    <input type="number" id="field-y" step="0.1" onchange="updateFieldPosition('y', this.value)">
                </div>
            </div>
            
            <div class="control-group">
                <div class="control-item">
                    <label for="field-width">宽度 (mm)</label>
                    <input type="number" id="field-width" step="0.1" onchange="updateFieldSize('width', this.value)">
                </div>
                <div class="control-item">
                    <label for="field-height">高度 (mm)</label>
                    <input type="number" id="field-height" step="0.1" onchange="updateFieldSize('height', this.value)">
                </div>
                <div class="control-item">
                    <label for="field-border-width">边框粗细 (mm)</label>
                    <input type="number" id="field-border-width" step="0.1" onchange="updateFieldProperty('borderWidth', this.value)">
                </div>
            </div>
            
            <div class="control-group">
                <div class="control-item">
                    <label for="field-font-size">字体大小</label>
                    <input type="number" id="field-font-size" step="0.1" onchange="updateFieldProperty('fontSize', this.value)">
                </div>
                <div class="control-item">
                    <label for="field-font-family">字体</label>
                    <select id="field-font-family" onchange="updateFieldProperty('fontFamily', this.value)">
                        <option value="Arial">Arial</option>
                        <option value="SimSun">宋体</option>
                        <option value="SimHei">黑体</option>
                        <option value="Microsoft YaHei">微软雅黑</option>
                    </select>
                </div>
                <div class="control-item">
                    <label for="field-font-weight">字体粗细</label>
                    <select id="field-font-weight" onchange="updateFieldProperty('fontWeight', this.value)">
                        <option value="normal">正常</option>
                        <option value="bold">粗体</option>
                    </select>
                </div>
            </div>
            
            <div class="control-group">
                <div class="control-item">
                    <label for="field-align">对齐方式</label>
                    <select id="field-align" onchange="updateFieldProperty('align', this.value)">
                        <option value="left">左对齐</option>
                        <option value="center">居中</option>
                        <option value="right">右对齐</option>
                    </select>
                </div>
                <div class="control-item">
                    <label for="field-text-color">文字颜色</label>
                    <input type="color" id="field-text-color" onchange="updateFieldProperty('textColor', this.value)">
                </div>
                <div class="control-item">
                    <label for="field-border-color">边框颜色</label>
                    <input type="color" id="field-border-color" onchange="updateFieldProperty('borderColor', this.value)">
                </div>
            </div>
            
            <div class="control-group">
                <div class="control-item">
                    <label>
                        <input type="checkbox" id="field-show-label" onchange="updateFieldProperty('showLabel', this.checked)">
                        显示标签
                    </label>
                </div>
                <div class="control-item">
                    <label for="field-label-position">标签位置</label>
                    <select id="field-label-position" onchange="updateFieldProperty('labelPosition', this.value)">
                        <option value="top">上方</option>
                        <option value="left">左侧</option>
                        <option value="bottom">下方</option>
                        <option value="right">右侧</option>
                    </select>
                </div>
            </div>
            
            <div class="control-group">
                <button class="danger" onclick="deleteSelectedField()">删除元素</button>
            </div>
        </div>
    </div>
    
    <!-- 添加元素面板 -->
    <div id="add-field-panel" class="add-field-panel">
        <h3>添加新元素</h3>
        <div class="field-type-selector">
            <button onclick="selectFieldType('text')">文本</button>
            <button onclick="selectFieldType('barcode')">条形码</button>
            <button onclick="selectFieldType('qrcode')">二维码</button>
        </div>
        
        <div id="text-field-form">
            <div class="control-group">
                <div class="control-item">
                    <label for="new-field-id">元素ID</label>
                    <input type="text" id="new-field-id" placeholder="例如：recipient_name">
                </div>
                <div class="control-item">
                    <label for="new-field-label">元素标签</label>
                    <input type="text" id="new-field-label" placeholder="例如：收件人姓名">
                </div>
            </div>
            <div class="control-group">
                <button onclick="addTextField()">添加文本元素</button>
            </div>
        </div>
        
        <div id="barcode-field-form" style="display: none;">
            <div class="control-group">
                <div class="control-item">
                    <label for="new-barcode-id">元素ID</label>
                    <input type="text" id="new-barcode-id" placeholder="例如：order_number">
                </div>
                <div class="control-item">
                    <label for="new-barcode-label">元素标签</label>
                    <input type="text" id="new-barcode-label" placeholder="例如：订单号">
                </div>
            </div>
            <div class="control-group">
                <div class="control-item">
                    <label for="new-barcode-type">条形码类型</label>
                    <select id="new-barcode-type">
                        <option value="code128">Code 128</option>
                        <option value="code39">Code 39</option>
                        <option value="ean13">EAN-13</option>
                        <option value="ean8">EAN-8</option>
                        <option value="upca">UPC-A</option>
                        <option value="itf14">ITF-14</option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <button onclick="addBarcodeField()">添加条形码元素</button>
            </div>
        </div>
        
        <div id="qrcode-field-form" style="display: none;">
            <div class="control-group">
                <div class="control-item">
                    <label for="new-qrcode-id">元素ID</label>
                    <input type="text" id="new-qrcode-id" placeholder="例如：tracking_number">
                </div>
                <div class="control-item">
                    <label for="new-qrcode-label">元素标签</label>
                    <input type="text" id="new-qrcode-label" placeholder="例如：物流单号">
                </div>
            </div>
            <div class="control-group">
                <div class="control-item">
                    <label for="new-qrcode-ec">纠错级别</label>
                    <select id="new-qrcode-ec">
                        <option value="L">低 (7%)</option>
                        <option value="M">中 (15%)</option>
                        <option value="Q">中高 (25%)</option>
                        <option value="H">高 (30%)</option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <button onclick="addQRCodeField()">添加二维码元素</button>
            </div>
        </div>
    </div>
    
    <!-- 配置输出区域 -->
    <div id="config-output">
        <div class="code-title">当前面单配置：</div>
        <pre id="config-json">$templateConfig</pre>
    </div>
    
    <script>
        // 模板配置
        const templateConfig = $templateConfig;
        let currentScale = 1.5; // 默认为1.5倍缩放，使面板更大
        let selectedFieldId = null;
        let isDragging = false;
        let isResizing = false;
        let dragStartX, dragStartY;
        let resizeStartX, resizeStartY;
        let resizeStartWidth, resizeStartHeight;
        let resizeCorner;
        
        // 毫米到像素的转换比例（每毫米约等于3.78像素）
        const MM_TO_PIXEL_RATIO = 3.78;
        
        // 初始化
        function init() {
            // 添加所有字段的拖拽和调整大小功能
            document.querySelectorAll('.field').forEach(field => {
                // 添加点击事件
                field.addEventListener('click', function(e) {
                    if (!isDragging && !isResizing) {
                        selectField(this.dataset.fieldId);
                        e.stopPropagation();
                    }
                });
                
                // 添加拖拽功能
                field.addEventListener('mousedown', function(e) {
                    // 如果点击的是调整大小手柄，不触发拖拽
                    if (e.target.classList.contains('resize-handle')) {
                        return;
                    }
                    
                    isDragging = true;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;
                    
                    // 选中当前字段
                    selectField(this.dataset.fieldId);
                    
                    e.preventDefault();
                });
                
                // 添加四个角的调整大小手柄
                addResizeHandles(field);
            });
            
            // 点击空白处取消选择
            document.addEventListener('click', function(e) {
                if (!isDragging && !isResizing && !e.target.closest('.field')) {
                    deselectField();
                }
            });
            
            // 处理鼠标移动
            document.addEventListener('mousemove', function(e) {
                if (isDragging && selectedFieldId) {
                    handleDrag(e);
                } else if (isResizing && selectedFieldId) {
                    handleResize(e);
                }
            });
            
            // 处理鼠标释放
            document.addEventListener('mouseup', function() {
                isDragging = false;
                isResizing = false;
            });
            
            // 默认显示元素控制面板
            document.getElementById('field-controls').style.display = 'block';
        }
        
        // 添加调整大小手柄
        function addResizeHandles(field) {
            const corners = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];
            
            corners.forEach(corner => {
                const handle = document.createElement('div');
                handle.className = `resize-handle ${corner}`;
                
                handle.addEventListener('mousedown', function(e) {
                    isResizing = true;
                    resizeCorner = corner;
                    resizeStartX = e.clientX;
                    resizeStartY = e.clientY;
                    
                    const fieldElement = handle.parentElement;
                    resizeStartWidth = parseFloat(fieldElement.style.width);
                    resizeStartHeight = parseFloat(fieldElement.style.height);
                    
                    // 选中当前字段
                    selectField(field.dataset.fieldId);
                    
                    e.preventDefault();
                    e.stopPropagation();
                });
                
                field.appendChild(handle);
            });
        }
        
        // 选择字段
        function selectField(fieldId) {
            // 取消之前的选择
            deselectField();
            
            selectedFieldId = fieldId;
            const fieldElement = document.querySelector(`[data-field-id="${fieldId}"]`);
            
            if (fieldElement) {
                fieldElement.classList.add('selected');
                
                // 查找字段配置
                const fieldConfig = templateConfig.fields.find(f => f.id === fieldId);
                if (fieldConfig) {
                    // 填充属性表单
                    document.getElementById('selected-field-id').value = fieldId;
                    document.getElementById('field-label').value = fieldConfig.label || '';
                    document.getElementById('field-x').value = fieldConfig.x || 0;
                    document.getElementById('field-y').value = fieldConfig.y || 0;
                    document.getElementById('field-width').value = fieldConfig.width || 0;
                    document.getElementById('field-height').value = fieldConfig.height || 0;
                    document.getElementById('field-font-size').value = fieldConfig.fontSize || 8;
                    document.getElementById('field-font-family').value = fieldConfig.fontFamily || 'Arial';
                    document.getElementById('field-font-weight').value = fieldConfig.fontWeight || 'normal';
                    document.getElementById('field-align').value = fieldConfig.align || 'left';
                    document.getElementById('field-text-color').value = fieldConfig.textColor || '#000000';
                    document.getElementById('field-border-width').value = fieldConfig.borderWidth || 0;
                    document.getElementById('field-border-color').value = fieldConfig.borderColor || '#000000';
                    document.getElementById('field-show-label').checked = fieldConfig.showLabel !== false;
                    document.getElementById('field-label-position').value = fieldConfig.labelPosition || 'top';
                    
                    // 显示属性面板，隐藏无选择提示
                    document.getElementById('field-properties').style.display = 'block';
                    document.getElementById('no-selection-message').style.display = 'none';
                }
            }
        }
        
        // 取消选择字段
        function deselectField() {
            if (selectedFieldId) {
                const fieldElement = document.querySelector(`[data-field-id="${selectedFieldId}"]`);
                if (fieldElement) {
                    fieldElement.classList.remove('selected');
                }
                selectedFieldId = null;
            }
            
            // 隐藏属性面板，显示无选择提示
            document.getElementById('field-properties').style.display = 'none';
            document.getElementById('no-selection-message').style.display = 'block';
        }
        
        // 处理拖拽
        function handleDrag(e) {
            const fieldElement = document.querySelector(`[data-field-id="${selectedFieldId}"]`);
            if (!fieldElement) return;
            
            // 计算移动距离
            const dx = e.clientX - dragStartX;
            const dy = e.clientY - dragStartY;
            
            // 获取当前位置
            const currentLeft = parseFloat(fieldElement.style.left) || 0;
            const currentTop = parseFloat(fieldElement.style.top) || 0;
            
            // 计算新位置
            const newLeft = currentLeft + dx;
            const newTop = currentTop + dy;
            
            // 更新元素位置
            fieldElement.style.left = newLeft + 'px';
            fieldElement.style.top = newTop + 'px';
            
            // 更新开始位置
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            
            // 更新配置中的坐标
            const fieldConfig = templateConfig.fields.find(f => f.id === selectedFieldId);
            if (fieldConfig) {
                fieldConfig.x = (newLeft / currentScale);
                fieldConfig.y = (newTop / currentScale);
                
                // 更新表单中的坐标值
                document.getElementById('field-x').value = fieldConfig.x.toFixed(1);
                document.getElementById('field-y').value = fieldConfig.y.toFixed(1);
            }
        }
        
        // 处理调整大小
        function handleResize(e) {
            const fieldElement = document.querySelector(`[data-field-id="${selectedFieldId}"]`);
            if (!fieldElement) return;
            
            // 计算变化量
            const dx = e.clientX - resizeStartX;
            const dy = e.clientY - resizeStartY;
            
            // 计算新的宽度和高度
            let newWidth = resizeStartWidth;
            let newHeight = resizeStartHeight;
            let newX = parseFloat(fieldElement.style.left) || 0;
            let newY = parseFloat(fieldElement.style.top) || 0;
            
            // 根据调整的角计算新尺寸和位置
            if (resizeCorner.includes('right')) {
                newWidth += dx;
            } else {
                newWidth -= dx;
                newX += dx;
            }
            
            if (resizeCorner.includes('bottom')) {
                newHeight += dy;
            } else {
                newHeight -= dy;
                newY += dy;
            }
            
            // 确保尺寸不小于最小限制
            const minSize = 20; // 最小尺寸 (像素)
            if (newWidth < minSize) newWidth = minSize;
            if (newHeight < minSize) newHeight = minSize;
            
            // 检查是否超出画布边界
            const canvas = document.querySelector('.label-preview');
            const canvasRect = canvas.getBoundingClientRect();
            const fieldRect = { width: newWidth, height: newHeight };
            
            if (newX < 0) newX = 0;
            if (newY < 0) newY = 0;
            if (newX + fieldRect.width > canvasRect.width) {
                newX = canvasRect.width - fieldRect.width;
            }
            if (newY + fieldRect.height > canvasRect.height) {
                newY = canvasRect.height - fieldRect.height;
            }
            
            // 更新元素尺寸和位置
            fieldElement.style.width = newWidth + 'px';
            fieldElement.style.height = newHeight + 'px';
            fieldElement.style.left = newX + 'px';
            fieldElement.style.top = newY + 'px';
            
            // 更新配置
            const fieldConfig = templateConfig.fields.find(f => f.id === selectedFieldId);
            if (fieldConfig) {
                fieldConfig.width = (newWidth / currentScale);
                fieldConfig.height = (newHeight / currentScale);
                fieldConfig.x = (newX / currentScale);
                fieldConfig.y = (newY / currentScale);
                
                // 更新表单中的值
                document.getElementById('field-width').value = fieldConfig.width.toFixed(1);
                document.getElementById('field-height').value = fieldConfig.height.toFixed(1);
                document.getElementById('field-x').value = fieldConfig.x.toFixed(1);
                document.getElementById('field-y').value = fieldConfig.y.toFixed(1);
            }
        }
        
        // 更新字段属性
        function updateFieldProperty(property, value) {
            if (!selectedFieldId) return;
            
            const fieldConfig = templateConfig.fields.find(f => f.id === selectedFieldId);
            if (fieldConfig) {
                fieldConfig[property] = value;
                updateFieldElement();
                updateConfigOutput();
                showSuccessMessage();
            }
        }
        
        // 更新字段位置
        function updateFieldPosition(axis, value) {
            if (!selectedFieldId) return;
            
            const fieldConfig = templateConfig.fields.find(f => f.id === selectedFieldId);
            if (fieldConfig) {
                const numericValue = parseFloat(value) || 0;
                fieldConfig[axis] = numericValue;
                
                // 更新元素位置
                const fieldElement = document.querySelector(`[data-field-id="${selectedFieldId}"]`);
                if (fieldElement) {
                    const position = numericValue * currentScale;
                    fieldElement.style[axis === 'x' ? 'left' : 'top'] = position + 'px';
                }
                
                updateConfigOutput();
                showSuccessMessage();
            }
        }
        
        // 更新字段大小
        function updateFieldSize(dimension, value) {
            if (!selectedFieldId) return;
            
            const fieldConfig = templateConfig.fields.find(f => f.id === selectedFieldId);
            if (fieldConfig) {
                const numericValue = parseFloat(value) || 0;
                fieldConfig[dimension] = numericValue;
                
                // 更新元素大小
                const fieldElement = document.querySelector(`[data-field-id="${selectedFieldId}"]`);
                if (fieldElement) {
                    const size = numericValue * currentScale;
                    fieldElement.style[dimension] = size + 'px';
                }
                
                updateConfigOutput();
                showSuccessMessage();
            }
        }
        
        // 更新字段元素
        function updateFieldElement() {
            if (!selectedFieldId) return;
            
            const fieldElement = document.querySelector(`[data-field-id="${selectedFieldId}"]`);
            const fieldConfig = templateConfig.fields.find(f => f.id === selectedFieldId);
            
            if (fieldElement && fieldConfig) {
                // 更新标签
                const labelElement = fieldElement.querySelector('.field-label');
                if (labelElement) {
                    labelElement.textContent = fieldConfig.label || '';
                }
                
                // 更新样式
                fieldElement.style.fontSize = (fieldConfig.fontSize * currentScale) + 'px';
                fieldElement.style.fontFamily = fieldConfig.fontFamily || 'Arial';
                fieldElement.style.fontWeight = fieldConfig.fontWeight || 'normal';
                fieldElement.style.textAlign = fieldConfig.align || 'left';
                fieldElement.style.color = fieldConfig.textColor || '#000000';
                fieldElement.style.borderWidth = (fieldConfig.borderWidth * currentScale) + 'px';
                fieldElement.style.borderColor = fieldConfig.borderColor || '#000000';
                
                // 更新标签显示
                if (labelElement) {
                    labelElement.style.display = fieldConfig.showLabel !== false ? 'block' : 'none';
                }
            }
        }
        
        // 切换元素面板
        function toggleFieldPanel() {
            const panel = document.getElementById('add-field-panel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
        
        // 切换配置输出
        function toggleConfigOutput() {
            const output = document.getElementById('config-output');
            output.style.display = output.style.display === 'none' ? 'block' : 'none';
            
            if (output.style.display !== 'none') {
                updateConfigOutput();
            }
        }
        
        // 更新配置输出
        function updateConfigOutput() {
            document.getElementById('config-json').textContent = JSON.stringify(templateConfig, null, 2);
        }
        
        // 更改缩放比例
        function changeScale(scale) {
            currentScale = parseFloat(scale);
            
            const labelPreview = document.querySelector('.label-preview');
            const dimensions = templateConfig.dimensions;
            
            // 使用毫米到像素的转换比例结合缩放因子，确保正确的物理尺寸显示
            const pixelWidth = dimensions.width * MM_TO_PIXEL_RATIO * currentScale;
            const pixelHeight = dimensions.height * MM_TO_PIXEL_RATIO * currentScale;
            
            // 更新预览容器尺寸
            labelPreview.style.width = pixelWidth + 'px';
            labelPreview.style.height = pixelHeight + 'px';
            
            // 更新所有字段的尺寸和位置
            document.querySelectorAll('.field').forEach(field => {
                const fieldId = field.dataset.fieldId;
                const fieldConfig = templateConfig.fields.find(f => f.id === fieldId);
                
                if (fieldConfig) {
                    // 使用毫米到像素的转换比例
                    field.style.left = (fieldConfig.x * MM_TO_PIXEL_RATIO * currentScale) + 'px';
                    field.style.top = (fieldConfig.y * MM_TO_PIXEL_RATIO * currentScale) + 'px';
                    field.style.width = (fieldConfig.width * MM_TO_PIXEL_RATIO * currentScale) + 'px';
                    field.style.height = (fieldConfig.height * MM_TO_PIXEL_RATIO * currentScale) + 'px';
                    
                    // 字体大小需要特别处理，不需要乘以MM_TO_PIXEL_RATIO
                    field.style.fontSize = (fieldConfig.fontSize * currentScale) + 'px';
                    
                    // 边框宽度也需要特别处理
                    field.style.borderWidth = (fieldConfig.borderWidth * currentScale) + 'px';
                }
            });
            
            // 更新当前尺寸显示信息
            updateSizeInfo();
        }
        
        // 更新当前尺寸信息
        function updateSizeInfo() {
            const dimensions = templateConfig.dimensions;
            const sizeInfo = document.createElement('div');
            sizeInfo.className = 'size-info';
            sizeInfo.textContent = `${dimensions.width}mm × ${dimensions.height}mm (实际显示: ${Math.round(dimensions.width * MM_TO_PIXEL_RATIO * currentScale)}px × ${Math.round(dimensions.height * MM_TO_PIXEL_RATIO * currentScale)}px)`;
            
            // 移除旧的尺寸信息
            const oldSizeInfo = document.querySelector('.size-info');
            if (oldSizeInfo) {
                oldSizeInfo.remove();
            }
            
            // 添加新的尺寸信息
            const labelPreview = document.querySelector('.label-preview');
            labelPreview.parentNode.insertBefore(sizeInfo, labelPreview.nextSibling);
        }
        
        // 更改面单规格
        function changeSize(size) {
            // 在实际应用中，这里应该调用服务器API来重新获取对应规格的模板配置
            // 这里为了演示，只更新本地配置中的尺寸信息
            
            const sizeConfigurations = {
                'A4': { width: 210, height: 297 },
                'A5': { width: 148, height: 210 },
                '10x15': { width: 100, height: 150 },
                '8x10': { width: 80, height: 100 },
                'ems_default': { width: 100, height: 140 }
            };
            
            const newDimensions = sizeConfigurations[size] || sizeConfigurations['ems_default'];
            templateConfig.size = size;
            templateConfig.dimensions = newDimensions;
            
            // 更新预览容器尺寸
            changeScale(currentScale);
            
            // 更新配置输出
            updateConfigOutput();
        }
        
        // 导出为PDF
        function exportToPdf() {
            alert('导出PDF功能需要在服务器端实现，这里只是演示。');
            // 在实际应用中，这里应该调用服务器API来生成PDF
        }
        
        // 选择字段类型
        function selectFieldType(type) {
            // 隐藏所有表单
            document.getElementById('text-field-form').style.display = 'none';
            document.getElementById('barcode-field-form').style.display = 'none';
            document.getElementById('qrcode-field-form').style.display = 'none';
            
            // 显示选中类型的表单
            document.getElementById(`${type}-field-form`).style.display = 'block';
        }
        
        // 添加文本字段
        function addTextField() {
            const id = document.getElementById('new-field-id').value.trim();
            const label = document.getElementById('new-field-label').value.trim();
            
            if (!id || !label) {
                alert('请输入元素ID和标签');
                return;
            }
            
            // 检查ID是否已存在
            if (templateConfig.fields.some(f => f.id === id)) {
                alert('元素ID已存在');
                return;
            }
            
            // 添加新字段配置
            const newField = {
                id: id,
                label: label,
                x: 10,
                y: 10,
                width: 50,
                height: 20,
                type: 'text',
                fontSize: 8,
                fontFamily: 'Arial',
                fontWeight: 'normal',
                align: 'left',
                textColor: '#000000',
                labelFontSize: 7,
                labelColor: '#666666',
                borderWidth: 0,
                showLabel: true,
                labelPosition: 'top',
                padding: { top: 0, left: 0 }
            };
            
            templateConfig.fields.push(newField);
            
            // 在界面上添加新字段
            addFieldToInterface(newField);
            
            // 更新配置输出
            updateConfigOutput();
            
            // 清空表单
            document.getElementById('new-field-id').value = '';
            document.getElementById('new-field-label').value = '';
            
            showSuccessMessage();
        }
        
        // 添加条形码字段
        function addBarcodeField() {
            const id = document.getElementById('new-barcode-id').value.trim();
            const label = document.getElementById('new-barcode-label').value.trim();
            const barcodeType = document.getElementById('new-barcode-type').value;
            
            if (!id || !label) {
                alert('请输入元素ID和标签');
                return;
            }
            
            // 检查ID是否已存在
            if (templateConfig.fields.some(f => f.id === id)) {
                alert('元素ID已存在');
                return;
            }
            
            // 添加新字段配置
            const newField = {
                id: id,
                label: label,
                x: 10,
                y: 10,
                width: 80,
                height: 30,
                type: 'barcode',
                barcodeType: barcodeType,
                barcodeModuleWidth: 0.25,
                barcodeHeight: 15,
                fontSize: 8,
                fontFamily: 'Arial',
                textColor: '#000000',
                labelFontSize: 7,
                labelColor: '#666666',
                borderWidth: 0,
                showLabel: true,
                labelPosition: 'top'
            };
            
            templateConfig.fields.push(newField);
            
            // 在界面上添加新字段
            addFieldToInterface(newField);
            
            // 更新配置输出
            updateConfigOutput();
            
            // 清空表单
            document.getElementById('new-barcode-id').value = '';
            document.getElementById('new-barcode-label').value = '';
            
            showSuccessMessage();
        }
        
        // 添加二维码字段
        function addQRCodeField() {
            const id = document.getElementById('new-qrcode-id').value.trim();
            const label = document.getElementById('new-qrcode-label').value.trim();
            const ecLevel = document.getElementById('new-qrcode-ec').value;
            
            if (!id || !label) {
                alert('请输入元素ID和标签');
                return;
            }
            
            // 检查ID是否已存在
            if (templateConfig.fields.some(f => f.id === id)) {
                alert('元素ID已存在');
                return;
            }
            
            // 添加新字段配置
            const newField = {
                id: id,
                label: label,
                x: 10,
                y: 10,
                width: 40,
                height: 40,
                type: 'qrcode',
                qrcodeSize: 100,
                qrcodeErrorCorrection: ecLevel,
                fontSize: 8,
                fontFamily: 'Arial',
                textColor: '#000000',
                labelFontSize: 7,
                labelColor: '#666666',
                borderWidth: 0,
                showLabel: true,
                labelPosition: 'top'
            };
            
            templateConfig.fields.push(newField);
            
            // 在界面上添加新字段
            addFieldToInterface(newField);
            
            // 更新配置输出
            updateConfigOutput();
            
            // 清空表单
            document.getElementById('new-qrcode-id').value = '';
            document.getElementById('new-qrcode-label').value = '';
            
            showSuccessMessage();
        }
        
        // 在界面上添加新字段
        function addFieldToInterface(fieldConfig) {
            const labelPreview = document.querySelector('.label-preview');
            const fieldElement = document.createElement('div');
            
            // 设置字段基本属性
            fieldElement.className = 'field';
            fieldElement.dataset.fieldId = fieldConfig.id;
            
            // 使用毫米到像素的转换比例
            fieldElement.style.left = (fieldConfig.x * MM_TO_PIXEL_RATIO * currentScale) + 'px';
            fieldElement.style.top = (fieldConfig.y * MM_TO_PIXEL_RATIO * currentScale) + 'px';
            fieldElement.style.width = (fieldConfig.width * MM_TO_PIXEL_RATIO * currentScale) + 'px';
            fieldElement.style.height = (fieldConfig.height * MM_TO_PIXEL_RATIO * currentScale) + 'px';
            fieldElement.style.fontFamily = fieldConfig.fontFamily || 'Arial';
            fieldElement.style.fontSize = (fieldConfig.fontSize * currentScale) + 'px';
            fieldElement.style.fontWeight = fieldConfig.fontWeight || 'normal';
            fieldElement.style.textAlign = fieldConfig.align || 'left';
            fieldElement.style.color = fieldConfig.textColor || '#000000';
            fieldElement.style.border = ((fieldConfig.borderWidth || 0) * currentScale) + 'px solid ' + (fieldConfig.borderColor || '#000000');
            fieldElement.style.backgroundColor = fieldConfig.backgroundColor || '#ffffff';
            
            // 添加标签
            if (fieldConfig.showLabel !== false) {
                const labelElement = document.createElement('div');
                labelElement.className = 'field-label';
                labelElement.style.fontSize = (fieldConfig.labelFontSize * currentScale) + 'px';
                labelElement.style.color = fieldConfig.labelColor || '#666666';
                labelElement.textContent = fieldConfig.label;
                fieldElement.appendChild(labelElement);
            }
            
            // 根据字段类型添加内容
            const contentElement = document.createElement('div');
            
            if (fieldConfig.type === 'barcode') {
                contentElement.className = 'barcode-placeholder';
                contentElement.textContent = '[条形码] ' + fieldConfig.id;
            } else if (fieldConfig.type === 'qrcode') {
                contentElement.className = 'qrcode-placeholder';
                contentElement.textContent = '[二维码]';
                contentElement.style.margin = 'auto';
                contentElement.style.width = '80%';
                contentElement.style.height = '80%';
                contentElement.style.backgroundColor = '#f0f0f0';
                contentElement.style.display = 'flex';
                contentElement.style.alignItems = 'center';
                contentElement.style.justifyContent = 'center';
            } else {
                contentElement.className = 'field-content';
                contentElement.textContent = '{{' + fieldConfig.id + '}}';
            }
            
            fieldElement.appendChild(contentElement);
            
            // 添加点击事件
            fieldElement.addEventListener('click', function(e) {
                if (!isDragging && !isResizing) {
                    selectField(this.dataset.fieldId);
                    e.stopPropagation();
                }
            });
            
            // 添加拖拽功能
            fieldElement.addEventListener('mousedown', function(e) {
                // 如果点击的是调整大小手柄，不触发拖拽
                if (e.target.classList.contains('resize-handle')) {
                    return;
                }
                
                isDragging = true;
                dragStartX = e.clientX;
                dragStartY = e.clientY;
                
                // 选中当前字段
                selectField(this.dataset.fieldId);
                
                e.preventDefault();
            });
            
            // 添加调整大小手柄
            addResizeHandles(fieldElement);
            
            // 添加到预览容器
            labelPreview.appendChild(fieldElement);
        }
        
        // 删除选中字段
        function deleteSelectedField() {
            if (!selectedFieldId) return;
            
            if (confirm('确定要删除这个元素吗？')) {
                // 从配置中删除
                const index = templateConfig.fields.findIndex(f => f.id === selectedFieldId);
                if (index > -1) {
                    templateConfig.fields.splice(index, 1);
                }
                
                // 从界面中删除
                const fieldElement = document.querySelector(`[data-field-id="${selectedFieldId}"]`);
                if (fieldElement) {
                    fieldElement.remove();
                }
                
                // 取消选择
                deselectField();
                
                // 更新配置输出
                updateConfigOutput();
                
                showSuccessMessage();
            }
        }
        
        // 显示成功消息
        function showSuccessMessage() {
            const message = document.getElementById('success-message');
            message.style.display = 'block';
            
            setTimeout(() => {
                message.style.display = 'none';
            }, 2000);
        }
        
        // 页面加载完成后初始化
        window.addEventListener('load', init);
    </script>
</body>
</html>
    
    /**
     * 导出面单为PDF（需要外部PDF库支持）
     *
     * @param string $outputPath 输出文件路径
     * @return bool 是否成功
     */
    public function exportToPdf(string $outputPath): bool
    {
        // 此方法需要集成PDF生成库，如TCPDF、FPDF或其他PHP PDF库
        // 这里仅提供接口，实际项目中需要安装并引入相应库
        
        // 示例实现（需要安装TCPDF）：
        /*
        require_once 'TCPDF/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, [$this->template->getWidth(), $this->template->getHeight()], true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Kode Express API');
        $pdf->SetTitle($this->template->getName());
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        
        // 渲染各个字段
        foreach ($this->template->getFields() as $field) {
            // 根据字段类型渲染不同内容
            if ($field->getType() === 'text') {
                $pdf->SetFont($field->getFontFamily(), $field->getFontWeight(), $field->getFontSize());
                $pdf->SetTextColorArray($this->hexToRgb($field->getTextColor()));
                $pdf->Text($field->getX(), $field->getY(), '{{' . $field->getId() . '}}');
            } elseif ($field->getType() === 'barcode') {
                // 生成条形码
                $pdf->write1DBarcode('{{' . $field->getId() . '}}', $field->getBarcodeType(), 
                    $field->getX(), $field->getY(), $field->getWidth(), $field->getHeight());
            } elseif ($field->getType() === 'qrcode') {
                // 生成二维码
                $pdf->write2DBarcode('https://api.ems.com.cn/tracking/' . '{{' . $field->getId() . '}}', 
                    'QRCODE', $field->getX(), $field->getY(), $field->getWidth(), $field->getHeight(), 
                    ['eclevel' => $field->getQrcodeErrorCorrection()]);
            }
        }
        
        return $pdf->Output($outputPath, 'F') === '';
        */
        
        // 演示返回，实际使用时请实现具体逻辑
        return false;
    }
    
    /**
     * 生成条形码图像数据
     *
     * @param string $text 条形码内容
     * @param string $type 条形码类型
     * @return string|null 图像数据或null
     */
    protected function generateBarcode(string $text, string $type): ?string
    {
        // 此方法需要集成条形码生成库
        // 示例实现可使用PHP Barcode Generator库
        return null;
    }
    
    /**
     * 生成二维码图像数据
     *
     * @param string $text 二维码内容
     * @param string $ecLevel 纠错级别
     * @return string|null 图像数据或null
     */
    protected function generateQrCode(string $text, string $ecLevel = 'M'): ?string
    {
        // 此方法需要集成二维码生成库
        // 示例实现可使用phpqrcode库
        return null;
    }
    
    /**
     * 将十六进制颜色转换为RGB数组
     *
     * @param string $hex 十六进制颜色
     * @return array RGB数组
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return [$r, $g, $b];
    }
    
    /**
     * 获取支持的尺寸列表
     *
     * @return array 尺寸列表
     */
    public function getSupportedSizes(): array
    {
        return array_keys($this->sizeConfigurations);
    }
    
    /**
     * 获取尺寸详细信息
     *
     * @param string $size 尺寸名称
     * @return array|null 尺寸信息或null
     */
    public function getSizeInfo(string $size): ?array
    {
        return $this->sizeConfigurations[$size] ?? null;
    }
}