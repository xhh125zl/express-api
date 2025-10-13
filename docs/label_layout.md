# 面单布局功能设计

## 功能概述

面单布局功能用于生成和管理快递面单的打印布局配置，支持不同快递公司的面单模板。

## 设计思路

### 面单模板结构

```php
$template = [
    'id' => 'ems_standard_100x150',
    'name' => 'EMS标准面单(100mm×150mm)',
    'size' => [
        'width' => 100,   // 宽度(mm)
        'height' => 150,  // 高度(mm)
    ],
    'fields' => [
        'sender_name' => [
            'x' => 10,      // X坐标(mm)
            'y' => 20,      // Y坐标(mm)
            'width' => 50,  // 宽度(mm)
            'height' => 5,  // 高度(mm)
            'font_size' => 12,
            'align' => 'left',
        ],
        'receiver_name' => [
            'x' => 10,
            'y' => 40,
            'width' => 50,
            'height' => 5,
            'font_size' => 12,
            'align' => 'left',
        ],
        // 更多字段...
    ],
];
```

### 布局管理器

```php
use Kode\ExpressApi\Label\LayoutManager;

$layoutManager = new LayoutManager();

// 创建面单模板
$template = $layoutManager->createTemplate([
    'name' => 'EMS标准面单',
    'size' => ['width' => 100, 'height' => 150],
]);

// 添加字段
$template->addField('sender_name', [
    'x' => 10,
    'y' => 20,
    'width' => 50,
    'height' => 5,
]);

// 保存模板
$layoutManager->saveTemplate($template);
```

### 获取和使用字段值

```php
// 读取模板
$template = $layoutManager->getTemplate('ems_standard');

// 获取所有字段
$fields = $template->getFields();

// 为字段设置实际值
if (isset($fields['sender_name'])) {
    $fields['sender_name']->setValue('张三');
}

if (isset($fields['receiver_name'])) {
    $fields['receiver_name']->setValue('李四');
}

// 获取字段值
$senderName = $fields['sender_name']->getValue();
$receiverName = $fields['receiver_name']->getValue();

// 转换为数组格式（用于API传输或存储）
$fieldArray = $fields['sender_name']->toArray();
```

## 核心类设计

### LayoutManager

负责面单布局的管理，包括创建、读取、更新、删除模板。

### Template

表示一个面单模板，包含尺寸信息和字段定义。

### Field

表示面单上的一个字段，包含位置、大小、样式等信息。

## Field类

Field类提供了更丰富的字段定义功能，支持以下属性：

- `id`: 字段ID
- `label`: 字段标签
- `x`, `y`: 字段在面单上的坐标位置(mm)
- `width`, `height`: 字段的宽度和高度(mm)
- `font_size`: 字体大小
- `align`: 对齐方式(left, center, right)
- `font_family`: 字体名称
- `bold`: 是否加粗
- `italic`: 是否斜体
- `value`: 字段值

## 使用示例

### 创建面单布局

```php
use Kode\ExpressApi\Label\LayoutManager;
use Kode\ExpressApi\Label\Field;

$layoutManager = new LayoutManager();

// 为EMS创建标准面单布局
$template = $layoutManager->createTemplate([
    'id' => 'ems_standard',
    'name' => 'EMS标准面单',
    'courier' => 'EMS',
    'size' => ['width' => 100, 'height' => 150],
]);

// 方式1: 使用数组配置添加字段
$template->addField('sender_name', [
    'label' => '发件人姓名',
    'x' => 10,
    'y' => 20,
    'width' => 50,
    'height' => 5,
    'font_size' => 12,
]);

$template->addField('sender_address', [
    'label' => '发件人地址',
    'x' => 10,
    'y' => 25,
    'width' => 80,
    'height' => 10,
    'font_size' => 10,
]);

// 方式2: 使用Field对象添加字段
$receiverNameField = new Field('receiver_name', [
    'label' => '收件人姓名',
    'x' => 10,
    'y' => 45,
    'width' => 50,
    'height' => 5,
    'font_size' => 12,
    'bold' => true,  // 加粗显示
]);
$template->addField('receiver_name', $receiverNameField);

// 保存模板
$layoutManager->saveTemplate($template);
```

### 使用面单布局打印

```php
use Kode\ExpressApi\EMS\Client;
use Kode\ExpressApi\Label\LayoutManager;

// 初始化EMS客户端
$client = new Client($config);

// 获取面单布局
$layoutManager = new LayoutManager();
$template = $layoutManager->getTemplate('ems_standard');

// 生成面单数据
$orderData = [
    'order_id' => '123456',
    'sender' => [
        'name' => '张三',
        'address' => '北京市朝阳区xxx街道',
    ],
    'receiver' => [
        'name' => '李四',
        'address' => '上海市浦东新区xxx街道',
    ],
];

// 打印面单
$result = $client->printLabel('123456', [
    'template' => $template->getId(),
    'data' => $orderData,
]);
```

## 配置文件格式

面单模板可以保存为JSON格式的配置文件：

