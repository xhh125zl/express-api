<?php

namespace Kode\ExpressApi\Label;

/**
 * 支持多语言的字段类
 */
class MultilingualField extends Field
{
    /**
     * @var array 多语言标签
     */
    protected $labels = [];

    /**
     * @var string 当前语言
     */
    protected $currentLanguage = 'zh';

    /**
     * 构造函数
     *
     * @param string $id 字段ID
     * @param array $config 字段配置
     */
    public function __construct(string $id, array $config = [])
    {
        // 处理多语言标签
        if (isset($config['labels'])) {
            $this->labels = $config['labels'];
            // 如果没有设置默认标签，使用中文标签
            if (!isset($config['label']) && isset($this->labels['zh'])) {
                $config['label'] = $this->labels['zh'];
            }
            unset($config['labels']);
        }

        parent::__construct($id, $config);
    }

    /**
     * 设置当前语言
     *
     * @param string $language 语言代码
     * @return self
     */
    public function setLanguage(string $language): self
    {
        $this->currentLanguage = $language;
        // 更新当前标签
        if (isset($this->labels[$language])) {
            $this->label = $this->labels[$language];
        }
        return $this;
    }

    /**
     * 获取当前语言
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->currentLanguage;
    }

    /**
     * 添加语言标签
     *
     * @param string $language 语言代码
     * @param string $label 标签
     * @return self
     */
    public function addLabel(string $language, string $label): self
    {
        $this->labels[$language] = $label;
        return $this;
    }

    /**
     * 获取指定语言的标签
     *
     * @param string $language 语言代码
     * @return string|null
     */
    public function getLabelByLanguage(string $language): ?string
    {
        return $this->labels[$language] ?? null;
    }

    /**
     * 获取所有语言标签
     *
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * 设置多语言标签
     *
     * @param array $labels 语言标签数组
     * @return self
     */
    public function setLabels(array $labels): self
    {
        $this->labels = $labels;
        return $this;
    }

    /**
     * 获取当前标签
     *
     * @return string
     */
    public function getLabel(): string
    {
        // 如果当前语言有标签，返回该标签
        if (isset($this->labels[$this->currentLanguage])) {
            return $this->labels[$this->currentLanguage];
        }

        // 否则返回默认标签
        return parent::getLabel();
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['labels'] = $this->labels;
        return $data;
    }

    /**
     * 从数组创建字段实例
     *
     * @param string $id 字段ID
     * @param array $data 字段数据
     * @return static
     */
    public static function fromArray(string $id, array $data): self
    {
        $field = new static($id, $data);
        return $field;
    }
}
