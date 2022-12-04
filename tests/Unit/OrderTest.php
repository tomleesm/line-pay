<?php
use PHPUnit\Framework\TestCase;
use tomleesm\LINEPay\Order;
use tomleesm\LINEPay\Product;
use tomleesm\LINEPay\Currencies\TWD;

class OrderTest extends TestCase
{
    public function testNormalOrder()
    {
        $orderId = 'MKSI_S_20180904_1000001';
        $currency = new TWD();
        $product = new Product([
            'id' => 'PEN-B-001',
            'name' => 'Pen Brown',
            'imageUrl' => 'https://pay-store.line.com/images/pen_brown.jpg',
            'quantity' => 2,
            'price' => 50
        ]);

        $order = new Order($orderId, $currency);
        $order->addProduct($product);

        # 訂單是有效的
        $this->assertTrue($order->isValid());
        # 自動計算訂單總金額
        $this->assertEquals(100, $order->getAmount());
        $this->assertEquals($orderId, $order->getOrderId());
        $this->assertInstanceOf(TWD::class, $order->getCurrency());
    }
}
