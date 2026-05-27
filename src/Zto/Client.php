<?php

namespace Kode\ExpressApi\Zto;

use Kode\ExpressApi\Common\ClientInterface;
use Kode\ExpressApi\Common\Exception\ExpressApiException;
use Kode\ExpressApi\Common\HttpClient;
use Kode\ExpressApi\Common\ResponseHandler;

/**
 * 中通快递开放平台 客户端
 *
 * 基于中通官方开放平台（open.zto.com）API实现，
 * 使用 x-companyid + x-datadigest 签名认证方式。
 *
 * 支持的API接口：
 * - 下单/创建运单 (createOrder)
 * - 取消订单 (cancelOrder)
 * - 物流轨迹查询 (trackQuery)
 * - 电子面单打印 (printOrder)
 */
class Client implements ClientInterface
{
    /**
     * @var Config 配置信息
     */
    protected $config;

    /**
     * @var Auth 认证对象（签名生成）
     */
    protected $auth;

    /**
     * 构造函数
     *
     * @param array|Config $config 配置信息，需包含 app_key 和 app_secret
     * @throws \InvalidArgumentException
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
     * 获取配置对象
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 获取认证对象
     *
     * @return Auth
     */
    public function getAuth(): Auth
    {
        return $this->auth;
    }

    /**
     * 发送HTTP请求到中通开放平台（核心方法）
     *
     * 自动处理签名认证头组装和响应解析。
     *
     * @param string $apiPath API路径（如 zto.open.createOrder）
     * @param array $data 业务数据（将被JSON编码为请求体）
     * @return array 解析后的响应数据
     * @throws ExpressApiException
     */
    protected function request(string $apiPath, array $data = []): array
    {
        $url = $this->config->getBaseUrl() . '/' . $apiPath;
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);

        // 通过Auth生成带签名的认证头（签名基于原始JSON字符串）
        $headers = $this->auth->buildAuthHeaders($body);

