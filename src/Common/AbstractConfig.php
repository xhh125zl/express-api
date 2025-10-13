<?php

namespace Kode\ExpressApi\Common;

/**
 * 抽象配置类
 */
abstract class AbstractConfig
{
    /**
     * @var array 配置参数
     */
    protected $config = [];

    /**
     * 默认配置
     *
     * @var array
     */
    protected $defaults = [
        'sandbox' => false,
        'timeout' => 30,
        'version' => 'v1',
    ];

    /**
     * 构造函数
     *
     * @param array $config 配置参数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->defaults, $config);
    }

    /**
     * 获取配置值
     *
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * 设置配置值
     *
     * @param string $key 配置键
     * @param mixed $value 配置值
     * @return self
     */
    public function set(string $key, $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * 获取所有配置
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * 获取应用Key
     *
     * @return string
     */
    abstract public function getAppKey(): string;

    /**
     * 获取应用密钥
     *
     * @return string
     */
    abstract public function getAppSecret(): string;

    /**
     * 获取基础URL
     *
     * @return string
     */
    abstract public function getBaseUrl(): string;

    /**
     * 获取沙箱环境URL
     *
     * @return string
     */
    abstract public function getSandboxUrl(): string;

    /**
     * 获取超时时间
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return (int) $this->get('timeout', 30);
    }

    /**
     * 获取API版本
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->get('version', 'v1');
    }

    /**
     * 是否使用沙箱环境
     *
     * @return bool
     */
    public function isSandbox(): bool
    {
        return (bool) $this->get('sandbox', false);
    }
}
