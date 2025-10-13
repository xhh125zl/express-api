<?php

namespace Kode\ExpressApi\Tests\Label;

use Kode\ExpressApi\Label\Template;
use PHPUnit\Framework\TestCase;

/**
 * 面单模板测试类
 */
class TemplateTest extends TestCase
{
    /**
     * 测试模板初始化
     */
    public function testTemplateInitialization()
    {
        $config = [
            'id' => 'test_template',
            'name' => '测试模板',
            'courier' => 'EMS',
            'size' => ['width' => 100, 'height' => 150],
        ];

        $template = new Template($config);

        $this->assertInstanceOf(Template::class, $template);
        $this->assertEquals('test_template', $template->getId());
        $this->assertEquals('测试模板', $template->getName());
        $this->assertEquals('EMS', $template->getCourier());
        $this->assertEquals(['width' => 100, 'height' => 150], $template->getSize());
    }

    /**
     * 测试默认模板值
     */
    public function testDefaultTemplateValues()
    {
        $template = new Template();

        $this->assertNotEmpty($template->getId());
        $this->assertEquals('未命名模板', $template->getName());
        $this->assertEquals('unknown', $template->getCourier());
        $this->assertEquals(['width' => 100, 'height' => 150], $template->getSize());
        $this->assertEquals([], $template->getFields());
    }

    /**
     * 测试设置器方法
     */
    public function testSetterMethods()
    {
        $template = new Template();

        $template->setId('new_id')
                 ->setName('新模板')
                 ->setCourier('SF')
                 ->setSize(['width' => 90, 'height' => 130]);

        $this->assertEquals('new_id', $template->getId());
        $this->assertEquals('新模板', $template->getName());
        $this->assertEquals('SF', $template->getCourier());
        $this->assertEquals(['width' => 90, 'height' => 130], $template->getSize());
    }

    /**
     * 测试添加字段（数组配置）
     */
    public function testAddFieldWithArrayConfig()
    {
        $template = new Template();

        $template->addField('sender_name', [
            'label' => '发件人姓名',
            'x' => 10,
            'y' => 20,
            'width' => 50,
            'height' => 5,
            'font_size' => 12,
            'align' => 'left',
        ]);

        $fields = $template->getFields();
        $this->assertCount(1, $fields);
        $this->assertArrayHasKey('sender_name', $fields);

        $field = $template->getField('sender_name');
        $this->assertNotNull($field);
        $this->assertEquals('发件人姓名', $field->getLabel());
        $this->assertEquals(10, $field->getX());
        $this->assertEquals(20, $field->getY());
        $this->assertEquals(50, $field->getWidth());
        $this->assertEquals(5, $field->getHeight());
        $this->assertEquals(12, $field->getFontSize());
        $this->assertEquals('left', $field->getAlign());
    }

    /**
     * 测试字段默认值
     */
    public function testFieldDefaultValues()
    {
        $template = new Template();

        // 只提供字段名称，使用默认配置
        $template->addField('test_field', []);

        $field = $template->getField('test_field');
        $this->assertNotNull($field);
        $this->assertEquals('test_field', $field->getLabel());
        $this->assertEquals(0, $field->getX());
        $this->assertEquals(0, $field->getY());
        $this->assertEquals(10, $field->getWidth());
        $this->assertEquals(5, $field->getHeight());
        $this->assertEquals(10, $field->getFontSize());
        $this->assertEquals('left', $field->getAlign());
    }

    /**
     * 测试添加Field对象
     */
    public function testAddFieldObject()
    {
        $template = new Template();

        $field = new \Kode\ExpressApi\Label\Field('sender_name', [
            'label' => '发件人姓名',
            'x' => 10,
            'y' => 20,
            'width' => 50,
            'height' => 5,
            'font_size' => 12,
            'align' => 'left',
        ]);

        $template->addField('sender_name', $field);

        $fields = $template->getFields();
        $this->assertCount(1, $fields);
        $this->assertArrayHasKey('sender_name', $fields);

        $retrievedField = $template->getField('sender_name');
        $this->assertInstanceOf(\Kode\ExpressApi\Label\Field::class, $retrievedField);
        $this->assertEquals('sender_name', $retrievedField->getId());
        $this->assertEquals('发件人姓名', $retrievedField->getLabel());
        $this->assertEquals(10, $retrievedField->getX());
        $this->assertEquals(20, $retrievedField->getY());
        $this->assertEquals(50, $retrievedField->getWidth());
        $this->assertEquals(5, $retrievedField->getHeight());
        $this->assertEquals(12, $retrievedField->getFontSize());
        $this->assertEquals('left', $retrievedField->getAlign());
    }

    /**
     * 测试获取不存在的字段
     */
    public function testGetNonexistentField()
    {
        $template = new Template();

        $field = $template->getField('nonexistent');
        $this->assertNull($field);
    }

    /**
     * 测试删除字段
     */
    public function testRemoveField()
    {
        $template = new Template();

        $template->addField('field1', [])
                 ->addField('field2', []);

        $this->assertCount(2, $template->getFields());

        $template->removeField('field1');

        $this->assertCount(1, $template->getFields());
        $this->assertArrayHasKey('field2', $template->getFields());
        $this->assertArrayNotHasKey('field1', $template->getFields());
    }

    /**
     * 测试转换为数组
     */
    public function testToArray()
    {
        $config = [
            'id' => 'array_test',
            'name' => '数组测试模板',
            'courier' => 'EMS',
            'size' => ['width' => 100, 'height' => 150],
        ];

        $template = new Template($config);
        $template->addField('sender_name', [
            'label' => '发件人姓名',
            'x' => 10,
            'y' => 20,
        ]);

        $array = $template->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('array_test', $array['id']);
        $this->assertEquals('数组测试模板', $array['name']);
        $this->assertEquals('EMS', $array['courier']);
        $this->assertEquals(['width' => 100, 'height' => 150], $array['size']);
        $this->assertArrayHasKey('sender_name', $array['fields']);
    }
}