        try {
            // 发送POST请求，使用 rawBody 确保发送的是签名的原始JSON
            $response = HttpClient::request(
                'POST',
                $url,
                [],          // data 为空，使用 rawBody 传递
                $headers,
                $this->config->getTimeout(),
                $body         // 原始 JSON 字符串作为请求体
            );

            return ResponseHandler::handle($response, 'zto');
        } catch (\Exception $e) {
            if ($e instanceof ExpressApiException) {
                throw $e;
            }
            throw new ExpressApiException('中通快递API请求失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 发送原始请求（允许自定义完整URL和数据体）
     *
     * 用于某些特殊接口需要自定义路径或数据格式的情况。
     *
     * @param string $url 完整的API URL
     * @param string $body 原始请求体字符串（JSON）
     * @return array 解析后的响应数据
     * @throws ExpressApiException
     */
    protected function rawRequest(string $url, string $body): array
    {
        $headers = $this->auth->buildAuthHeaders($body);

        try {
            $response = HttpClient::request(
                'POST',
                $url,
                [],
                $headers,
                $this->config->getTimeout(),
                $body
            );
            return ResponseHandler::handle($response, 'zto');
        } catch (\Exception $e) {
            if ($e instanceof ExpressApiException) {
                throw $e;
            }
            throw new ExpressApiException('中通快递API请求失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 创建订单 / 下单（发货通知）
     *
     * 接口：zto.open.createOrder
     *
     * @param array $data 订单数据，包含发件人、收件人、物品信息等
     * @return array 返回创建结果（包含运单号等）
     * @throws ExpressApiException
     */
    public function sendShipment(array $data): array
    {
        $this->validateShipmentData($data);
        return $this->request('zto.open.createOrder', $data);
    }

    /**
     * 批量下单
     *
     * 接口：zto.open.batchCreateOrder
     *
     * @param array $shipments 订单数据数组
     * @return array 批量创建结果
     * @throws ExpressApiException
     */
    public function batchSendShipment(array $shipments): array
    {
        foreach ($shipments as $shipment) {
            $this->validateShipmentData($shipment);
        }
        return $this->request('zto.open.batchCreateOrder', ['orders' => $shipments]);
    }

    /**
     * 取件通知（预约寄件）
     *
     * @param array $data 取件数据
     * @return array
     * @throws ExpressApiException
     */
    public function pickupNotice(array $data): array
    {
        if (empty($data)) {
            throw new ExpressApiException('取件数据不能为空');
        }
        return $this->request('zto.open.pickup', $data);
    }

    /**
     * 查询订单详情
     *
     * @param string $orderId 商家订单号
     * @return array
     * @throws ExpressApiException
     */
    public function queryOrder(string $orderId): array
    {
        if (empty($orderId)) {
            throw new ExpressApiException('商家订单号不能为空');
        }
        return $this->request('zto.open.orderQuery', ['orderId' => $orderId]);
    }

    /**
     * 批量查询订单
     *
     * @param array $orderIds 商家订单号数组
     * @return array
     * @throws ExpressApiException
     */
    public function batchQueryOrders(array $orderIds): array
    {
        if (empty($orderIds)) {
            throw new ExpressApiException('订单ID列表不能为空');
        }
        return $this->request('zto.open.batchOrderQuery', ['orderIds' => $orderIds]);
    }

    /**
     * 取消订单
     *
     * 接口：zto.open.cancelOrder
     *
     * @param string $orderId 商家订单号
     * @param string $reason 取消原因（可选）
     * @return array
     * @throws ExpressApiException
     */
    public function cancelOrder(string $orderId, string $reason = ''): array
    {
        if (empty($orderId)) {
            throw new ExpressApiException('商家订单号不能为空');
        }
        $data = ['orderId' => $orderId];
        if (!empty($reason)) {
            $data['reason'] = $reason;
        }
        return $this->request('zto.open.cancelOrder', $data);
    }

    /**
     * 物流轨迹查询
     *
     * 接口：zto.merchant.waybill.track.query
     *
     * @param string $trackingNumber 中通运单号
     * @param string $language 语言（zh-CN, en-US），默认 zh-CN
     * @return array 包含轨迹节点列表
     * @throws ExpressApiException
     */
    public function queryTracking(string $trackingNumber, string $language = 'zh-CN'): array
    {
        if (empty($trackingNumber)) {
            throw new ExpressApiException('运单号不能为空');
        }
        return $this->request('zto.merchant.waybill.track.query', [
            'billCode' => $trackingNumber,
            'language' => $language,
        ]);
    }

    /**
     * 拦截订单
     *
     * @param string $orderId 商家订单号
     * @param string $reason 拦截原因
     * @return array
     * @throws ExpressApiException
     */
    public function interceptOrder(string $orderId, string $reason): array
    {
        return $this->intercept($orderId, ['reason' => $reason]);
    }

    /**
     * 修改订单信息
     *
     * @param string $orderId 商家订单号
     * @param array $updateData 更新数据
     * @return array
     * @throws ExpressApiException
     */
    public function updateOrderInfo(string $orderId, array $updateData): array
    {
        return $this->modify($orderId, $updateData);
    }

    /**
     * 拦截件
     *
     * @param string $orderId 商家订单号
     * @param array $data 拦截数据（必须包含 reason）
     * @return array
     * @throws ExpressApiException
     */
    public function intercept(string $orderId, array $data = []): array
    {
        if (empty($orderId)) {
            throw new ExpressApiException('商家订单号不能为空');
        }
        if (empty($data['reason'])) {
            throw new ExpressApiException('拦截原因不能为空');
        }
        return $this->request('zto.open.intercept', array_merge(['orderId' => $orderId], $data));
    }

    /**
     * 改件信息
     *
     * @param string $orderId 商家订单号
     * @param array $data 修改数据
     * @return array
     * @throws ExpressApiException
     */
    public function modify(string $orderId, array $data): array
    {
        if (empty($orderId)) {
            throw new ExpressApiException('商家订单号不能为空');
        }
        if (empty($data)) {
            throw new ExpressApiException('更新数据不能为空');
        }
        return $this->request('zto.open.modify', array_merge(['orderId' => $orderId], $data));
    }

    /**
     * 电子面单打印
     *
     * 接口：zto.merchant.waybill.print
     *
     * @param string $orderId 商家订单号
     * @param array $data 打印参数（如模板ID、打印机类型等）
     * @return array 面单数据（HTML/PDF等格式）
     * @throws ExpressApiException
     */
    public function printLabel(string $orderId, array $data = []): array
    {
        if (empty($orderId)) {
            throw new ExpressApiException('商家订单号不能为空');
        }
        $printData = array_merge(['orderId' => $orderId], $data);
        return $this->request('zto.merchant.waybill.print', $printData);
    }

    /**
     * 验证下单必填字段
     *
     * @param array $data 下单数据
     * @throws ExpressApiException
     */
    protected function validateShipmentData(array $data): void
    {
        $requiredFields = [
            'orderId',      // 商家订单号
            'sender',       // 发件人信息
            'receiver',     // 收件人信息
        ];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new ExpressApiException("下单数据缺少必填字段: {$field}");
            }
        }
    }
}
