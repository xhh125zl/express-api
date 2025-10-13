# 使用说明

## 安装

使用Composer安装：

```bash
composer require kode/express-api
```

## 配置

### 方式一：使用数组配置

```php
<?php

require_once 'vendor/autoload.php';

use Kode\ExpressApi\ExpressApiClient;
use Kode\ExpressApi\EMS\Config;

$config = [
    'app_key' => 'your_app_key',
    'app_secret' => 'your_app_secret',
    'access_token' => 'your_access_token', // 可选，如果不提供则自动获取
];

// 使用通用客户端工厂创建EMS客户端
$client = ExpressApiClient::create('ems', $config);
```

### 方式二：使用Config对象

```php
<?php

require_once 'vendor/autoload.php';

use Kode\ExpressApi\ExpressApiClient;
use Kode\ExpressApi\EMS\Config;

$config = new Config([
    'app_key' => 'your_app_key',
    'app_secret' => 'your_app_secret',
    'access_token' => 'your_access_token', // 可选，如果不提供则自动获取
]);

// 使用通用客户端工厂创建EMS客户端
$client = ExpressApiClient::create('ems', $config);
```

## API接口

### 发货通知

```php
$result = $client->sendShipment([
    'order_id' => '123456',
    'sender' => [
        'name' => '张三',
        'phone' => '13800138000',
        'address' => '北京市朝阳区xxx街道'
    ],
    'receiver' => [
        'name' => '李四',
        'phone' => '13900139000',
        'address' => '上海市浦东新区xxx街道'
    ]
]);

print_r($result);
```

### 取件通知

```php
$result = $client->pickupNotice([
    'order_id' => '123456',
    'pickup_time' => '2023-10-15 14:30:00',
    'pickup_address' => '北京市朝阳区xxx街道'
]);

print_r($result);
```

### 查询订单

```php
$result = $client->queryOrder('123456');
print_r($result);
```

### 取消订单

```php
$result = $client->cancelOrder('123456');
print_r($result);
```

### 查询轨迹

```php
$result = $client->queryTracking('EMS123456789CN');
print_r($result);
```

### 拦截件

```php
$result = $client->intercept('123456', [
    'reason' => '客户要求拦截'
]);
print_r($result);
```

### 改件信息

```php
$result = $client->modify('123456', [
    'receiver' => [
        'name' => '王五',
        'phone' => '13700137000',
        'address' => '广州市天河区xxx街道'
    ]
]);
print_r($result);
```

### 面单打印

```php
$result = $client->printLabel('123456', [
    'printer' => 'default_printer',
    'copies' => 1
]);
print_r($result);
```

## 错误处理

所有API调用都可能抛出`ExpressApiException`异常：

```php
use Kode\ExpressApi\Common\Exception\ExpressApiException;

try {
    $result = $client->queryOrder('123456');
    print_r($result);
} catch (ExpressApiException $e) {
    echo "API调用失败: " . $e->getMessage() . "\n";
    echo "错误码: " . $e->getCode() . "\n";
    echo "错误详情: ";
    print_r($e->getDetails());
}
```

## 配置选项

| 选项 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| app_key | string | '' | 应用Key |
| app_secret | string | '' | 应用密钥 |
| sandbox | bool | false | 是否使用沙箱环境 |
| timeout | int | 30 | 请求超时时间（秒） |
| version | string | 'v1' | API版本 |