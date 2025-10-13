<?php

namespace Kode\ExpressApi\Tests\Common\Exception;

use Kode\ExpressApi\Common\Exception\ExpressApiException;
use PHPUnit\Framework\TestCase;

/**
 * 通用异常类测试
 */
class ExpressApiExceptionTest extends TestCase
{
    /**
     * 测试异常初始化
     */
    public function testExceptionInitialization()
    {
        $exception = new ExpressApiException('Test exception message', 1001, null, ['detail' => 'test']);

        $this->assertInstanceOf(ExpressApiException::class, $exception);
        $this->assertEquals('Test exception message', $exception->getMessage());
        $this->assertEquals(1001, $exception->getCode());
        $this->assertEquals(['detail' => 'test'], $exception->getDetails());
    }

    /**
     * 测试默认异常值
     */
    public function testDefaultExceptionValues()
    {
        $exception = new ExpressApiException();

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        $this->assertNull($exception->getDetails());
    }
}
