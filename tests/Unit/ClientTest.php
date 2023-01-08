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
    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        if($filesystem->exists('.env')) {
            $filesystem->remove(['.env']);
        }
    }

    /**
     * new 一個 Client 物件，傳入必要的參數，使其可以產生 API Authentication 需要的 HTTP header
     **/
    public function testNewObjectWithParameter()
    {
        $merchantDeviceProfileId = '9876543210';
        $channelId = '1234567890';
        $option = [
          'channelId' => $channelId,
          'channelSecret' => 'abcdefg',
          'merchantDeviceProfileId' =>$merchantDeviceProfileId,
          'nonceType' => 'uuid',
          'confirmUrl' => 'https://pay-store.line.com/order/payment/authorize',
          'cancelUrl' => 'https://pay-store.line.com/order/payment/cancel'
        ];

        $header = [
            'Content-Type' => 'application/json',
            'X-LINE-ChannelId' => $channelId,
            'X-LINE-MerchantDeviceProfileId' => $merchantDeviceProfileId,
        ];
        $requestBody = json_encode([
            'amount' => 0,
            'currency' => 'TWD',
            'orderId' => '',
            'packages' => [],
            'redirectUrls' => [
                'confirmUrl' => 'https://pay-store.line.com/order/payment/authorize',
                'cancelUrl' => 'https://pay-store.line.com/order/payment/cancel'
            ]
        ]);

        $client = new Client(null, $option);

        $this->assertEquals($header['Content-Type'], $client->getHeader()['Content-Type']);
        $this->assertEquals($header['X-LINE-ChannelId'], $client->getHeader()['X-LINE-ChannelId']);
        $this->assertEquals($header['X-LINE-MerchantDeviceProfileId'], $client->getHeader()['X-LINE-MerchantDeviceProfileId']);

        $nonceUUID1 = $client->getHeader()['X-LINE-Authorization-Nonce'];
        $this->assertTrue(is_string($nonceUUID1));
        $this->assertTrue(Uuid::isValid($nonceUUID1));

        $this->assertEquals($requestBody, $client->getRequestBody());

        $this->assertEquals(44, strlen($client->getHeader()['X-LINE-Authorization']));
        $this->assertEquals('=', substr($client->getHeader()['X-LINE-Authorization'], -1));
    }

    public function testNewObjectWithoutParameter()
    {
        $merchantDeviceProfileId = '9876543212';
        $channelId = '1234567892';

        # $option 新增到 .env 檔案
        $filesystem = new Filesystem();
        $filesystem->copy('.env.test1', '.env');

        $header = [
            'Content-Type' => 'application/json',
            'X-LINE-ChannelId' => $channelId,
            'X-LINE-MerchantDeviceProfileId' => $merchantDeviceProfileId,
        ];
        $requestBody = json_encode([
            'amount' => 0,
            'currency' => 'TWD',
            'orderId' => '',
            'packages' => [],
            'redirectUrls' => [
                'confirmUrl' => 'https://pay-store.line.com/order/payment/authorize',
                'cancelUrl' => 'https://pay-store.line.com/order/payment/cancel'
            ]
        ]);

        $client = new Client();

        $this->assertEquals($header['Content-Type'], $client->getHeader()['Content-Type']);
        $this->assertEquals($header['X-LINE-ChannelId'], $client->getHeader()['X-LINE-ChannelId']);
        $this->assertEquals($header['X-LINE-MerchantDeviceProfileId'], $client->getHeader()['X-LINE-MerchantDeviceProfileId']);

        $this->assertEquals(44, strlen($client->getHeader()['X-LINE-Authorization']));
        $this->assertEquals('=', substr($client->getHeader()['X-LINE-Authorization'], -1));

        $nonceUUID1 = $client->getHeader()['X-LINE-Authorization-Nonce'];
        $this->assertTrue(is_string($nonceUUID1));
        $this->assertTrue(Uuid::isValid($nonceUUID1));

        $this->assertEquals($requestBody, $client->getRequestBody());
    }

    /**
     * 新增訂單後，生成 HTTP request body，自動計算金額
     **/
    public function testRequestBodyWithOrder()
    {
        $merchantDeviceProfileId = '9876543211';
        $channelId = '1234567891';
        $option = [
          'channelId' => $channelId,
          'channelSecret' => 'abcdefg',
          'merchantDeviceProfileId' =>$merchantDeviceProfileId,
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

        $client = new Client($order, $option);

        $this->assertEquals($requestBody, $client->getRequestBody());
    }

    public function testRequestAPI()
    {
        $filesystem = new Filesystem();
        $filesystem->copy('.env.test2', '.env');

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
}
