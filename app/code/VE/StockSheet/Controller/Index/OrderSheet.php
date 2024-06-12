<?php
namespace VE\StockSheet\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Dompdf\Dompdf;
use Magento\Framework\Controller\ResultFactory;

class OrderSheet extends \Magento\Framework\App\Action\Action
{
    public function __construct (
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
        \VE\StockSheet\Logger\Logger $logger,
        \Magento\Framework\Filesystem $filesystem,
        FileFactory $fileFactory,
        \VE\StockSheet\Helper\Data $helper,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request
    ){
		$this->_pageFactory = $pageFactory;
        $this->logger = $logger;
        $this->filesystem = $filesystem;       
        $this->fileFactory = $fileFactory;
        $this->helper = $helper;  
        $this->layout = $layout;  
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
		return parent::__construct($context);
	}

	public function execute()
	{
        // Creating csv from the cart/stocksheet.phtml
        $mediaPath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('stocksheet');
        $files = glob($mediaPath.'/*');
        //foreach($files as $file) { 
          //  unlink($file);  
        //}
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productpdf = $this->layout->createBlock('\VE\StockSheet\Block\OrderSheet')->setData('area','frontend')->setTemplate('VE_StockSheet::ordersheet.phtml')->toHtml();
        $processor = $objectManager->create('Magento\Cms\Model\Template\FilterProvider');
        $finalpdf = $processor->getBlockFilter()->filter($productpdf);
        // Loading dom and convert the html to pdf and saving in media directory
        $dompdf = new Dompdf();
        
        $dompdf->load_html($finalpdf);
        $dompdf->set_option('isRemoteEnabled', TRUE);
        $dompdf->setPaper('A4','landscape');
        $dompdf->render();
        // $output = $dompdf->output();
        $output = $dompdf->stream();
        exit();
        // $pdfFile = file_put_contents($mediaPath . "/stocksheet.pdf", $output);
        // $result = $this->resultJsonFactory->create();
        // $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        // $downloadPath = $mediaUrl.'stocksheet/stocksheet.pdf';      

        // $content['type'] = 'filename';
        // $content['value'] = $mediaPath;
        // $content['rm'] = 1;       
        return $this->fileFactory->create('stocksheet.pdf',@file_get_contents($downloadPath));       
        // return $this->_pageFactory->create(); 
	}
}
