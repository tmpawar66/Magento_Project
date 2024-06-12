<?php

namespace VE\StockSheet\Block;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    /**
     * @var Quote|null
     */
    protected $_quote = null;

    /**
     * @var array
     */
    protected $_totals;

    /**
     * @var array
     */
    protected $_itemRenders = [];

    /**
     * TODO: MAGETWO-34827: unused object?
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \VE\StockSheet\Helper\Data $helper,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->helper = $helper;  
        $this->logo = $logo;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Get all cart items
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getAllItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    public function getFinalData()
    {
        $id = $this->getQuote()->getId();
        $final = $this->helper->getAllData($id,'quote');
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