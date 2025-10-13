<?php

namespace Kode\ExpressApi\Label;

/**
 * 面单字段类
 */
class Field
{
    /**
     * @var string 字段ID
     */
    protected $id;

    /**
     * @var string 字段标签
     */
    protected $label;

    /**
     * @var float X坐标(mm)
     */
    protected $x;

    /**
     * @var float Y坐标(mm)
     */
    protected $y;

    /**
     * @var float 宽度(mm)
     */
    protected $width;

    /**
     * @var float 高度(mm)
     */
    protected $height;

    /**
     * @var int 字体大小
     */
    protected $fontSize = 12;

    /**
     * @var string 对齐方式
     */
    protected $align = 'left';

    /**
     * @var string 字体名称
     */
    protected $fontFamily = 'Arial';

    /**
     * @var bool 是否加粗
     */
    protected $bold = false;

    /**
     * @var bool 是否斜体
     */
    protected $italic = false;

    /**
     * @var string 字段值
     */
    protected $value;

    /**
     * @var string 字段类型 (text, barcode, qrcode, image)
     */
    protected $type = 'text';

    /**
     * @var string 条码类型 (code128, code39, ean13, etc.)
     */
    protected $barcodeType = 'code128';

    /**
     * @var string 图片路径
     */
    protected $imagePath;

    /**
     * 构造函数
     *
     * @param string $id 字段ID
     * @param array $config 字段配置
     */
    public function __construct(string $id, array $config = [])
    {
        $this->id = $id;
        $this->configure($config);
    }

    /**
     * 配置字段属性
     *
     * @param array $config 字段配置
     * @return self
     */
    public function configure(array $config): self
    {
        // 处理特殊键名
        $keyMap = [
            'barcode_type' => 'barcodeType',
            'image_path' => 'imagePath',
        ];

        foreach ($config as $key => $value) {
            // 处理键名映射
            if (isset($keyMap[$key])) {
                $key = $keyMap[$key];
            }

            $setter = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
        return $this;
    }

    /**
     * 获取字段ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * 设置字段标签
     *
     * @param string $label 字段标签
     * @return self
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * 获取字段标签
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label ?? $this->id;
    }

    /**
     * 设置X坐标
     *
     * @param float $x X坐标(mm)
     * @return self
     */
    public function setX(float $x): self
    {
        $this->x = $x;
        return $this;
    }

    /**
     * 获取X坐标
     *
     * @return float
     */
    public function getX(): float
    {
        return $this->x;
    }

    /**
     * 设置Y坐标
     *
     * @param float $y Y坐标(mm)
     * @return self
     */
    public function setY(float $y): self
    {
        $this->y = $y;
        return $this;
    }

    /**
     * 获取Y坐标
     *
     * @return float
     */
    public function getY(): float
    {
        return $this->y;
    }

    /**
     * 设置宽度
     *
     * @param float $width 宽度(mm)
     * @return self
     */
    public function setWidth(float $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * 获取宽度
     *
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * 设置高度
     *
     * @param float $height 高度(mm)
     * @return self
     */
    public function setHeight(float $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * 获取高度
     *
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * 设置字体大小
     *
     * @param int $fontSize 字体大小
     * @return self
     */
    public function setFontSize(int $fontSize): self
    {
        $this->fontSize = $fontSize;
        return $this;
    }

    /**
     * 获取字体大小
     *
     * @return int
     */
    public function getFontSize(): int
    {
        return $this->fontSize;
    }

    /**
     * 设置对齐方式
     *
     * @param string $align 对齐方式 (left, center, right)
     * @return self
     */
    public function setAlign(string $align): self
    {
        $this->align = $align;
        return $this;
    }

    /**
     * 获取对齐方式
     *
     * @return string
     */
    public function getAlign(): string
    {
        return $this->align;
    }

    /**
     * 设置字体名称
     *
     * @param string $fontFamily 字体名称
     * @return self
     */
    public function setFontFamily(string $fontFamily): self
    {
        $this->fontFamily = $fontFamily;
        return $this;
    }

    /**
     * 获取字体名称
     *
     * @return string
     */
    public function getFontFamily(): string
    {
        return $this->fontFamily;
    }

    /**
     * 设置是否加粗
     *
     * @param bool $bold 是否加粗
     * @return self
     */
    public function setBold(bool $bold): self
    {
        $this->bold = $bold;
        return $this;
    }

    /**
     * 获取是否加粗
     *
     * @return bool
     */
    public function isBold(): bool
    {
        return $this->bold;
    }

    /**
     * 设置是否斜体
     *
     * @param bool $italic 是否斜体
     * @return self
     */
    public function setItalic(bool $italic): self
    {
        $this->italic = $italic;
        return $this;
    }

    /**
     * 获取是否斜体
     *
     * @return bool
     */
    public function isItalic(): bool
    {
        return $this->italic;
    }

    /**
     * 设置字段值
     *
     * @param string|null $value 字段值
     * @return self
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * 获取字段值
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'label' => $this->getLabel(),
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
            'font_size' => $this->fontSize,
            'align' => $this->align,
            'font_family' => $this->fontFamily,
            'bold' => $this->bold,
            'italic' => $this->italic,
            'value' => $this->value,
            'type' => $this->type,
        ];

        // 只有在需要时才添加特定类型的属性
        if ($this->type === 'barcode' || $this->type === 'qrcode') {
            $data['barcode_type'] = $this->barcodeType;
        }

        if ($this->type === 'image') {
            $data['image_path'] = $this->imagePath;
        }

        return $data;
    }

    /**
     * 从数组创建字段
     *
     * @param string $id 字段ID
     * @param array $data 字段数据
     * @return self
     */
    public static function fromArray(string $id, array $data): self
    {
        return new self($id, $data);
    }

    /**
     * 设置字段类型
     *
     * @param string $type 字段类型
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * 获取字段类型
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * 设置条码类型
     *
     * @param string $barcodeType 条码类型
     * @return self
     */
    public function setBarcodeType(string $barcodeType): self
    {
        $this->barcodeType = $barcodeType;
        return $this;
    }

    /**
     * 获取条码类型
     *
     * @return string
     */
    public function getBarcodeType(): string
    {
        return $this->barcodeType;
    }

    /**
     * 设置图片路径
     *
     * @param string $imagePath 图片路径
     * @return self
     */
    public function setImagePath(string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    /**
     * 获取图片路径
     *
     * @return string|null
     */
    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }
}
