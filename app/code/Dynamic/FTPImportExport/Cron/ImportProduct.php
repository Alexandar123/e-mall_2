<?php
namespace Dynamic\FTPImportExport\Cron;

class ImportProduct {

    protected $_logger;
    protected $importProduct;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Dynamic\FTPImportExport\Helper\ImportProduct $importProduct
    ) {
        $this->importProduct = $importProduct;
        $this->_logger = $logger;
    }

    /**
     * Method executed when cron runs in server - Import Products
     */
    public function execute() {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ImportProduct_cron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        try {
            $startTime = $this->getCurrentDateTime();
            $logger->info('Import started at ' . $startTime);

            $this->importProduct->import();
            
            $endTime = $this->getCurrentDateTime();
            $logger->info('Import End at ' . $endTime);
            
            $duration = $this->getDuration($startTime, $endTime);
            $logger->info('Image Products took ' . $duration . ' minutes.');
        } catch (\Exception $th) {
            $logger->info($th->getMessage());
        }
        
        return $this;
    }

    private function getCurrentDateTime()
    {
        date_default_timezone_set('Europe/Belgrade');
        return date("Y-m-d H:i:s");
    }
    private function getDuration($startTime, $endTime)
    {
        $startDateTime = new \DateTime($startTime);
        $endDateTime = new \DateTime($endTime);
        $interval = $startDateTime->diff($endDateTime);
        return $interval->i;
    }
}
