<?php
use PHPUnit\Framework\TestCase;
use tomleesm\LINEPay\Payment;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use function Env\env;
use tomleesm\LINEPay\Order;
use tomleesm\LINEPay\Product;
use tomleesm\LINEPay\Currencies\TWD;

class PaymentTest extends TestCase
{
    /**
     * new 一個 Payment 物件，傳入必要的參數，使其可以產生 API Authentication 需要的 HTTP header
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
            'ContentType' => 'application/json',
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

        $p = new Payment(null, $option);

        $this->assertEquals($header['ContentType'], $p->getHeader()['ContentType']);
        $this->assertEquals($header['X-LINE-ChannelId'], $p->getHeader()['X-LINE-ChannelId']);
        $this->assertEquals($header['X-LINE-MerchantDeviceProfileId'], $p->getHeader()['X-LINE-MerchantDeviceProfileId']);

        $nonceUUID1 = $p->getHeader()['X-LINE-Authorization-Nonce'];
        $this->assertTrue(is_string($nonceUUID1));
        $this->assertTrue(Uuid::isValid($nonceUUID1));

        $this->assertEquals($requestBody, $p->getRequestBody());
    }

    public function testNewObjectWithoutParameter()
    {
        $merchantDeviceProfileId = '9876543210';
        $channelId = '1234567890';
        $option = [
          'LINEPAY_CHANNEL_ID' => $channelId,
          'LINEPAY_CHANNEL_SECRET' => 'abcdefg',
          'LINEPAY_MERCHANT_DEVICE_PROFILE_ID' => $merchantDeviceProfileId,
          'LINEPAY_NONCE_TYPE' => 'uuid',
          'LINEPAY_CONFIRM_URL' => 'https://pay-store.line.com/order/payment/authorize',
          'LINEPAY_CANCEL_URL' => 'https://pay-store.line.com/order/payment/cancel'
        ];

        # $option 新增到 .env 檔案
        $filesystem = new Filesystem();
        $envPath = '.env';
        if($filesystem->exists($envPath)) {
            $filesystem->remove([$envPath]);
        }
        $filesystem->appendToFile($envPath, 'LINEPAY_CHANNEL_ID=' . $option['LINEPAY_CHANNEL_ID'] . PHP_EOL);
        $filesystem->appendToFile($envPath, 'LINEPAY_CHANNEL_SECRET=' . $option['LINEPAY_CHANNEL_SECRET'] . PHP_EOL);
        $filesystem->appendToFile($envPath, 'LINEPAY_MERCHANT_DEVICE_PROFILE_ID=' . $option['LINEPAY_MERCHANT_DEVICE_PROFILE_ID'] . PHP_EOL);
        $filesystem->appendToFile($envPath, 'LINEPAY_NONCE_TYPE=' . $option['LINEPAY_NONCE_TYPE'] . PHP_EOL);
        $filesystem->appendToFile($envPath, 'LINEPAY_CONFIRM_URL=' . $option['LINEPAY_CONFIRM_URL'] . PHP_EOL);
        $filesystem->appendToFile($envPath, 'LINEPAY_CANCEL_URL=' . $option['LINEPAY_CANCEL_URL'] . PHP_EOL);

        $header = [
            'ContentType' => 'application/json',
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

        $p = new Payment();

        $this->assertEquals($header['ContentType'], $p->getHeader()['ContentType']);
        $this->assertEquals($header['X-LINE-ChannelId'], $p->getHeader()['X-LINE-ChannelId']);
        $this->assertEquals($header['X-LINE-MerchantDeviceProfileId'], $p->getHeader()['X-LINE-MerchantDeviceProfileId']);

        $nonceUUID1 = $p->getHeader()['X-LINE-Authorization-Nonce'];
        $this->assertTrue(is_string($nonceUUID1));
        $this->assertTrue(Uuid::isValid($nonceUUID1));

        $this->assertEquals($requestBody, $p->getRequestBody());
    }

    /**
     * 新增訂單後，生成 HTTP request body，自動計算金額
     **/
    public function testRequestBodyWithOrder()
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

        $p = new Payment($order, $option);

        $this->assertEquals($requestBody, $p->getRequestBody());
    }
}
