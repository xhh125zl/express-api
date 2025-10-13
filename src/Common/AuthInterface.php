<?php

namespace Kode\ExpressApi\Common;

/**
 * 通用认证接口
 */
interface AuthInterface
{
    /**
     * 获取访问令牌
     *
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * 清除当前令牌
     *
     * @return void
     */
    public function clearToken(): void;
}
