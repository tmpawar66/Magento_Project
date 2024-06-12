<?php

namespace BmsIndia\Report\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;

class Report extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * Report constructor.
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        AttributeRepositoryInterface $attributeRepository,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->attributeRepository = $attributeRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get order collection
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }



    /**
     * Get all options for a custom attribute
     *
     * @return array
     */
    public function getAttributeValues()
    {
        // Get attribute by custom attribute code
        $attributeCode = $this->attributeRepository->get('catalog_product', 'product_category');
        // Get source model for the attribute
        $source = $attributeCode->getSource();
        // Get All options
        return $source->getAllOptions();
    }

    /**
     * Get the next month name
     *
     * @param int $i Number of months to add
     * @return string
     */
    public function getNextMonth($i)
    {
        return date('F Y', strtotime("+$i month"));
    }

    /**
     * Get product count for a specific category in a specific month
     *
     * @param int $i Number of months to subtract
     * @param string $productCategory Category of the product
     * @return int
     */
    public function getProductCount($i, $productCategory)
    {
        $month = date('F', strtotime("-$i month"));
        $year = date('Y', strtotime("-$i month"));

        $startDate = date('Y-m-d', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));

        $orderCollection = $this->getCollection()
            ->addFieldToFilter('created_at', ['from' => $startDate, 'to' => $endDate]);

        $itemQty = 0;

        foreach ($orderCollection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $category = $item->getProduct()->getProductCategory();
                if ($productCategory == $category) {
                    $itemQty += $item->getQtyOrdered();
                }
            }
        }
        return $itemQty;
    }

    /**
     * Get total product count for a specific category in the last 12 months
     *
     * @param string $productCategory Category of the product
     * @return int
     */
    public function getTotal($productCategory)
    {
        $nextMonth = date('F', strtotime("-11 month"));
        $lastMonth = date('F');
        $nextYear = date('Y', strtotime("-11 month"));
        $lastYear = date('Y');

        $startDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-01"));
        $endDate = date('Y-m-t', strtotime("$lastYear-$lastMonth-01"));

        $orderCollection = $this->getCollection()
            ->addFieldToFilter('created_at', ['from' => $startDate, 'to' => $endDate]);

        $itemQty = 0;

        foreach ($orderCollection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $category = $item->getProduct()->getProductCategory();
                if ($productCategory == $category) {
                    $itemQty += $item->getQtyOrdered();
                }
            }
        }
        return $itemQty;
    }

    public function getHighestLowestValue()
    {
        $nextMonth = date('F', strtotime("-11 month"));
        $lastMonth = date('F');
        $nextYear = date('Y', strtotime("-11 month"));
        $lastYear = date('Y');

        $startDate = date('Y-m-d', strtotime("$nextYear-$nextMonth-01"));
        $endDate = date('Y-m-t', strtotime("$lastYear-$lastMonth-01"));

        $orderCollection = $this->getCollection()
            ->addFieldToFilter('created_at', ['from' => $startDate, 'to' => $endDate]);

        $itemQty = 0;
        $highestVal=0;
        $lowestVal=0;
        $highestCat="";
        $lowestCat="";
           foreach ($this->getAttributeValues() as $categoryItems) {
                foreach ($orderCollection as $order) {
                    foreach ($order->getAllVisibleItems() as $item) {
                        $category = $item->getProduct()->getProductCategory();
                        if ($categoryItems['value'] == $category) {
                            $itemQty += $item->getQtyOrdered();
                        }
                    }
                }
                // Nutralize highest and lowest values at initial time
                if($highestVal==0){
                    
                    $highestVal=$itemQty;
                    $lowestVal=$itemQty;
                    $highestCat=$categoryItems['label'];
                    $lowestCat=$categoryItems['label'];
                }
                // add highest values
                if($highestVal < $itemQty){
                    
                     $highestVal=$itemQty;
                     $highestCat=$categoryItems['label'];
                }
                // add lowest values
                if($lowestVal > $itemQty){
                    $lowestVal=$itemQty;
                    $lowestCat=$categoryItems['label'];
                }

        }

        return $arra=array('highest'=>$highestCat,'lowest'=>$lowestCat);
}

}