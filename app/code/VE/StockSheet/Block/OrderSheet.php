<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VE\StockSheet\Block;

use Magento\Framework\View\Element\Template\Context as TemplateContext;


class OrderSheet extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'VE_StockSheet::ordersheet.phtml';

    
    /**
     * @param TemplateContext $context    
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,     
        \VE\StockSheet\Helper\Data $helper,
        \Magento\Theme\Block\Html\Header\Logo $logo,  
        \Magento\Framework\App\RequestInterface $request,         
        array $data = []
    ) {       
        $this->helper = $helper; 
        $this->logo = $logo;
        $this->request = $request;
        $this->_isScopePrivate = true;
        parent::__construct($context, $data);
    }
    
    public function getFinalData()
    {
        $id = $this->request->getParam('id');
        $final = $this->helper->getAllData($id,'order');
        return $final;
    }

    /**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {
        return $this->logo->getLogoSrc();
    }

}
