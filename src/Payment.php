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
          $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
          $dotenv->safeLoad();

          $this->setOption($option, 'channelId', 'LINEPAY_CHANNEL_ID');
          $this->setOption($option, 'channelScrect', 'LINEPAY_CHANNEL_SECRET');
          $this->setOption($option, 'merchantDeviceProfileId', 'LINEPAY_MERCHANT_DEVICE_PROFILE_ID');
          $this->setNonce($option);
          $this->setOption($option, 'confirmUrl', 'LINEPAY_CONFIRM_URL');
          $this->setOption($option, 'cancelUrl', 'LINEPAY_CANCEL_URL');
    }

    private function setOption($option, $optionIndex, $envIndex)
    {
        if( ! empty($option[$optionIndex]))
            $this->$optionIndex = $option[$optionIndex];
        else if( ! empty($_ENV[$envIndex]))
            $this->$optionIndex = $_ENV[$envIndex];
        else
            throw new \Exception("set {$optionIndex} via constructor or {$envIndex} in .env");
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
        return [
            'Content-Type' => 'application/json',
            'X-LINE-ChannelId' => $this->channelId,
            'X-LINE-MerchantDeviceProfileId' => $this->merchantDeviceProfileId,
            'X-LINE-Authorization-Nonce' => $this->nonce,
            'X-LINE-Authorization' => Signature::generate(
                                            $this->channelSecret,
                                            $requestUri,
                                            $this->getRequestBody(),
                                            $this->nonce
                                        )
        ];
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
}
