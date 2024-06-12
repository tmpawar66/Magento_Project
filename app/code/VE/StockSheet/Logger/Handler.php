<?php
namespace VE\StockSheet\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * Add the filename
     *
     * @var string
     */
    protected $fileName = '/var/log/StockSheet.log';
}
