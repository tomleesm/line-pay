<?php
use PHPUnit\Framework\TestCase;
use tomleesm\LINEPay\Signature;

class SignatureTest extends TestCase
{
    /**
     * 計算 Hmac Signature
     */
	public function testGenerateSignature()
	{
      $channelSecret = 'ChannelSecret';
      $requestUri = '/v3/payments/request';
      $requestBody = json_encode([
          'orderId' => 'abc123',
          'amount' => 10,
          'currency' => 'TWD'
      ]);
      $nonce = time();

      $signature = Signature::generate($channelSecret, $requestUri, $requestBody, $nonce);

      # 結果類似 qPCpImno0RDXnG1X5GCR/U3EdkcZO/vIP+ulx0WHar0=
      # 44 個字元的字串，結尾是 =
      $this->assertEquals(44, strlen($signature));
      $this->assertEquals('=', substr($signature, -1));
	}
}
