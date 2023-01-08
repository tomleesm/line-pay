<?php
use PHPUnit\Framework\TestCase;
use tomleesm\LINEPay\Client;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use function Env\env;
use tomleesm\LINEPay\Order;
use tomleesm\LINEPay\Product;
use tomleesm\LINEPay\Currencies\TWD;

class ClientTest extends TestCase
{
    /**
     * new 一個 Client 物件，在建構式傳入必要的參數，使其可以產生 API Authentication 需要的 HTTP header 和 HTTP request body
     **/
    public function testNewObjectWithParameter()
    {
        $option = [
          'channelId' => '1234567890',
          'channelSecret' => 'abcdefg',
          'merchantDeviceProfileId' =>'987654321',
          'nonceType' => 'uuid',
          'confirmUrl' => 'https://pay-store.line.com/order/payment/authorize',
          'cancelUrl' => 'https://pay-store.line.com/order/payment/cancel'
        ];

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
        $client = new Client($order, $option);

        # 檢查 header
        $header = [
            'Content-Type' => 'application/json',
            'X-LINE-ChannelId' => '1234567890',
            'X-LINE-MerchantDeviceProfileId' => '987654321'
        ];

        $this->assertEquals($header['Content-Type'], $client->getHeader()['Content-Type']);
        $this->assertEquals($header['X-LINE-ChannelId'], $client->getHeader()['X-LINE-ChannelId']);
        $this->assertEquals($header['X-LINE-MerchantDeviceProfileId'], $client->getHeader()['X-LINE-MerchantDeviceProfileId']);

        $nonceUUID1 = $client->getHeader()['X-LINE-Authorization-Nonce'];
        $this->assertTrue(is_string($nonceUUID1));
        $this->assertTrue(Uuid::isValid($nonceUUID1));

        $this->assertEquals(44, strlen($client->getHeader()['X-LINE-Authorization']));
        $this->assertEquals('=', substr($client->getHeader()['X-LINE-Authorization'], -1));

        # 檢查 request body
        $requestBody = json_encode([
            'amount' => 100,
            'currency' => 'TWD',
            'orderId' => $orderId,
            'packages' => [
                [
                    'id' => '1',
                    'amount'=> 100,
                    'products' => [
                        [
                            'id' => 'PEN-B-001',
                            'name' => 'Pen Brown',
                            'imageUrl' => 'https://pay-store.line.com/images/pen_brown.jpg',
                            'quantity' => 2,
                            'price' => 50
                        ]
                    ]
                ]
            ],
            'redirectUrls' => [
                'confirmUrl' => 'https://pay-store.line.com/order/payment/authorize',
                'cancelUrl' => 'https://pay-store.line.com/order/payment/cancel'
            ]
        ]);
        $this->assertEquals($requestBody, $client->getRequestBody());
    }

    public function testNewObjectWithoutParameter()
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
        # 透過 .env 注入設定，所以沒有第二個參數 $option
        $client = new Client($order);

        # 檢查 header
        $header = [
            'Content-Type' => 'application/json',
            'X-LINE-ChannelId' => '1657680135'
        ];

        $this->assertEquals($header['Content-Type'], $client->getHeader()['Content-Type']);
        $this->assertEquals($header['X-LINE-ChannelId'], $client->getHeader()['X-LINE-ChannelId']);

        $this->assertEquals(44, strlen($client->getHeader()['X-LINE-Authorization']));
        $this->assertEquals('=', substr($client->getHeader()['X-LINE-Authorization'], -1));

        $nonceUUID1 = $client->getHeader()['X-LINE-Authorization-Nonce'];
        $this->assertTrue(is_string($nonceUUID1));
        $this->assertTrue(Uuid::isValid($nonceUUID1));

        # 檢查 request body
        $requestBody = json_encode([
            'amount' => 100,
            'currency' => 'TWD',
            'orderId' => $orderId,
            'packages' => [
                [
                    'id' => '1',
                    'amount'=> 100,
                    'products' => [
                        [
                            'id' => 'PEN-B-001',
                            'name' => 'Pen Brown',
                            'imageUrl' => 'https://pay-store.line.com/images/pen_brown.jpg',
                            'quantity' => 2,
                            'price' => 50
                        ]
                    ]
                ]
            ],
            'redirectUrls' => [
                'confirmUrl' => 'http://192.168.56.10/confirm',
                'cancelUrl' => 'http://192.168.56.10/cancel'
            ]
        ]);
        $this->assertEquals($requestBody, $client->getRequestBody());
    }

    /**
    public function testRequestAPI()
    {
        $currency = new TWD();
        $product = new Product([
            'id' => 'PEN-B-001',
            'name' => 'Pen Brown',
            'imageUrl' => 'https://pay-store.line.com/images/pen_brown.jpg',
            'quantity' => 2,
            'price' => 50
        ]);
        $order = new Order(null, $currency);
        $order->addProduct($product);

        $client = new Client($order);
        var_dump($client->request());
    }
    **/
}
