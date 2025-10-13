<?php

namespace Kode\ExpressApi\Tests\Label;

use Kode\ExpressApi\Label\LayoutManager;
use Kode\ExpressApi\Label\Template;
use PHPUnit\Framework\TestCase;

/**
 * 面单布局管理器测试类
 */
class LayoutManagerTest extends TestCase
{
    /**
     * @var string 测试模板路径
     */
    protected $testTemplatePath;

    /**
     * 设置测试环境
     */
    protected function setUp(): void
    {
        $this->testTemplatePath = __DIR__ . '/../../tmp/templates';

        // 确保测试目录存在
        if (!is_dir($this->testTemplatePath)) {
            mkdir($this->testTemplatePath, 0755, true);
        }
    }

    /**
     * 清理测试环境
     */
    protected function tearDown(): void
    {
        // 删除测试模板文件
        $files = glob($this->testTemplatePath . '/*.json');
        foreach ($files as $file) {
            unlink($file);
        }

        // 删除测试目录
        if (is_dir($this->testTemplatePath)) {
            rmdir($this->testTemplatePath);
        }
    }

    /**
     * 测试布局管理器初始化
     */
    public function testLayoutManagerInitialization()
    {
        $layoutManager = new LayoutManager($this->testTemplatePath);

        $this->assertInstanceOf(LayoutManager::class, $layoutManager);
    }

    /**
     * 测试创建模板
     */
    public function testCreateTemplate()
    {
        $layoutManager = new LayoutManager($this->testTemplatePath);

        $config = [
            'id' => 'test_template',
            'name' => '测试模板',
            'courier' => 'EMS',
            'size' => ['width' => 100, 'height' => 150],
        ];

        $template = $layoutManager->createTemplate($config);

        $this->assertInstanceOf(Template::class, $template);
        $this->assertEquals('test_template', $template->getId());
        $this->assertEquals('测试模板', $template->getName());
    }

    /**
     * 测试保存和获取模板
     */
    public function testSaveAndGetTemplate()
    {
        $layoutManager = new LayoutManager($this->testTemplatePath);

        $template = $layoutManager->createTemplate([
            'id' => 'save_test',
            'name' => '保存测试模板',
            'courier' => 'EMS',
        ]);

        $template->addField('sender_name', [
            'label' => '发件人姓名',
            'x' => 10,
            'y' => 20,
            'width' => 50,
            'height' => 5,
        ]);

        // 保存模板
        $result = $layoutManager->saveTemplate($template);
        $this->assertTrue($result);

        // 获取模板
        $retrievedTemplate = $layoutManager->getTemplate('save_test');
        $this->assertInstanceOf(Template::class, $retrievedTemplate);
        $this->assertEquals('save_test', $retrievedTemplate->getId());
        $this->assertEquals('保存测试模板', $retrievedTemplate->getName());
        $this->assertEquals('EMS', $retrievedTemplate->getCourier());

        $field = $retrievedTemplate->getField('sender_name');
        $this->assertNotNull($field);
        $this->assertEquals('发件人姓名', $field->getLabel());
        $this->assertEquals(10, $field->getX());
        $this->assertEquals(20, $field->getY());
    }

    /**
     * 测试获取不存在的模板
     */
    public function testGetNonexistentTemplate()
    {
        $layoutManager = new LayoutManager($this->testTemplatePath);

        $template = $layoutManager->getTemplate('nonexistent');
        $this->assertNull($template);
    }

    /**
     * 测试删除模板
     */
    public function testDeleteTemplate()
    {
        $layoutManager = new LayoutManager($this->testTemplatePath);

        // 先创建并保存一个模板
        $template = $layoutManager->createTemplate([
            'id' => 'delete_test',
            'name' => '删除测试模板',
        ]);

        $layoutManager->saveTemplate($template);

        // 确保模板存在
        $this->assertNotNull($layoutManager->getTemplate('delete_test'));

        // 删除模板
        $result = $layoutManager->deleteTemplate('delete_test');
        $this->assertTrue($result);

        // 确保模板已被删除
        $this->assertNull($layoutManager->getTemplate('delete_test'));
    }

    /**
     * 测试删除不存在的模板
     */
    public function testDeleteNonexistentTemplate()
    {
        $layoutManager = new LayoutManager($this->testTemplatePath);

        $result = $layoutManager->deleteTemplate('nonexistent');
        $this->assertFalse($result);
    }

    /**
     * 测试列出所有模板
     */
    public function testListTemplates()
    {
        $layoutManager = new LayoutManager($this->testTemplatePath);

        // 创建两个模板
        $template1 = $layoutManager->createTemplate([
            'id' => 'list_test_1',
            'name' => '列表测试模板1',
        ]);

        $template2 = $layoutManager->createTemplate([
            'id' => 'list_test_2',
            'name' => '列表测试模板2',
        ]);

        $layoutManager->saveTemplate($template1);
        $layoutManager->saveTemplate($template2);

        // 获取模板列表
        $templates = $layoutManager->listTemplates();

        $this->assertCount(2, $templates);

        $ids = array_map(function ($template) {
            return $template->getId();
        }, $templates);

        $this->assertContains('list_test_1', $ids);
        $this->assertContains('list_test_2', $ids);
    }
}
