# Kode Express API

一个通用的快递API集成包，支持多种快递公司（目前支持EMS，后续将支持韵达、申通、中通、菜鸟、顺丰、京东快递等）。

## 特性

- 支持PHP 7.4+版本
- 符合PSR-12代码规范
- 完整的PHPUnit测试用例（PHPUnit 12）
- 支持多语言面单设计
- 灵活的面单布局管理
- 通用的调用方法，可轻松集成到任何PHP框架中

## 安装

使用Composer安装：

```bash
composer require kode/express-api
```

## 快速开始

### 基本使用

```php
<?php

require_once 'vendor/autoload.php';

use Kode\ExpressApi\Label\Template;
use Kode\ExpressApi\Label\Field;
use Kode\ExpressApi\Label\MultilingualField;
use Kode\ExpressApi\Label\LabelPreview;
use Kode\ExpressApi\Label\LayoutManager;

// 创建模板配置
$config = [
    'id' => 'ems_template_001',
    'name' => 'EMS标准面单模板',
    'courier' => 'EMS',
    'size' => ['width' => 100, 'height' => 150]
];

// 创建模板
$template = new Template($config);

// 添加字段
$senderField = new MultilingualField([
    'id' => 'sender',
    'label' => '发件人',
    'x' => 5,
    'y' => 5,
    'width' => 40,
    'height' => 10,
    'font_size' => 8,
    'align' => 'left',
    'font_family' => 'Arial'
]);
$senderField->addLabel('zh', '发件人');
$senderField->addLabel('en', 'Sender');

$template->addField($senderField);

// 生成预览
$preview = new LabelPreview($template);
$html = $preview->generateHtmlPreview([
    'sender' => "张三\n北京市朝阳区xxx街道xx号"
]);

echo $html;
```

### 面单布局管理

```php
<?php

require_once 'vendor/autoload.php';

use Kode\ExpressApi\Label\LayoutManager;

// 创建布局管理器
$layoutManager = new LayoutManager(__DIR__ . '/templates');

// 保存模板
$layoutManager->saveTemplate($template);

// 获取模板
$template = $layoutManager->getTemplate('ems_template_001');

// 列出所有模板
$templates = $layoutManager->listTemplates();

// 删除模板
$layoutManager->deleteTemplate('ems_template_001');
```

## 示例

查看 `examples/` 目录中的示例文件：

- `basic_usage.php` - 基本使用示例
- `label_designer.php` - 面单设计器示例
- `label_layout.php` - 面单布局管理示例
- `multi_courier.php` - 多快递公司支持示例

## 测试

运行测试套件：

```bash
composer test
```

## 代码规范检查

检查代码是否符合PSR-12规范：

```bash
composer cs-check
```

自动修复代码规范问题：

```bash
composer cs-fix
```

## API文档

### Template 类

用于创建和管理面单模板。

#### 构造函数
```php
new Template(array $config)
```

#### 方法
- `getId(): string` - 获取模板ID
- `getName(): string` - 获取模板名称
- `getCourier(): string` - 获取快递公司
- `addField(Field $field): void` - 添加字段
- `getFields(): array` - 获取所有字段
- `getField(string $fieldId): ?Field` - 获取指定字段
- `removeField(string $fieldId): void` - 移除字段
- `toArray(): array` - 转换为数组

### Field 类

表示面单上的一个字段。

#### 构造函数
```php
new Field(array $config)
```

#### 方法
- `getId(): string` - 获取字段ID
- `getLabel(): string` - 获取字段标签
- `getX(): float` - 获取X坐标
- `getY(): float` - 获取Y坐标
- `getWidth(): float` - 获取宽度
- `getHeight(): float` - 获取高度
- `getFontSize(): int` - 获取字体大小
- `getAlign(): string` - 获取对齐方式
- `getFontFamily(): string` - 获取字体族
- `isBold(): bool` - 是否粗体
- `isItalic(): bool` - 是否斜体
- `getType(): string` - 获取字段类型
- `setValue(mixed $value): void` - 设置值
- `getValue(): mixed` - 获取值
- `toArray(): array` - 转换为数组

### MultilingualField 类

支持多语言的字段，继承自Field类。

#### 方法
- `addLabel(string $language, string $label): void` - 添加语言标签
- `getLabelByLanguage(string $language): ?string` - 根据语言获取标签
- `getLabels(): array` - 获取所有语言标签

### LabelPreview 类

用于生成面单预览。

#### 构造函数
```php
new LabelPreview(Template $template)
```

#### 方法
- `generateHtmlPreview(array $values): string` - 生成HTML预览
- `generateJsonData(array $values): array` - 生成JSON数据

### LayoutManager 类

面单布局管理器。

#### 构造函数
```php
new LayoutManager(string $templatePath = null)
```

#### 方法
- `createTemplate(array $config): Template` - 创建模板
- `saveTemplate(Template $template): bool` - 保存模板
- `getTemplate(string $templateId): ?Template` - 获取模板
- `deleteTemplate(string $templateId): bool` - 删除模板
- `listTemplates(): array` - 列出所有模板

## 开发

### 运行测试

```bash
composer test
```

### 代码规范

使用PHP_CodeSniffer检查代码规范：

```bash
composer cs-check
```

自动修复代码规范问题：

```bash
composer cs-fix
```

## 许可证

MIT