<?php
use PHPUnit\Framework\TestCase;
use tomleesm\LINEPay\Payment;
use Ramsey\Uuid\Uuid;

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

        $p = new Payment($option);

        $this->assertEquals($header['ContentType'], $p->getHeader()['ContentType']);
        $this->assertEquals($header['X-LINE-ChannelId'], $p->getHeader()['X-LINE-ChannelId']);
        $this->assertEquals($header['X-LINE-MerchantDeviceProfileId'], $p->getHeader()['X-LINE-MerchantDeviceProfileId']);

        $nonceUUID1 = $p->getHeader()['X-LINE-Authorization-Nonce'];
        $this->assertTrue(is_string($nonceUUID1));
        $this->assertTrue(Uuid::isValid($nonceUUID1));

        $this->assertEquals($requestBody, $p->getRequestBody());
    }
}
