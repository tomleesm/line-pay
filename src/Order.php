<?php
namespace tomleesm\LINEPay;

use tomleesm\LINEPay\Product;

class Order
{
    public function addProduct(Product $product)
    {

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
}
