<?php
namespace tomleesm\LINEPay;

class Product
{
    private $product = null;

    public function __construct(array $product = null)
    {
        $this->product = [];
        foreach($product as $name => $value) {
            $this->product[$name] = $value;
        }

        if($this->product['quantity'] < 0 || $this->product['price'] < 0) {
            throw new \InvalidArgumentException('quantity and price must not be less than 0');
        }
    }

    public function __get(string $name)
    {
        return (string) $this->product[$name];
    }
}
