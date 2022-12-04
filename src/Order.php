<?php
namespace tomleesm\LINEPay;

use tomleesm\LINEPay\Product;
use tomleesm\LINEPay\Currencies\Currency;
use tomleesm\LINEPay\Currencies\TWD;

class Order
{
    private $orderId = '';
    private $currency = null;
    private $productList = null;
    private $amount = null;

    public function __construct($orderId = null, Currency $currency = null)
    {
        $this->orderId = is_null($orderId) ? $this->generateOrderId() : $orderId;
        $this->currency = is_null($currency) ? new TWD() : $currency;
        $this->productList = new \SplObjectStorage();
    }

    public function addProduct(Product $product)
    {
        $this->productList->attach($product);

        bcscale(3);
        foreach($this->productList as $product) {
            $this->amount = bcadd($this->amount, bcmul($product->quantity, $product->price));
        }
    }

    /**
     * 檢查訂單是有效的：
     *   - 有訂單編號且在 LINE Pay 支援範圍內
     *   - 貨幣 LINE Pay 有支援
     *   - 訂單內有商品
     *   - 總金額大於等於 0
     **/
    public function isValid()
    {
        return isset($this->orderId) && is_string($this->orderId)
            && mb_strlen($this->orderId) > 0 && mb_strlen($this->orderId) <= 100
            && $this->currency instanceof Currency
            && $this->productList->count() > 0
            && $this->getAmount() >= 0;
    }

    public function getAmount()
    {
        return (double) $this->amount;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}
