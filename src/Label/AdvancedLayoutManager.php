<?php

namespace Kode\ExpressApi\Label;

use Kode\ExpressApi\Label\BaseTemplate;
use Kode\ExpressApi\Label\ConcreteTemplate;
use Kode\ExpressApi\Label\Field;
use Kode\ExpressApi\Label\MultilingualField;

/**
 * 高级面单布局管理器
 * 
 * 提供增强的面单模板管理功能，支持更复杂的布局配置和多语言支持
 */
class AdvancedLayoutManager
{
    /**
     * @var string 模板存储路径
     */
    protected $templatePath;

    /**
     * @var array 支持的面单尺寸
     */
    protected $supportedSizes = [
        'standard_100x150' => ['width' => 100, 'height' => 150],
        'standard_100x100' => ['width' => 100, 'height' => 100],
        'standard_80x80' => ['width' => 80, 'height' => 80],
        'custom' => ['width' => 0, 'height' => 0]
    ];

    /**
     * @var array 支持的快递公司
     */
    protected $supportedCouriers = [
        'ems', 'sf', 'yt', 'zt', 'sto', 'jd', 'db', 'yto', 'zto'
    ];

    /**
     * 构造函数
     *
     * @param string $templatePath 模板存储路径
     */
    public function __construct(string $templatePath)
    {
        $this->templatePath = rtrim($templatePath, '/');
        $this->ensureTemplateDirectoryExists();
    }

    /**
     * 确保模板目录存在
     *
     * @return void
     */
    protected function ensureTemplateDirectoryExists(): void
    {
        if (!is_dir($this->templatePath)) {
            mkdir($this->templatePath, 0755, true);
        }
    }

    /**
     * 创建面单模板
     *
     * @param array $config 模板配置
     * @return BaseTemplate
     */
    public function createTemplate(array $config): BaseTemplate
    {
        // 验证配置
        $this->validateTemplateConfig($config);
        
        // 创建模板实例
        $template = new ConcreteTemplate($config);
        
        // 设置默认字段
        $this->setupDefaultFields($template, $config);
        
        return $template;
    }

    /**
     * 验证模板配置
     *
     * @param array $config 模板配置
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateTemplateConfig(array $config): void
    {
        // 验证必需字段
        $requiredFields = ['id', 'name', 'courier', 'size'];
        foreach ($requiredFields as $field) {
            if (!isset($config[$field])) {
                throw new \InvalidArgumentException("缺少必需的配置字段: {$field}");
            }
        }

        // 验证快递公司
        if (!in_array($config['courier'], $this->supportedCouriers)) {
            throw new \InvalidArgumentException("不支持的快递公司: {$config['courier']}");
        }

        // 验证尺寸
        if (is_string($config['size'])) {
            if (!isset($this->supportedSizes[$config['size']])) {
                throw new \InvalidArgumentException("不支持的面单尺寸: {$config['size']}");
            }
        } elseif (is_array($config['size'])) {
            if (!isset($config['size']['width']) || !isset($config['size']['height'])) {
                throw new \InvalidArgumentException("尺寸配置必须包含width和height字段");
            }
        }
    }

    /**
     * 设置默认字段
     *
     * @param BaseTemplate $template 模板实例
     * @param array $config 模板配置
     * @return void
     */
    protected function setupDefaultFields(BaseTemplate $template, array $config): void
    {
        // 如果没有字段配置，添加默认字段
        if (empty($config['fields'])) {
            $defaultFields = $this->getDefaultFields($config['courier']);
            foreach ($defaultFields as $fieldName => $fieldConfig) {
                $template->addField($fieldName, $fieldConfig);
            }
        }
    }

