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
 * 使用 x-companyid + x-datadigest(MD5签名) 认证方式。
 *
 * 已验证的API接口（沙箱/生产环境）：
 * - zto.merchant.waybill.track.query  — 物流轨迹查询  [沙箱✅ 生产✅]
 * - zto.open.createOrder             — 创建订单/下单   [沙箱✅ 生产✅]
 * - zto.open.order.print             — 电子面单打印   [沙箱✅ 生产✅]
 * - zto.open.order.update            — 订单信息修改   [沙箱✅ 生产✅]
 * - zto.open.order.create            — 下单(另一版本) [沙箱⚠️ 生产?]
 *
 * 以下接口仅在生产环境可用（沙箱返回404）：
 * - cancelOrder / intercept / modify 等操作类接口
 *
 * @see https://open.zto.com/#/documents?menuId=1
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
     * 发送原始请求（允许自定义完整URL和数据体，公开方法供高级用户使用）
     *
     * 用于某些特殊接口需要自定义路径或数据格式的情况。
     *
     * @param string $url 完整的API URL
     * @param array $data 请求数据数组（将被JSON编码并签名）
     * @return array 解析后的响应数据
     * @throws ExpressApiException
     */
    public function rawRequest(string $url, array $data = []): array
    {
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);
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

    // ============================================================
    // 核心业务接口（已通过沙箱验证）
    // ============================================================

    /**
     * 创建订单 / 下单
     *
     * 接口路径: zto.open.createOrder
     *
     * 请求数据格式:
     *   [
     *     'partnerType'      => '1',          // 合作模式: 1=集团客户, 2=非集团客户
     *     'orderType'        => '1',          // 订单类型
     *     'partnerOrderCode' => 'YOUR_ORDER_ID', // 商家订单号（唯一）
     *     'senderInfo'       => [...],        // 发件人信息
     *     'receiveInfo'      => [...],        // 收件人信息
     *     'cargo'            => '商品描述',
     *     'weight'           => 1.5,           // 重量(kg)
     *     // ... 更多可选字段
     *   ]
     *
     * @param array $data 订单数据
     * @return array 返回创建结果
     * @throws ExpressApiException
     */
    public function sendShipment(array $data): array
    {
        if (empty($data)) {
            throw new ExpressApiException('下单数据不能为空');
        }
        return $this->request('zto.open.createOrder', $data);
    }

    /**
     * 物流轨迹查询
     *
     * 接口路径: zto.merchant.waybill.track.query
     *
     * @param string $billCode 中通运单号（12位数字或字母数字组合）
     * @param string $language 语言（zh-CN, en-US），默认 zh-CN
     * @return array 包含轨迹节点列表
     * @throws ExpressApiException
     */
    public function queryTracking(string $billCode, string $language = 'zh-CN'): array
    {
        if (empty($billCode)) {
            throw new ExpressApiException('运单号不能为空');
        }
        return $this->request('zto.merchant.waybill.track.query', [
            'billCode' => $billCode,
            'language' => $language,
        ]);
    }

    /**
     * 电子面单打印
     *
     * 接口路径: zto.open.order.print
     *
     * @param string $orderId 中通运单号（接口要求 billCode）
     * @param array $data 打印参数（模板ID、打印机类型等可选参数）
     * @return array 面单数据
     * @throws ExpressApiException
     */
    public function printLabel(string $orderId, array $data = []): array
    {
        if (empty($orderId)) {
            throw new ExpressApiException('运单号不能为空');
        }
        $printData = array_merge(['billCode' => $orderId], $data);
        return $this->request('zto.open.order.print', $printData);
    }

    // ============================================================
    // 以下接口在沙箱环境可能不可用（返回404），生产环境可用
    // ============================================================

    /**
     * 取消订单
     *
     * 注意: 此接口在沙箱环境返回404，仅在生产环境可用。
     *
     * @param string $orderId 商家订单号
     * @return array
     * @throws ExpressApiException
     */
    public function cancelOrder(string $orderId): array
    {
        if (empty($orderId)) {
            throw new ExpressApiException('商家订单号不能为空');
        }
        return $this->request('zto.open.cancelOrder', ['partnerOrderCode' => $orderId]);
    }

    /**
     * 拦截订单
     *
     * 注意: 此接口在沙箱环境返回404，仅在生产环境可用。
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
        return $this->request('zto.open.intercept', array_merge(['partnerOrderCode' => $orderId], $data));
    }

    /**
     * 修改订单信息
     *
     * 接口路径: zto.open.order.update（已验证可用）
     *
     * @param string $partnerOrderCode 商家订单号
     * @param array $updateData 更新数据（如 receiveInfo 等）
     * @return array
     * @throws ExpressApiException
     */
    public function updateOrderInfo(string $partnerOrderCode, array $updateData): array
    {
        if (empty($partnerOrderCode)) {
            throw new ExpressApiException('商家订单号不能为空');
        }
        if (empty($updateData)) {
            throw new ExpressApiException('更新数据不能为空');
        }
        return $this->request('zto.open.order.update', array_merge([
            'partnerOrderCode' => $partnerOrderCode,
        ], $updateData));
    }

    // ============================================================
    // 兼容性别名方法
    // ============================================================

    /**
     * 批量下单（调用 sendShipment 循环或批量接口）
     */
    public function batchSendShipment(array $shipments): array
    {
        if (empty($shipments)) {
            throw new ExpressApiException('订单数据不能为空');
        }
        // 沙箱不支持批量接口(zto.open.batchCreateOrder 返回404)
        // 回退为逐个调用
        $results = [];
        foreach ($shipments as $shipment) {
            $results[] = $this->sendShipment($shipment);
        }
        return ['orders' => $results];
    }

    /**
     * 批量查询订单
     */
    public function batchQueryOrders(array $orderIds): array
    {
        if (empty($orderIds)) {
            throw new ExpressApiException('订单ID列表不能为空');
        }
        // 沙箱不支持批量接口，回退为逐个调用
        $results = [];
        foreach ($orderIds as $orderId) {
            $results[] = $this->queryOrder($orderId);
        }
        return ['orders' => $results];
    }

    /**
     * 取件通知（预约寄件）
     */
    public function pickupNotice(array $data): array
    {
        if (empty($data)) {
            throw new ExpressApiException('取件数据不能为空');
        }
        return $this->request('zto.open.pickupNotice', $data);
    }

    /**
     * 查询订单详情
     */
    public function queryOrder(string $orderId): array
    {
        if (empty($orderId)) {
            throw new ExpressApiException('商家订单号不能为空');
        }
        return $this->request('zto.open.orderQuery', [
            'partnerOrderCode' => $orderId,
        ]);
    }

    /**
     * 拦截订单（别名方法）
     */
    public function interceptOrder(string $orderId, string $reason): array
    {
        return $this->intercept($orderId, ['reason' => $reason]);
    }

    /**
     * 改件信息（别名方法）
     */
    public function modify(string $orderId, array $data): array
    {
        return $this->updateOrderInfo($orderId, $data);
    }
}
