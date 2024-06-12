<?php

namespace VE\StockSheet\Cron;
use Magento\Sales\Model\OrderFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
/**
 * Get Stock from ritter
 */
class StockSync
{
    /**
     * @var $logger
     */
    protected $logger;

    /**
     * @param \VE\StockSheet\Logger\Logger $logger     
     */
    public function __construct(
        \VE\StockSheet\Logger\Logger $logger,
        OrderFactory $orderFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magento\Framework\Filesystem $filesystem,
        FileFactory $fileFactory,
        \VE\StockSheet\Helper\Data $helper
        
    ) {
        $this->logger = $logger; 
        $this->orderFactory = $orderFactory;
        $this->productRepository = $productRepository;
        $this->customerFactory = $customerFactory;  
        $this->productFactory = $productFactory; 
        $this->_ProductAttributeRepository = $productAttributeRepository; 
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); 
        $this->fileFactory = $fileFactory;
        $this->helper = $helper;
    }
   
    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute()
    {
        
        
        $orderModel = $this->orderFactory->create();
        $order = $orderModel->loadByIncrementId('000000007');
        $total_qty = 0;
        foreach ($order->getAllVisibleItems() as $_item) {            
            $this->logger->info(json_encode($_item->debug()));
            $product = $this->productRepository->get($_item->getSku());
            $color = $product->getResource()->getAttribute('color')->getFrontend()->getValue($product);
            $size = $product->getResource()->getAttribute('size')->getFrontend()->getValue($product);
            $gender = $product->getResource()->getAttribute('gender')->getFrontend()->getValue($product);
            $allSize[] = $size;
            $data[] = ['discription' => $_item->getName(),'gender'=>$gender, 'color' => $color, 'size' => $size, 'qty' => $_item->getQtyOrdered()]; 
            $total_qty = $total_qty + $_item->getQtyOrdered();          
        }
        $newSizes =  $this->helper->sort(array_unique($allSize));
        array_push($newSizes,'Total');
        
        $filepath = 'export/customdata.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $header = array_merge(['Description','Sex','Color'],$newSizes);       
        $stream->writeCsv($header);
        $stream->lock();
        
        foreach($newSizes as $rowdt){ 
            if($rowdt!="description" && $rowdt!="color" && $rowdt!="gender"){
                $rowdata[$rowdt]="";
            } 
        }
        
        foreach($data as $key=>$rowdt) {
            $resultdata[$rowdt["discription"]][$rowdt["gender"]][$rowdt["color"]][$rowdt["size"]] = $rowdt["qty"];  
        }
        
        foreach($resultdata as $dkey=>$desdata) {            
            foreach($desdata as $gkey=>$genderdata) {
                foreach ($genderdata as $ckey => $colordata) {
                    $resultdata[$dkey][$gkey][$ckey]=$rowdata;
                    $resultdata[$dkey][$gkey][$ckey]['Total']=array_sum($colordata);
                    foreach($colordata as $qkey=>$qdata){                        
                        $resultdata[$dkey][$gkey][$ckey][$qkey]=$qdata;
                    }
                }
            }
        }
       
        foreach ($resultdata as $keyR => $valueR) {
            
            $test['description'] = $keyR;
            foreach ($valueR as $keyG => $valueG) {
                $test['sex'] = $keyG;
                foreach ($valueG as $keyC => $valueC) {
                    $test['color'] = $keyC;
                    foreach ($valueC as $key => $value) {                       
                        $test[$key] = $value;                       
                    }
                    
                    $stream->writeCsv($test);
                }
            }
        }
    }   
    
}