    /**
     * 获取默认字段配置
     *
     * @param string $courier 快递公司
     * @return array
     */
    protected function getDefaultFields(string $courier): array
    {
        // 根据快递公司返回不同的默认字段配置
        switch ($courier) {
            case 'ems':
                return [
                    // 发件人信息
                    'sender_name' => [
                        'label' => '发件人',
                        'x' => 5,
                        'y' => 5,
                        'width' => 40,
                        'height' => 8,
                        'font_size' => 9,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'bold' => true,
                        'text_color' => '#000000',
                        'label_font_size' => 8,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 2, 'left' => 3]
                    ],
                    'sender_phone' => [
                        'label' => '电话',
                        'x' => 5,
                        'y' => 13,
                        'width' => 40,
                        'height' => 6,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    'sender_address' => [
                        'label' => '地址',
                        'x' => 5,
                        'y' => 19,
                        'width' => 45,
                        'height' => 12,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    
                    // 收件人信息
                    'recipient_name' => [
                        'label' => '收件人',
                        'x' => 5,
                        'y' => 33,
                        'width' => 40,
                        'height' => 8,
                        'font_size' => 9,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'bold' => true,
                        'text_color' => '#000000',
                        'label_font_size' => 8,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 2, 'left' => 3]
                    ],
                    'recipient_phone' => [
                        'label' => '电话',
                        'x' => 5,
                        'y' => 41,
                        'width' => 40,
                        'height' => 6,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    'recipient_address' => [
                        'label' => '地址',
                        'x' => 5,
                        'y' => 47,
                        'width' => 45,
                        'height' => 12,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    
                    // 订单信息
                    'order_no' => [
                        'label' => '订单号',
                        'x' => 55,
                        'y' => 5,
                        'width' => 40,
                        'height' => 6,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    'weight' => [
                        'label' => '重量',
                        'x' => 55,
                        'y' => 12,
                        'width' => 20,
                        'height' => 6,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    'service_type' => [
                        'label' => '服务类型',
                        'x' => 75,
                        'y' => 12,
                        'width' => 20,
                        'height' => 6,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    
                    // 条码信息
                    'tracking_no' => [
                        'label' => '运单号',
                        'x' => 5,
                        'y' => 61,
                        'width' => 60,
                        'height' => 25,
                        'font_size' => 12,
                        'align' => 'center',
                        'font_family' => 'Arial',
                        'type' => 'barcode',
                        'barcode_type' => 'code128',
                        'barcode_module_width' => 0.25,
                        'barcode_height' => 15,
                        'text_color' => '#000000',
                        'label_font_size' => 8,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'show_label' => true,
                        'label_position' => 'top'
                    ],
                    
                    // 二维码
                    'qrcode' => [
                        'label' => '追踪二维码',
                        'x' => 70,
                        'y' => 65,
                        'width' => 25,
                        'height' => 25,
                        'type' => 'qrcode',
                        'qrcode_size' => 100,
                        'qrcode_error_correction' => 'M',
                        'text_color' => '#000000',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'show_label' => false
                    ],
                    
                    // 产品信息
                    'product_name' => [
                        'label' => '物品名称',
                        'x' => 5,
                        'y' => 90,
                        'width' => 50,
                        'height' => 10,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    'quantity' => [
                        'label' => '数量',
                        'x' => 55,
                        'y' => 90,
                        'width' => 20,
                        'height' => 6,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    
                    // 价格信息
                    'total_amount' => [
                        'label' => '总金额',
                        'x' => 75,
                        'y' => 90,
                        'width' => 20,
                        'height' => 6,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    
                    // 备注信息
                    'remark' => [
                        'label' => '备注',
                        'x' => 5,
                        'y' => 105,
                        'width' => 90,
                        'height' => 12,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    
                    // 区域信息
                    'shipping_area' => [
                        'label' => '寄达区域',
                        'x' => 5,
                        'y' => 120,
                        'width' => 45,
                        'height' => 6,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ],
                    
                    // 日期信息
                    'create_date' => [
                        'label' => '创建日期',
                        'x' => 55,
                        'y' => 120,
                        'width' => 40,
                        'height' => 6,
                        'font_size' => 8,
                        'align' => 'left',
                        'font_family' => 'Arial',
                        'text_color' => '#333333',
                        'label_font_size' => 7,
                        'label_color' => '#666666',
                        'border_width' => 0,
                        'padding' => ['top' => 1, 'left' => 3]
                    ]
                ];
            
            case 'sf':
                return [
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
                ];
            
            default:
                return [
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
                    ]
                ];
        }
    }

    /**
     * 保存模板
     *
     * @param BaseTemplate $template 模板对象
     * @return bool
     */
    public function saveTemplate(BaseTemplate $template): bool
    {
        $filename = $this->templatePath . '/' . $template->getId() . '.json';
        $data = $template->toArray();

        // 添加版本信息
        $data['version'] = '2.0';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * 获取模板
     *
     * @param string $templateId 模板ID
     * @return BaseTemplate|null
     */
    public function getTemplate(string $templateId): ?BaseTemplate
    {
        $filename = $this->templatePath . '/' . $templateId . '.json';

        if (!file_exists($filename)) {
            return null;
        }

        $data = json_decode(file_get_contents($filename), true);

        if (!$data) {
            return null;
        }

        // 处理版本兼容性
        return $this->createTemplateFromData($data);
    }

    /**
     * 从数据创建模板
     *
     * @param array $data 模板数据
     * @return BaseTemplate
     */
    protected function createTemplateFromData(array $data): BaseTemplate
    {
        $template = new ConcreteTemplate($data);
        
        // 如果有字段数据，添加字段
        if (isset($data['fields']) && is_array($data['fields'])) {
            foreach ($data['fields'] as $fieldId => $fieldData) {
                // 检查是否是多语言字段
                if (isset($fieldData['labels']) && is_array($fieldData['labels'])) {
                    $field = new MultilingualField($fieldId, $fieldData);
                } else {
                    $field = new Field($fieldId, $fieldData);
                }
                $template->setField($fieldId, $field);
            }
        }

        return $template;
    }

    /**
     * 删除模板
     *
     * @param string $templateId 模板ID
     * @return bool
     */
    public function deleteTemplate(string $templateId): bool
    {
        $filename = $this->templatePath . '/' . $templateId . '.json';

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return false;
    }

    /**
     * 列出所有模板
     *
     * @return BaseTemplate[]
     */
    public function listTemplates(): array
    {
        $templates = [];

        $files = glob($this->templatePath . '/*.json');

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $templates[] = $this->createTemplateFromData($data);
            }
        }

        return $templates;
    }

    /**
     * 根据快递公司列出模板
     *
     * @param string $courier 快递公司
     * @return BaseTemplate[]
     */
    public function listTemplatesByCourier(string $courier): array
    {
        $allTemplates = $this->listTemplates();
        $filteredTemplates = [];

        foreach ($allTemplates as $template) {
            if ($template->getCourier() === $courier) {
                $filteredTemplates[] = $template;
            }
        }

        return $filteredTemplates;
    }

    /**
     * 复制模板
     *
     * @param string $sourceTemplateId 源模板ID
     * @param string $newTemplateId 新模板ID
     * @param string|null $newTemplateName 新模板名称
     * @return BaseTemplate|null
     */
    public function copyTemplate(string $sourceTemplateId, string $newTemplateId, ?string $newTemplateName = null): ?BaseTemplate
    {
        $sourceTemplate = $this->getTemplate($sourceTemplateId);
        
        if (!$sourceTemplate) {
            return null;
        }

        // 创建新模板配置
        $config = $sourceTemplate->toArray();
        $config['id'] = $newTemplateId;
        $config['name'] = $newTemplateName ?? $config['name'] . ' (副本)';
        $config['updated_at'] = date('Y-m-d H:i:s');

        // 创建并保存新模板
        $newTemplate = $this->createTemplate($config);
        $this->saveTemplate($newTemplate);

        return $newTemplate;
    }

    /**
     * 验证模板数据
     *
     * @param BaseTemplate $template 模板对象
     * @return array 验证结果
     */
    public function validateTemplate(BaseTemplate $template): array
    {
        $errors = [];

        // 验证字段位置是否在模板范围内
        $size = $template->getSize();
        $width = $size['width'] ?? 100;
        $height = $size['height'] ?? 150;

        foreach ($template->getFields() as $field) {
            $fieldId = $field->getId();
            
            if ($field->getX() + $field->getWidth() > $width) {
                $errors[] = "字段 {$fieldId} 超出模板右边界";
            }

            if ($field->getY() + $field->getHeight() > $height) {
                $errors[] = "字段 {$fieldId} 超出模板下边界";
            }
            
            // 获取字段配置数据进行额外验证
            $fieldData = $field->toArray();
            
            // 验证字体属性
            if (isset($fieldData['font_size']) && (float)$fieldData['font_size'] <= 0) {
                $errors[] = "字段 {$fieldId} 的字体大小无效";
            }
            
            if (isset($fieldData['font_weight']) && !in_array($fieldData['font_weight'], ['normal', 'bold', 'italic'])) {
                $errors[] = "字段 {$fieldId} 的字体粗细设置无效";
            }
            
            if (isset($fieldData['align']) && !in_array($fieldData['align'], ['left', 'center', 'right'])) {
                $errors[] = "字段 {$fieldId} 的对齐方式设置无效";
            }
            
            // 验证条形码特定属性
            if (isset($fieldData['type']) && $fieldData['type'] === 'barcode') {
                if (isset($fieldData['barcode_module_width']) && (float)$fieldData['barcode_module_width'] <= 0) {
                    $errors[] = "字段 {$fieldId} 的条形码模块宽度无效";
                }
                
                if (isset($fieldData['barcode_height']) && (float)$fieldData['barcode_height'] <= 0) {
                    $errors[] = "字段 {$fieldId} 的条形码高度无效";
                }
                
                $supportedBarcodeTypes = ['code128', 'code39', 'ean13', 'ean8', 'upca', 'itf14'];
                if (isset($fieldData['barcode_type']) && !in_array($fieldData['barcode_type'], $supportedBarcodeTypes)) {
                    $errors[] = "字段 {$fieldId} 的条形码类型无效";
                }
            }
            
            // 验证二维码特定属性
            if (isset($fieldData['type']) && $fieldData['type'] === 'qrcode') {
                if (isset($fieldData['qrcode_size']) && (int)$fieldData['qrcode_size'] <= 0) {
                    $errors[] = "字段 {$fieldId} 的二维码大小无效";
                }
                
                $supportedECLevels = ['L', 'M', 'Q', 'H'];
                if (isset($fieldData['qrcode_error_correction']) && !in_array($fieldData['qrcode_error_correction'], $supportedECLevels)) {
                    $errors[] = "字段 {$fieldId} 的二维码纠错级别无效";
                }
            }
            
            // 验证颜色格式（十六进制或特殊值）
            $colorFields = ['text_color', 'label_color', 'background_color', 'border_color'];
            foreach ($colorFields as $colorField) {
                if (isset($fieldData[$colorField])) {
                    $colorValue = $fieldData[$colorField];
                    // 接受十六进制颜色值或特殊值（如transparent）
                    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $colorValue) && $colorValue !== 'transparent') {
                        $errors[] = "字段 {$fieldId} 的 {$colorField} 颜色格式无效，应为 #RRGGBB 格式或特殊值（如transparent）";
                    }
                }
            }
            
            // 验证边框宽度
            if (isset($fieldData['border_width']) && (float)$fieldData['border_width'] < 0) {
                $errors[] = "字段 {$fieldId} 的边框宽度不能为负数";
            }
            
            // 验证标签位置
            if (isset($fieldData['label_position']) && !in_array($fieldData['label_position'], ['top', 'bottom', 'left', 'right'])) {
                $errors[] = "字段 {$fieldId} 的标签位置无效";
            }
            
            // 验证内边距
            if (isset($fieldData['padding']) && is_array($fieldData['padding'])) {
                foreach ($fieldData['padding'] as $paddingType => $paddingValue) {
                    if (in_array($paddingType, ['top', 'bottom', 'left', 'right']) && (float)$paddingValue < 0) {
                        $errors[] = "字段 {$fieldId} 的 {$paddingType} 内边距不能为负数";
                    }
                }
            }
            
            // 验证字段坐标和尺寸的有效性
            if ((float)$field->getX() < 0 || (float)$field->getY() < 0) {
                $errors[] = "字段 {$fieldId} 的坐标值不能为负数";
            }
            
            if ((float)$field->getWidth() <= 0 || (float)$field->getHeight() <= 0) {
                $errors[] = "字段 {$fieldId} 的尺寸值必须大于0";
            }
            
            // 验证多语言字段的标签配置
            if ($field instanceof MultilingualField && isset($fieldData['labels'])) {
                if (!is_array($fieldData['labels'])) {
                    $errors[] = "字段 {$fieldId} 的多语言标签必须是数组格式";
                } else {
                    // 验证每个语言标签的格式
                    foreach ($fieldData['labels'] as $lang => $label) {
                        if (!is_string($label) || empty($label)) {
                            $errors[] = "字段 {$fieldId} 的 {$lang} 语言标签必须是非空字符串";
                        }
                    }
                }
            }
        }

        // 验证必需字段是否存在
        $requiredFields = ['sender', 'recipient', 'order_no'];
        foreach ($requiredFields as $requiredField) {
            if (!$template->getField($requiredField)) {
                $errors[] = "缺少必需字段: {$requiredField}";
            }
        }
        
        // 验证模板基本信息
        if (empty($template->getId())) {
            $errors[] = "模板缺少ID";
        }
        
        if (empty($template->getName())) {
            $errors[] = "模板缺少名称";
        }
        
        if (!in_array($template->getCourier(), $this->supportedCouriers)) {
            $errors[] = "模板使用了不支持的快递公司";
        }

        return $errors;
    }

    /**
     * 导出模板为JSON
     *
     * @param string $templateId 模板ID
     * @return string|null
     */
    public function exportTemplate(string $templateId): ?string
    {
        $template = $this->getTemplate($templateId);
        
        if (!$template) {
            return null;
        }

        $data = $template->toArray();
        $data['exported_at'] = date('Y-m-d H:i:s');
        
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * 导入模板
     *
     * @param string $jsonData JSON数据
     * @return BaseTemplate|null
     */
    public function importTemplate(string $jsonData): ?BaseTemplate
    {
        $data = json_decode($jsonData, true);
        
        if (!$data || !isset($data['id'])) {
            return null;
        }

        // 检查模板是否已存在
        if ($this->getTemplate($data['id'])) {
            throw new \InvalidArgumentException("模板 {$data['id']} 已存在");
        }

        // 创建并保存模板
        $template = $this->createTemplate($data);
        $this->saveTemplate($template);

        return $template;
    }

    /**
     * 获取支持的面单尺寸
     *
     * @return array
     */
    public function getSupportedSizes(): array
    {
        return $this->supportedSizes;
    }

    /**
     * 获取支持的快递公司
     *
     * @return array
     */
    public function getSupportedCouriers(): array
    {
        return $this->supportedCouriers;
    }
}