<?php
namespace tomleesm\LINEPay;

use Ramsey\Uuid\Uuid;

class Nonce
{
    static function get($type = 'uuid', $uuidVersion = 'v1')
    {
        if($type == 'timestamp')
            return time();
        if($uuidVersion == 'v4')
            return Uuid::uuid4();

        return Uuid::uuid1();
    }
}
