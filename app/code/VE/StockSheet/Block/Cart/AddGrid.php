<?php

namespace VE\StockSheet\Block\Cart;

class AddGrid extends \Magento\Checkout\Block\Cart\Grid
{
    protected function _construct()
    {
        parent::_construct();
    }

    public function getQuoteItems()
    {
        return $this->getItems();
    }

    public function getQuoteId()
    {
        return $this->getQuote()->getId();
    }
}
  