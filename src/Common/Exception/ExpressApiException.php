<?php

namespace Kode\ExpressApi\Common\Exception;

/**
 * 快递API通用异常类
 */
class ExpressApiException extends \Exception
{
    /**
     * @var mixed 错误详情
     */
    protected $details;

    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param int $code 错误代码
     * @param \Throwable|null $previous 前一个异常
     * @param mixed $details 错误详情
     */
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null, $details = null)
    {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }

    /**
     * 获取错误详情
     *
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }
}
