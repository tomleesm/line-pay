<?php
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use tomleesm\LINEPay\Nonce;

class NonceTest extends TestCase
{
    /**
     * 測試 nonce 是一個 UUID v1 或 v4
     * 或是毫秒以下的時間戳（timestamp）
     */
	public function testGetNonce()
	{
      # 預設回傳 UUID v1 字串
      $nonceUUID1 = Nonce::get();
      # 指定回傳 UUID 字串(預設 v1)
      $nonceUUID2 = Nonce::get('uuid');
      $nonceUUID3 = Nonce::get('uuid', 'v1');
      $nonceUUID4 = Nonce::get('uuid', 'v4');
      # 也可以回傳 timestamp 字串
      $nonceTimestamp = Nonce::get('timestamp');

      $this->assertTrue(is_string($nonceUUID1));
      $this->assertTrue(Uuid::isValid($nonceUUID1));
      $this->assertTrue(is_string($nonceUUID2));
      $this->assertTrue(Uuid::isValid($nonceUUID2));
      $this->assertEquals('1', Uuid::fromString($nonceUUID3)->getFields()->getVersion());
      $this->assertEquals('4', Uuid::fromString($nonceUUID4)->getFields()->getVersion());

      $date = (new \DateTime)->setTimestamp($nonceTimestamp);
      $this->assertEquals($nonceTimestamp, $date->getTimestamp());
	}
}
