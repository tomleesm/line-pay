<?php
namespace tomleesm\LINEPay;

use tomleesm\LINEPay\Nonce;

class Payment
{
    private $channelId = '';
    private $channelSecret = '';
    private $merchantDeviceProfileId = '';
    private $nonce = '';
    private $confirmUrl = '';
    private $cancelUrl = '';

    public function __construct($option)
    {
          $this->channelId = $option['channelId'];
          $this->channelSecret = $option['channelSecret'];
          $this->merchantDeviceProfileId = $option['merchantDeviceProfileId'];
          $this->setNonce($option['nonceType']);
          $this->confirmUrl = $option['confirmUrl'];
          $this->cancelUrl = $option['cancelUrl'];
    }

    private function setNonce($type)
    {
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

    public function getHeader()
    {
        return [
            'ContentType' => 'application/json',
            'X-LINE-ChannelId' => $this->channelId,
            'X-LINE-MerchantDeviceProfileId' => $this->merchantDeviceProfileId,
            'X-LINE-Authorization-Nonce' => $this->nonce
        ];
    }

    public function getRequestBody()
    {
        return json_encode([
            'amount' => 0,
            'currency' => 'TWD',
            'orderId' => '',
            'packages' => [],
            'redirectUrls' => [
                'confirmUrl' => $this->confirmUrl,
                'cancelUrl' => $this->cancelUrl
            ]
        ]);
    }
}
