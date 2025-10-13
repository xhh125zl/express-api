<?php

namespace Kode\ExpressApi\Tests\Label;

use Kode\ExpressApi\Label\Field;
use PHPUnit\Framework\TestCase;

/**
 * Field类测试
 */
class FieldTest extends TestCase
{
    /**
     * 测试字段初始化
     */
    public function testFieldInitialization()
    {
        $field = new Field('sender_name');

        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('sender_name', $field->getId());
    }

    /**
     * 测试字段配置
     */
    public function testFieldConfiguration()
    {
        $config = [
            'label' => '发件人姓名',
            'x' => 10.5,
            'y' => 20.3,
            'width' => 50.0,
            'height' => 5.0,
            'font_size' => 12,
            'align' => 'left',
            'font_family' => 'Arial',
            'bold' => true,
            'italic' => false,
        ];

        $field = new Field('sender_name', $config);

        $this->assertEquals('发件人姓名', $field->getLabel());
        $this->assertEquals(10.5, $field->getX());
        $this->assertEquals(20.3, $field->getY());
        $this->assertEquals(50.0, $field->getWidth());
        $this->assertEquals(5.0, $field->getHeight());
        $this->assertEquals(12, $field->getFontSize());
        $this->assertEquals('left', $field->getAlign());
        $this->assertEquals('Arial', $field->getFontFamily());
        $this->assertTrue($field->isBold());
        $this->assertFalse($field->isItalic());
    }

    /**
     * 测试字段值设置
     */
    public function testFieldValue()
    {
        $field = new Field('sender_name');
        $field->setValue('张三');

        $this->assertEquals('张三', $field->getValue());
    }

    /**
     * 测试字段转换为数组
     */
    public function testFieldToArray()
    {
        $config = [
            'label' => '发件人姓名',
            'x' => 10.5,
            'y' => 20.3,
            'width' => 50.0,
            'height' => 5.0,
            'font_size' => 12,
            'align' => 'left',
            'font_family' => 'Arial',
            'bold' => true,
            'italic' => false,
            'value' => '张三'
        ];

        $field = new Field('sender_name', $config);
        $array = $field->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('sender_name', $array['id']);
        $this->assertEquals('发件人姓名', $array['label']);
        $this->assertEquals(10.5, $array['x']);
        $this->assertEquals(20.3, $array['y']);
        $this->assertEquals(50.0, $array['width']);
        $this->assertEquals(5.0, $array['height']);
        $this->assertEquals(12, $array['font_size']);
        $this->assertEquals('left', $array['align']);
        $this->assertEquals('Arial', $array['font_family']);
        $this->assertTrue($array['bold']);
        $this->assertFalse($array['italic']);
        $this->assertEquals('张三', $array['value']);
    }

    /**
     * 测试从数组创建字段
     */
    public function testFieldFromArray()
    {
        $data = [
            'label' => '发件人姓名',
            'x' => 10.5,
            'y' => 20.3,
            'width' => 50.0,
            'height' => 5.0,
            'font_size' => 12,
            'align' => 'left',
        ];

        $field = Field::fromArray('sender_name', $data);

        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('sender_name', $field->getId());
        $this->assertEquals('发件人姓名', $field->getLabel());
        $this->assertEquals(10.5, $field->getX());
        $this->assertEquals(20.3, $field->getY());
        $this->assertEquals(50.0, $field->getWidth());
        $this->assertEquals(5.0, $field->getHeight());
        $this->assertEquals(12, $field->getFontSize());
        $this->assertEquals('left', $field->getAlign());
    }
}
