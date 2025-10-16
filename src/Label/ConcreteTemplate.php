<?php

namespace Kode\ExpressApi\Label;

/**
 * 具体面单模板类
 * 
 * 继承自BaseTemplate，实现具体的面单模板功能
 */
class ConcreteTemplate extends BaseTemplate
{
    /**
     * 构造函数
     *
     * @param array $config 模板配置
     */
    public function __construct(array $config = [])
    {
        // 设置默认配置
        $defaults = [
            'id' => 'template_' . uniqid(),
            'name' => '默认模板',
            'courier' => 'ems',
            'size' => ['width' => 100, 'height' => 150],
            'unit' => 'mm',
            'paper_type' => 'custom'
        ];
        
        // 合并配置
        $config = array_merge($defaults, $config);
        
        // 设置基本属性
        $this->id = $config['id'];
        $this->name = $config['name'];
        $this->courier = $config['courier'];
        
        // 确保size始终是数组
        if (is_string($config['size'])) {
            // 如果size是字符串，尝试从支持的尺寸中查找
            $supportedSizes = [
                'standard_100x150' => ['width' => 100, 'height' => 150],
                'standard_100x100' => ['width' => 100, 'height' => 100],
                'standard_80x80' => ['width' => 80, 'height' => 80],
                'custom' => ['width' => 0, 'height' => 0]
            ];
            
            $this->size = $supportedSizes[$config['size']] ?? $defaults['size'];
        } else {
            $this->size = $config['size'];
        }
        
        $this->unit = $config['unit'];
        $this->paperType = $config['paper_type'];
        
        // 如果有字段配置，添加字段
        if (isset($config['fields']) && is_array($config['fields'])) {
            foreach ($config['fields'] as $fieldName => $fieldConfig) {
                $this->addField($fieldName, $fieldConfig);
            }
        }
    }
}