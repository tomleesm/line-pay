<?php
namespace tomleesm\LINEPay;

class Signature
{
    static function generate($channelSecret, $requestUri, $requestBody, $nonce)
    {
        $message = $channelSecret . $requestUri . $requestBody . $nonce;
        return base64_encode(hash_hmac('sha256', $message, $channelSecret, true));
    }
}
