<?php
namespace tomleesm\LINEPay;

class Result
{
    private $result = '';

    public function __construct($json)
    {
        $this->result = json_decode($json);
    }

    public function isSuccessful()
    {
        return ! empty($this->result->returnCode)
            && $this->result->returnCode == '0000';
    }

    public function getPaymentUrl()
    {
        return $this->result->info->paymentUrl->web;
    }
}
