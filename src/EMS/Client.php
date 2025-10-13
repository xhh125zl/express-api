<?php

namespace Kode\ExpressApi\EMS;

use Kode\ExpressApi\Common\ClientInterface;
use Kode\ExpressApi\Common\Exception\ExpressApiException;

/**
 * EMS API 客户端
 */
class Client implements ClientInterface
{
    /**
     * @var Config 配置信息
     */
    protected $config;

    /**
     * @var Auth 认证对象
     */
    protected $auth;

    /**
     * 构造函数
     *
     * @param array|Config $config 配置信息
     */
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = new Config($config);
        } elseif ($config instanceof Config) {
            $this->config = $config;
        } else {
            throw new \InvalidArgumentException('配置信息必须是数组或Config对象');
        }

        $this->auth = new Auth($this->config);
    }



    /**
     * 发送HTTP请求
     *
     * @param string $method HTTP方法
     * @param string $uri 请求URI
     * @param array $data 请求数据
     * @param array $headers 请求头
     * @return array
     * @throws ExpressApiException
     */
    protected function request(string $method, string $uri, array $data = [], array $headers = []): array
    {
        $url = $this->config->getBaseUrl() . $uri;

        // 添加认证头
        $headers['Authorization'] = 'Bearer ' . $this->auth->getAccessToken();
        $headers['Content-Type'] = 'application/json';

        // 使用通用HTTP客户端发送请求
        $response = \Kode\ExpressApi\Common\HttpClient::request(
            $method,
            $url,
            $data,
            $headers,
            $this->config->getTimeout()
        );

        // 使用通用响应处理器处理响应
        return \Kode\ExpressApi\Common\ResponseHandler::handle($response, 'ems');
    }



    /**
     * 发货通知
     *
     * @param array $data 发货数据
     * @return array
     * @throws ExpressApiException
     */
    public function sendShipment(array $data): array
    {
        return $this->request('POST', '/shipment', $data);
    }

    /**
     * 取件通知
     *
     * @param array $data 取件数据
     * @return array
     * @throws ExpressApiException
     */
    public function pickupNotice(array $data): array
    {
        return $this->request('POST', '/pickup', $data);
    }

    /**
     * 查询订单
     *
     * @param string $orderId 订单ID
     * @return array
     * @throws ExpressApiException
     */
    public function queryOrder(string $orderId): array
    {
        return $this->request('GET', '/order/' . $orderId);
    }

    /**
     * 取消订单
     *
     * @param string $orderId 订单ID
     * @return array
     * @throws ExpressApiException
     */
    public function cancelOrder(string $orderId): array
    {
        return $this->request('DELETE', '/order/' . $orderId);
    }

    /**
     * 查询轨迹
     *
     * @param string $trackingNumber 运单号
     * @return array
     * @throws ExpressApiException
     */
    public function queryTracking(string $trackingNumber): array
    {
        return $this->request('GET', '/tracking/' . $trackingNumber);
    }

    /**
     * 拦截件
     *
     * @param string $orderId 订单ID
     * @param array $data 拦截数据
     * @return array
     * @throws ExpressApiException
     */
    public function intercept(string $orderId, array $data = []): array
    {
        return $this->request('POST', '/order/' . $orderId . '/intercept', $data);
    }

    /**
     * 改件信息
     *
     * @param string $orderId 订单ID
     * @param array $data 修改数据
     * @return array
     * @throws ExpressApiException
     */
    public function modify(string $orderId, array $data): array
    {
        return $this->request('PUT', '/order/' . $orderId, $data);
    }

    /**
     * 面单打印
     *
     * @param string $orderId 订单ID
     * @param array $data 打印数据
     * @return array
     * @throws ExpressApiException
     */
    public function printLabel(string $orderId, array $data = []): array
    {
        return $this->request('POST', '/order/' . $orderId . '/print', $data);
    }
}
