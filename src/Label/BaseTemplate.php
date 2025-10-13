<?php

namespace Kode\ExpressApi\Label;

/**
 * 面单模板基类
 */
abstract class BaseTemplate
{
    /**
     * @var string 模板ID
     */
    protected $id;

    /**
     * @var string 模板名称
     */
    protected $name;

    /**
     * @var string 快递公司
     */
    protected $courier;

    /**
     * @var array 面单尺寸
     */
    protected $size = ['width' => 100, 'height' => 150];

    /**
     * @var string 面单尺寸单位
     */
    protected $unit = 'mm';

    /**
     * @var string 面单纸张类型
     */
    protected $paperType = 'custom';

    /**
     * @var array 字段定义
     */
    protected $fields = [];

    /**
     * @var array 父模板字段（用于继承）
     */
    protected $parentFields = [];

    /**
     * 构造函数
     *
     * @param array $config 模板配置
     */
    public function __construct(array $config = [])
    {
        $this->id = $config['id'] ?? uniqid('template_');
        $this->name = $config['name'] ?? '未命名模板';
        $this->courier = $config['courier'] ?? 'unknown';
        $this->size = $config['size'] ?? ['width' => 100, 'height' => 150];
        $this->unit = $config['unit'] ?? 'mm';
        $this->paperType = $config['paper_type'] ?? 'custom';

        // 处理父模板字段
        if (isset($config['parent_fields'])) {
            $this->parentFields = $config['parent_fields'];
        }

        // 合并父模板字段和当前模板字段
        $allFields = array_merge($this->parentFields, $config['fields'] ?? []);

        if (!empty($allFields)) {
            foreach ($allFields as $name => $fieldConfig) {
                $this->addField($name, $fieldConfig);
            }
        }
    }

    /**
     * 获取模板ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * 设置模板ID
     *
     * @param string $id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 获取模板名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 设置模板名称
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 获取快递公司
     *
     * @return string
     */
    public function getCourier(): string
    {
        return $this->courier;
    }

    /**
     * 设置快递公司
     *
     * @param string $courier
     * @return self
     */
    public function setCourier(string $courier): self
    {
        $this->courier = $courier;
        return $this;
    }

    /**
     * 获取面单尺寸
     *
     * @return array
     */
    public function getSize(): array
    {
        return $this->size;
    }

    /**
     * 设置面单尺寸
     *
     * @param array $size
     * @return self
     */
    public function setSize(array $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * 获取尺寸单位
     *
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * 设置尺寸单位
     *
     * @param string $unit
     * @return self
     */
    public function setUnit(string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * 获取纸张类型
     *
     * @return string
     */
    public function getPaperType(): string
    {
        return $this->paperType;
    }

    /**
     * 设置纸张类型
     *
     * @param string $paperType
     * @return self
     */
    public function setPaperType(string $paperType): self
    {
        $this->paperType = $paperType;
        return $this;
    }

    /**
     * 获取字段定义
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * 添加字段
     *
     * @param string $name 字段名称
     * @param array|Field $field 字段配置或Field对象
     * @return self
     */
    public function addField(string $name, $field): self
    {
        if ($field instanceof Field) {
            $this->fields[$name] = $field;
        } else {
            $defaultConfig = [
                'label' => $name,
                'x' => 0,
                'y' => 0,
                'width' => 10,
                'height' => 5,
                'font_size' => 10,
                'align' => 'left',
            ];

            $config = array_merge($defaultConfig, $field);
            $this->fields[$name] = new Field($name, $config);
        }
        return $this;
    }

    /**
     * 获取字段配置
     *
     * @param string $name 字段名称
     * @return array|Field|null
     */
    public function getField(string $name)
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * 删除字段
     *
     * @param string $name 字段名称
     * @return self
     */
    public function removeField(string $name): self
    {
        unset($this->fields[$name]);
        return $this;
    }

    /**
     * 设置父模板字段
     *
     * @param array $parentFields
     * @return self
     */
    public function setParentFields(array $parentFields): self
    {
        $this->parentFields = $parentFields;
        return $this;
    }

    /**
     * 获取父模板字段
     *
     * @return array
     */
    public function getParentFields(): array
    {
        return $this->parentFields;
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        $fields = [];
        foreach ($this->fields as $name => $field) {
            if ($field instanceof Field) {
                $fields[$name] = $field->toArray();
            } else {
                $fields[$name] = $field;
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'courier' => $this->courier,
            'size' => $this->size,
            'unit' => $this->unit,
            'paper_type' => $this->paperType,
            'fields' => $fields,
        ];
    }
}
