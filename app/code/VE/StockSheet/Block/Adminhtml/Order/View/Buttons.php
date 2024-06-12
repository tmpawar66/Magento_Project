<?php

namespace VE\StockSheet\Block\Adminhtml\Order\View;

class Buttons extends \Magento\Sales\Block\Adminhtml\Order\View
{    

   protected function _construct()
    {
        parent::_construct();

        if(!$this->getOrderId()) {
            return $this;
        }

        $buttonUrl = $this->_urlBuilder->getUrl(
            'vestocksheet/index',
            ['order_id' => $this->getOrder()->getIncrementId()]
        );

        $this->addButton(
            'StockSheet',
            ['label' => __('StockSheet'), 'onclick' => 'setLocation(\'' . $buttonUrl . '\')']
        );
        
        return $this;
    }

}