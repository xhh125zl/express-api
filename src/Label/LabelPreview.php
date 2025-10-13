<?php

namespace Kode\ExpressApi\Label;

/**
 * 面单预览类
 */
class LabelPreview
{
    /**
     * @var Template 面单模板
     */
    protected $template;

    /**
     * @var array 字段值
     */
    protected $values = [];

    /**
     * 构造函数
     *
     * @param Template $template 面单模板
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * 设置字段值
     *
     * @param string $fieldId 字段ID
     * @param mixed $value 字段值
     * @return self
     */
    public function setValue(string $fieldId, $value): self
    {
        $this->values[$fieldId] = $value;
        return $this;
    }

    /**
     * 批量设置字段值
     *
     * @param array $values 字段值数组
     * @return self
     */
    public function setValues(array $values): self
    {
        $this->values = array_merge($this->values, $values);
        return $this;
    }

    /**
     * 获取字段值
     *
     * @param string $fieldId 字段ID
     * @return mixed|null
     */
    public function getValue(string $fieldId)
    {
        return $this->values[$fieldId] ?? null;
    }

    /**
     * 获取所有字段值
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * 生成HTML预览
     *
     * @return string HTML代码
     */
    public function generateHtmlPreview(): string
    {
        $templateSize = $this->template->getSize();
        $unit = $this->template->getUnit();
        $width = $templateSize['width'];
        $height = $templateSize['height'];

        $html = '<div class="label-preview" style="position: relative; width: ' . $width . $unit .
                '; height: ' . $height . $unit . '; border: 1px solid #ccc; background: white;">';

        // 渲染所有字段
        foreach ($this->template->getFields() as $field) {
            if ($field instanceof Field) {
                $html .= $this->renderField($field);
            }
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * 渲染字段
     *
     * @param Field $field 字段对象
     * @return string HTML代码
     */
    protected function renderField(Field $field): string
    {
        $id = $field->getId();
        $label = $field->getLabel();
        $x = $field->getX();
        $y = $field->getY();
        $width = $field->getWidth();
        $height = $field->getHeight();
        $fontSize = $field->getFontSize();
        $align = $field->getAlign();
        $fontFamily = $field->getFontFamily();
        $bold = $field->isBold();
        $italic = $field->isItalic();
        $value = $this->values[$id] ?? $field->getValue();

        // 根据字段类型渲染不同内容
        switch ($field->getType()) {
            case 'barcode':
                return $this->renderBarcodeField($field, $value);
            case 'qrcode':
                return $this->renderQrCodeField($field, $value);
            case 'image':
                return $this->renderImageField($field, $value);
            default:
                return $this->renderTextField($field, $value);
        }
    }

    /**
     * 渲染文本字段
     *
     * @param Field $field 字段对象
     * @param mixed $value 字段值
     * @return string HTML代码
     */
    protected function renderTextField(Field $field, $value): string
    {
        $x = $field->getX();
        $y = $field->getY();
        $width = $field->getWidth();
        $height = $field->getHeight();
        $fontSize = $field->getFontSize();
        $align = $field->getAlign();
        $fontFamily = $field->getFontFamily();
        $bold = $field->isBold();
        $italic = $field->isItalic();
        $label = $field->getLabel();

        $style = "position: absolute; left: {$x}mm; top: {$y}mm; width: {$width}mm; " .
                 "height: {$height}mm; font-size: {$fontSize}pt;";
        $style .= "font-family: {$fontFamily}; text-align: {$align};";

        if ($bold) {
            $style .= " font-weight: bold;";
        }

        if ($italic) {
            $style .= " font-style: italic;";
        }

        return "<div class=\"label-field\" style=\"{$style}\">" . htmlspecialchars((string)$value) . "</div>";
    }

    /**
     * 渲染条码字段
     *
     * @param Field $field 字段对象
     * @param mixed $value 字段值
     * @return string HTML代码
     */
    protected function renderBarcodeField(Field $field, $value): string
    {
        $x = $field->getX();
        $y = $field->getY();
        $width = $field->getWidth();
        $height = $field->getHeight();

        $style = "position: absolute; left: {$x}mm; top: {$y}mm; width: {$width}mm; " .
                 "height: {$height}mm; background: #000;";

        // 简化的条码表示
        $barcodeHtml = "<div class=\"barcode-field\" style=\"{$style}\">";
        $barcodeHtml .= "<div style=\"height: 100%; display: flex; align-items: center; " .
                        "justify-content: center; color: white; font-size: 8px;\">";
        $barcodeHtml .= "BARCODE: " . htmlspecialchars((string)$value);
        $barcodeHtml .= "</div>";
        $barcodeHtml .= "</div>";

        return $barcodeHtml;
    }

    /**
     * 渲染二维码字段
     *
     * @param Field $field 字段对象
     * @param mixed $value 字段值
     * @return string HTML代码
     */
    protected function renderQrCodeField(Field $field, $value): string
    {
        $x = $field->getX();
        $y = $field->getY();
        $width = $field->getWidth();
        $height = $field->getHeight();

        $style = "position: absolute; left: {$x}mm; top: {$y}mm; width: {$width}mm; " .
                 "height: {$height}mm; background: #000;";

        // 简化的二维码表示
        $qrCodeHtml = "<div class=\"qrcode-field\" style=\"{$style}\">";
        $qrCodeHtml .= "<div style=\"height: 100%; display: flex; align-items: center; " .
                       "justify-content: center; color: white; font-size: 8px;\">";
        $qrCodeHtml .= "QR CODE: " . htmlspecialchars((string)$value);
        $qrCodeHtml .= "</div>";
        $qrCodeHtml .= "</div>";

        return $qrCodeHtml;
    }

    /**
     * 渲染图片字段
     *
     * @param Field $field 字段对象
     * @param mixed $value 字段值
     * @return string HTML代码
     */
    protected function renderImageField(Field $field, $value): string
    {
        $x = $field->getX();
        $y = $field->getY();
        $width = $field->getWidth();
        $height = $field->getHeight();
        $imagePath = $field->getImagePath();

        if (!$imagePath) {
            // 如果没有图片路径，显示占位符
            $style = "position: absolute; left: {$x}mm; top: {$y}mm; width: {$width}mm; " .
                     "height: {$height}mm; border: 1px dashed #999; background: #f0f0f0;";
            return "<div class=\"image-placeholder\" style=\"{$style}\">" .
                   "<div style=\"display: flex; align-items: center; justify-content: center; " .
                   "height: 100%; font-size: 8px; color: #666;\">IMAGE</div></div>";
        }

        $style = "position: absolute; left: {$x}mm; top: {$y}mm; width: {$width}mm; height: {$height}mm;";
        return "<img src=\"" . htmlspecialchars($imagePath) . "\" style=\"{$style}\" alt=\"Image\" />";
    }

    /**
     * 生成JSON数据用于前端渲染
     *
     * @return array
     */
    public function generateJsonData(): array
    {
        $templateData = $this->template->toArray();
        $templateData['values'] = $this->values;

        return $templateData;
    }
}
