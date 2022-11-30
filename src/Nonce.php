<?php
namespace tomleesm\LINEPay;

use Ramsey\Uuid\Uuid;

class Nonce
{
    static function get($type = 'uuid', $uuidVersion = null)
    {
        if($type == 'timestamp' && is_null($uuidVersion))
            return time();
        if($type == 'uuid' && ($uuidVersion == 'v1' || $uuidVersion === null))
            return Uuid::uuid1()->toString();
        if($type == 'uuid' && $uuidVersion == 'v4')
            return Uuid::uuid4()->toString();

        throw new \InvalidArgumentException('it is not supported argument.');
    }
}
