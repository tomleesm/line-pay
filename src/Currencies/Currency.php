<?php
namespace tomleesm\LINEPay\Currencies;

abstract class Currency
{
    public function __toString()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