```json
{
    "id": "ems_standard",
    "name": "EMS标准面单",
    "courier": "EMS",
    "size": {
        "width": 100,
        "height": 150
    },
    "fields": {
        "sender_name": {
            "label": "发件人姓名",
            "x": 10,
            "y": 20,
            "width": 50,
            "height": 5,
            "font_size": 12,
            "align": "left"
        },
        "sender_address": {
            "label": "发件人地址",
            "x": 10,
            "y": 25,
            "width": 80,
            "height": 10,
            "font_size": 10,
            "align": "left"
        }
    }
}
```

## 扩展性考虑

### 支持多种面单尺寸

面单模板现在支持多种尺寸和单位设置：

```php
$template = $layoutManager->createTemplate([
    'id' => 'ems_a4',
    'name' => 'EMS A4面单',
    'courier' => 'EMS',
    'size' => ['width' => 210, 'height' => 297], // A4尺寸(mm)
    'unit' => 'mm',           // 支持mm, cm, inch等单位
    'paper_type' => 'A4',     // 纸张类型: A4, A5, custom等
]);
```

### 支持条码和二维码

字段现在支持多种类型，包括条码和二维码：

```php
// 条码字段
$template->addField('barcode', [
    'label' => '条形码',
    'type' => 'barcode',
    'barcode_type' => 'code128',  // 支持code128, code39, ean13等
    'x' => 10,
    'y' => 50,
    'width' => 60,
    'height' => 15,
]);

// 二维码字段
$template->addField('qrcode', [
    'label' => '二维码',
    'type' => 'qrcode',
    'x' => 10,
    'y' => 70,
    'width' => 25,
    'height' => 25,
]);
```

### 支持图片插入

字段支持图片类型，可以插入Logo等图片元素：

```php
$template->addField('logo', [
    'label' => '公司Logo',
    'type' => 'image',
    'image_path' => '/path/to/logo.png',
    'x' => 150,
    'y' => 10,
    'width' => 30,
    'height' => 15,
]);
```

### 模板继承

通过BaseTemplate类实现模板继承和复用：

```php
// 创建父模板
$parentTemplate = new Template([
    'id' => 'base_template',
    'name' => '基础模板',
    'courier' => 'EMS',
    'size' => ['width' => 100, 'height' => 150],
    'fields' => [
        'sender_name' => [
            'label' => '发件人姓名',
            'x' => 10,
            'y' => 10,
            'width' => 50,
            'height' => 5,
        ]
    ]
]);

// 创建子模板继承父模板
$childTemplate = new Template([
    'id' => 'child_template',
    'name' => '子模板',
    'courier' => 'EMS',
    'size' => ['width' => 100, 'height' => 150],
    'parent_fields' => $parentTemplate->getFields(),  // 继承父模板字段
    'fields' => [
        'order_id' => [
            'label' => '订单号',
            'x' => 10,
            'y' => 20,
            'width' => 50,
            'height' => 5,
        ]
    ]
]);
```

### 多语言支持

通过MultilingualField类支持不同语言的字段标签：

```php
use Kode\ExpressApi\Label\MultilingualField;

$field = new MultilingualField('product_name', [
    'labels' => [
        'zh' => '产品名称',
        'en' => 'Product Name',
        'ja' => '商品名',
        'ko' => '제품명'
    ],
    'x' => 10,
    'y' => 30,
    'width' => 50,
    'height' => 5,
]);

// 设置当前语言
$field->setLanguage('en');  // 显示英文标签
$field->setLanguage('ja');  // 显示日文标签
```

## 后续开发计划

1. ~~实现基础的LayoutManager类~~ (已完成)
2. ~~实现Template和Field类~~ (已完成)
3. ~~添加面单预览功能~~ (已完成)
4. 集成第三方打印库
5. ~~开发Web界面用于面单布局设计~~ (已完成)

### 面单预览功能

通过LabelPreview类实现面单预览功能：

```php
use Kode\ExpressApi\Label\LabelPreview;

// 创建预览对象
$preview = new LabelPreview($template);

// 设置字段值
$preview->setValues([
    'sender_name' => '张三',
    'receiver_name' => '李四',
    'order_id' => '123456',
    'barcode' => '123456789012'
]);

// 生成HTML预览
$htmlPreview = $preview->generateHtmlPreview();

// 生成JSON数据用于前端渲染
$jsonData = $preview->generateJsonData();
```

### Web界面用于面单布局设计

提供了一个基于HTML/JavaScript的面单布局设计工具，位于 `examples/label_designer.html`。该工具具有以下功能：

1. **模板设置**：配置面单模板的基本信息和尺寸
2. **字段管理**：添加、编辑和删除面单字段
3. **预览设置**：为字段设置预览值
4. **实时预览**：实时查看面单布局效果
5. **数据导出**：生成JSON格式的模板数据

### 集成第三方打印库

计划集成以下第三方打印库以增强打印功能：

1. **TCPDF**：用于生成PDF格式的面单
2. **GD库**：用于生成图像格式的面单
3. **条码生成库**：如php-barcode-generator，用于生成各种条码和二维码