# 开发规范

## 代码规范

本项目遵循PSR-12代码规范。

### 代码检查

使用PHP_CodeSniffer检查代码规范：

```bash
composer cs-check
```

### 自动修复

自动修复代码规范问题：

```bash
composer cs-fix
```

## 测试

### 运行测试

运行所有测试用例：

```bash
composer test
```

### 测试覆盖率

生成测试覆盖率报告：

```bash
composer test -- --coverage-html coverage-report
```

### 测试要求

1. **测试覆盖率**: 所有代码都必须有相应的测试用例，测试覆盖率应达到90%以上
2. **测试类型**: 包含单元测试和集成测试
3. **测试环境**: 测试应在沙箱环境中进行，避免影响生产数据

## Git工作流

### 分支策略

- `main`: 主分支，包含稳定版本
- `develop`: 开发分支，包含最新功能
- `feature/*`: 功能分支，用于开发新功能
- `hotfix/*`: 热修复分支，用于紧急修复

### 提交信息规范

提交信息应遵循以下格式：

```
<type>(<scope>): <subject>

<body>

<footer>
```

**type类型**:
- `feat`: 新功能
- `fix`: 修复bug
- `docs`: 文档更新
- `style`: 代码格式调整
- `refactor`: 代码重构
- `test`: 测试相关
- `chore`: 构建过程或辅助工具的变动

**示例**:
```
feat(ems-client): 添加面单布局功能

- 实现面单布局配置生成
- 支持自定义面单模板

Closes #123
```

## 版本管理

遵循语义化版本规范（SemVer）：

- 主版本号(MAJOR): 不兼容的API修改
- 次版本号(MINOR): 向后兼容的功能性新增
- 修订号(PATCH): 向后兼容的问题修正

## 依赖管理

### 添加新依赖

仅在必要时添加新依赖，并确保：

1. 依赖是稳定版本
2. 依赖有良好的维护记录
3. 依赖符合项目许可要求

```bash
composer require vendor/package:^1.0
```

### 更新依赖

定期更新依赖以获取安全修复和新功能：

```bash
composer update
```

## 文档编写

### API文档

所有公共API都应有详细的文档注释：

```php
/**
 * 发货通知
 *
 * @param array $data 发货数据
 *   - order_id string 订单ID
 *   - sender array 发货人信息
 *   - receiver array 收货人信息
 * @return array API响应结果
 * @throws ExpressApiException API调用失败时抛出异常
 */
public function sendShipment(array $data): array
{
    // 实现代码
}
```

### Markdown文档

Markdown文档应遵循以下规范：

1. 使用中文标点符号
2. 代码块应指定语言类型
3. 列表项应保持格式一致
4. 链接应使用相对路径

## 持续集成

### 代码质量检查

每次提交都应通过以下检查：

1. PHP语法检查
2. PSR-12代码规范检查
3. 单元测试通过
4. 测试覆盖率达标

### 自动化部署

- 主分支的更新会自动触发生产环境部署
- Develop分支的更新会自动部署到测试环境

## 性能优化

### HTTP请求优化

1. 复用HTTP连接
2. 合理设置超时时间
3. 使用连接池管理

### 缓存策略

1. 访问令牌自动缓存和刷新
2. API响应结果可选择性缓存
3. 避免重复请求相同数据

## 安全规范

### 数据安全

1. 敏感信息不应记录到日志中
2. 所有外部输入都应进行验证和过滤
3. 使用HTTPS传输所有数据

### 认证安全

1. 访问令牌应安全存储
2. 定期刷新访问令牌
3. 实现令牌失效处理机制

### 输入验证

```php
// 验证必填参数
if (empty($data['order_id'])) {
    throw new ExpressApiException('订单ID不能为空');
}

// 验证数据格式
if (!preg_match('/^1[3-9]\d{9}$/', $data['sender']['phone'])) {
    throw new ExpressApiException('发货人手机号格式不正确');
}
```

## 添加新快递公司指南

要添加对新快递公司的支持，请按照以下步骤操作：

### 1. 创建配置类

在`src/[CourierName]`目录下创建`Config.php`文件，继承`AbstractConfig`类：

```php
use Kode\ExpressApi\Common\AbstractConfig;

class Config extends AbstractConfig
{
    protected $defaults = [
        'app_key' => '',
        'app_secret' => '',
        'sandbox' => false,
        'timeout' => 30,
        'version' => 'v1',
    ];

    public function getAppKey(): string { ... }
    public function getAppSecret(): string { ... }
    public function getBaseUrl(): string { ... }
    public function getSandboxUrl(): string { ... }
}
```

### 2. 创建认证类（如果需要）

在`src/[CourierName]`目录下创建`Auth.php`文件，实现`AuthInterface`接口：

```php
use Kode\ExpressApi\Common\AuthInterface;

class Auth implements AuthInterface
{
    public function getAccessToken(): string { ... }
    public function clearToken(): void { ... }
}
```

### 3. 创建客户端类

在`src/[CourierName]`目录下创建`Client.php`文件，实现`ClientInterface`接口：

```php
use Kode\ExpressApi\Common\ClientInterface;

class Client implements ClientInterface
{
    public function sendShipment(array $data): array { ... }
    public function pickupNotice(array $data): array { ... }
    // ... 实现其他接口方法
}
```

### 4. 更新工厂类

在`ExpressApiClient`类中添加新的case：

```php
case 'courier_code':
    return new CourierName\Client($config);
```

### 5. 添加测试

为新添加的类创建相应的测试类，确保测试覆盖率。

### 6. 更新文档

更新README.md和相关文档，添加对新快递公司的支持说明。