<?php

namespace VE\StockSheet\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Show listing of ritter order sync
 */
class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var $logger
     */
    protected $logger;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\RequestInterface $request,
        \VE\StockSheet\Logger\Logger $logger,      
        \Magento\Framework\Filesystem $filesystem,
        FileFactory $fileFactory,
        \VE\StockSheet\Helper\Data $helper
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->logger = $logger;           
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); 
        $this->fileFactory = $fileFactory;
        $this->helper = $helper;
    }

    /**
     * Load the page defined 
     *
     * @return Page
     */
    public function execute()
    {
        //Getting order increament id from request
        $orderid = $this->request->getParam('order_id');

        #calling helper function
        $resultdata =  $this->helper->getAllData($orderid,'order'); 

        //Saving csv in  var/export folder
        $filepath = 'export/stocksheet.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->writeCsv($resultdata['header']);
        $stream->lock();

        #creating array for csv
        foreach ($resultdata['resultdata'] as $keyR => $valueR) {
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

        #downloading csv and removing it from folder       
        $content['type'] = 'filename';
        $content['value'] = $filepath;
        $content['rm'] = 1;
        return $this->fileFactory->create('stocksheet.csv', $content, DirectoryList::VAR_DIR);
    }
}
