<?php
namespace tomleesm\LINEPay;

use tomleesm\LINEPay\Nonce;
use tomleesm\LINEPay\Order;
use GuzzleHttp\Client;
use tomleesm\LINEPay\Signature;

class Payment
{
    private $channelId = '';
    private $channelSecret = '';
    private $merchantDeviceProfileId = '';
    private $nonce = '';
    private $nonceType = '';
    private $confirmUrl = '';
    private $cancelUrl = '';
    private $order = null;

    public function __construct(Order $order = null, $option = null)
    {
          $this->order = $order;

          # load .env
          # 這兩個並不是必要的，所以可能 .env 沒有這兩個
          # 一旦讀取 .env 設定，會被快取，所以必須設定成初始值沒有這兩個
          if( ! empty($_ENV['LINEPAY_MERCHANT_DEVICE_PROFILE_ID']))
              unset($_ENV['LINEPAY_MERCHANT_DEVICE_PROFILE_ID']);
          if( ! empty($_ENV['LINEPAY_CANCEL_URL']))
              unset($_ENV['LINEPAY_CANCEL_URL']);

          $dotenv = \Dotenv\Dotenv::createMutable(__DIR__ . '/..');
          $dotenv->safeLoad();

          $this->setRequireOption($option, 'channelId', 'LINEPAY_CHANNEL_ID');
          $this->setRequireOption($option, 'channelSecret', 'LINEPAY_CHANNEL_SECRET');
          $this->setNonce($option);
          $this->setRequireOption($option, 'confirmUrl', 'LINEPAY_CONFIRM_URL');
          $this->setOption($option, 'cancelUrl', 'LINEPAY_CANCEL_URL');
          $this->setOption($option, 'merchantDeviceProfileId', 'LINEPAY_MERCHANT_DEVICE_PROFILE_ID');
    }

    private function setRequireOption($option, $optionIndex, $envIndex)
    {
        if( ! empty($option[$optionIndex]))
            $this->$optionIndex = $option[$optionIndex];
        else if( ! empty($_ENV[$envIndex]))
            $this->$optionIndex = $_ENV[$envIndex];
        else
            throw new \Exception("set {$optionIndex} via constructor or {$envIndex} in .env");
    }

    private function setOption($option, $optionIndex, $envIndex)
    {
        if( ! empty($option[$optionIndex]))
            $this->$optionIndex = $option[$optionIndex];
        else if( ! empty($_ENV[$envIndex]))
            $this->$optionIndex = $_ENV[$envIndex];
        else
            $this->$optionIndex = '';
    }

    private function setNonce($option)
    {
        $this->setOption($option, 'nonceType', 'LINEPAY_NONCE_TYPE');

        $type = $this->nonceType;
        if ($type == 'uuid')
            $this->nonce = Nonce::get('uuid');
        else if ($type == 'uuid_v1')
            $this->nonce = Nonce::get('uuid', 'v1');
        else if ($type == 'uuid_v4')
            $this->nonce = Nonce::get('uuid', 'v4');
        else if ($type == 'timestamp')
            $this->nonce = Nonce::get('timestamp');
        else
            $this->nonce = Nonce::get('uuid');
    }

    public function getHeader($requestUri = '')
    {
        $header = [
            'Content-Type' => 'application/json',
            'X-LINE-ChannelId' => $this->channelId,
            'X-LINE-Authorization-Nonce' => $this->nonce,
            'X-LINE-Authorization' => Signature::generate(
                                            $this->channelSecret,
                                            $requestUri,
                                            $this->getRequestBody(),
                                            $this->nonce
                                        )
        ];

        if( ! empty($this->merchantDeviceProfileId)) {
            $header = array_merge($header, ['X-LINE-MerchantDeviceProfileId' => $this->merchantDeviceProfileId]);
        }
        return $header;
    }

    public function getRequestBody()
    {
        $products = [];
        $packages = [];
        if ( ! is_null($this->order) && $this->order->getProductList()->count() !== 0 ) {
            foreach($this->order->getProductList() as $p) {
                $products[] = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'imageUrl' => $p->imageUrl,
                    'quantity' => (double) $p->quantity,
                    'price' => (double) $p->price
                ];
            }
            $packages = [
                [
                    'id' => '1',
                    'amount'=> $this->order->getAmount(),
                    'products' => $products
                ]
            ];
        }


        return json_encode([
            'amount' => is_null($this->order) ? 0 : $this->order->getAmount(),
            'currency' => is_null($this->order) ? 'TWD' : (string) $this->order->getCurrency(),
            'orderId' => is_null($this->order) ? '' : $this->order->getOrderId(),
            'packages' => $packages,
            'redirectUrls' => [
                'confirmUrl' => $this->confirmUrl,
                'cancelUrl' => $this->cancelUrl
            ]
        ]);
    }

    public function request()
    {
        $requestUri = '/v3/payments/request';
        $client = new Client([
            'base_uri' => 'https://sandbox-api-pay.line.me'
        ]);
        $response = $client->request(
            'POST',
            $requestUri,
            [
                'body' => $this->getRequestBody(),
                'headers' => $this->getHeader($requestUri)
            ]
        );
        return (string) $response->getBody();
    }
}
