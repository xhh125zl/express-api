<?php

namespace Kode\ExpressApi\Label;

/**
 * 面单布局管理器
 */
class LayoutManager
{
    /**
     * @var string 模板存储路径
     */
    protected $templatePath;

    /**
     * 构造函数
     *
     * @param string $templatePath 模板存储路径
     */
    public function __construct(string $templatePath = null)
    {
        $this->templatePath = $templatePath ?? __DIR__ . '/../../templates';

        // 确保模板目录存在
        if (!is_dir($this->templatePath)) {
            mkdir($this->templatePath, 0755, true);
        }
    }

    /**
     * 创建面单模板
     *
     * @param array $config 模板配置
     * @return Template
     */
    public function createTemplate(array $config): Template
    {
        return new Template($config);
    }

    /**
     * 保存模板
     *
     * @param Template $template 模板对象
     * @return bool
     */
    public function saveTemplate(Template $template): bool
    {
        $filename = $this->templatePath . '/' . $template->getId() . '.json';
        $data = $template->toArray();

        return file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * 获取模板
     *
     * @param string $templateId 模板ID
     * @return Template|null
     */
    public function getTemplate(string $templateId): ?Template
    {
        $filename = $this->templatePath . '/' . $templateId . '.json';

        if (!file_exists($filename)) {
            return null;
        }

        $data = json_decode(file_get_contents($filename), true);

        return new Template($data);
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
     * @return Template[]
     */
    public function listTemplates(): array
    {
        $templates = [];

        $files = glob($this->templatePath . '/*.json');

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $templates[] = new Template($data);
            }
        }

        return $templates;
    }
}
