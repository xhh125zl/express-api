# 配置说明

## 获取API密钥

要使用邮政EMS API，您需要先在EMS开放平台注册并获取API密钥：

1. 访问[EMS开放平台](https://api.ems.com.cn/)
2. 注册开发者账号并完成企业认证
3. 在API控制台选择需要的服务接口
4. 获取API密钥（AppKey和AppSecret）

## 环境配置

### 生产环境

在生产环境中使用真实的EMS API端点：

```php
$config = new \Kode\ExpressApi\EMS\Config([
    'app_key' => 'your_production_app_key',
    'app_secret' => 'your_production_app_secret',
    'sandbox' => false, // 默认值，可省略
]);
```

### 沙箱环境

在开发和测试阶段，建议使用沙箱环境：

```php
$config = new \Kode\ExpressApi\EMS\Config([
    'app_key' => 'your_sandbox_app_key',
    'app_secret' => 'your_sandbox_app_secret',
    'sandbox' => true,
]);
```

## 配置参数详解

### app_key

- **类型**: string
- **必填**: 是
- **说明**: 应用Key，从EMS开放平台获取

### app_secret

- **类型**: string
- **必填**: 是
- **说明**: 应用密钥，从EMS开放平台获取

### sandbox

- **类型**: boolean
- **默认值**: false
- **说明**: 是否使用沙箱环境
  - `true`: 使用沙箱环境，用于开发测试
  - `false`: 使用生产环境，用于正式业务

### timeout

- **类型**: integer
- **默认值**: 30
- **说明**: HTTP请求超时时间（秒）

### version

- **类型**: string
- **默认值**: 'v1'
- **说明**: API版本号

## 认证机制

EMS API使用OAuth 2.0客户端凭证模式进行认证：

1. 使用`app_key`和`app_secret`获取访问令牌
2. 在后续API请求中使用该令牌进行认证

```php
// 认证过程由SDK自动处理
$client = new \Kode\ExpressApi\EMS\Client($config);

// 首次API调用时会自动获取并缓存访问令牌
$result = $client->queryOrder('123456');
```

## 网络要求

### 生产环境端点

- API地址: `https://api.ems.com.cn`
- 端口: 443 (HTTPS)

### 沙箱环境端点

- API地址: `https://api-sandbox.ems.com.cn`
- 端口: 443 (HTTPS)

确保您的服务器能够访问这些地址。

## 安全建议

1. **保护密钥信息**:
   - 不要在代码中硬编码密钥
   - 使用环境变量或配置文件管理密钥
   - 定期更换密钥

2. **使用HTTPS**:
   - 所有API调用都应通过HTTPS进行
   - 验证SSL证书的有效性

3. **限制访问权限**:
   - 仅申请必要的API权限
   - 定期审查权限使用情况

4. **错误处理**:
   - 妥善处理API错误响应
   - 不要将敏感信息暴露给客户端

```php
// 推荐的密钥管理方式
$config = new \Kode\ExpressApi\EMS\Config([
    'app_key' => getenv('EMS_APP_KEY'),
    'app_secret' => getenv('EMS_APP_SECRET'),
    'sandbox' => getenv('EMS_SANDBOX') === 'true',
]);
```