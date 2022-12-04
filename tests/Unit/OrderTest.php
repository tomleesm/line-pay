<?php
use PHPUnit\Framework\TestCase;
use tomleesm\LINEPay\Order;
use tomleesm\LINEPay\Product;

class OrderTest extends TestCase
{
    public function testNormalOrder()
    {
        $orderId = 'MKSI_S_20180904_1000001';
        $currency = 'TWD';
        $product = new Product([
            'id' => 'PEN-B-001',
            'name' => 'Pen Brown',
            'imageUrl' => 'https://pay-store.line.com/images/pen_brown.jpg',
            'quantity' => 2,
            'price' => 50
        ]);

        $order = new Order($orderId, $currency);
        $order->addProduct($product);

        # 訂單是有效的：有訂單編號且在 LINE Pay 支援範圍內、貨幣 LINE Pay 有支援、訂單內有商品、總金額大於等於 0、商品售價和數量都大於等於 0
        $this->assertTrue($order->isValid());
        # 自動計算訂單總金額
        $this->assertEquals(100, $order->getAmount());
        $this->assertEquals($orderId, $order->getOrderId());
        $this->assertEquals($currency, $order->getCurrency());
    }
}
