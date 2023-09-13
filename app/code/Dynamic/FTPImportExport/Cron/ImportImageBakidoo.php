<?php

namespace Dynamic\FTPImportExport\Cron;

class ImportImageBakidoo
{
    protected $imageImportHelper;
    protected $_logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Dynamic\FTPImportExport\Helper\ImportImageBakidoo $imageImportHelper
    ) {
        $this->imageImportHelper = $imageImportHelper;
        $this->_logger = $logger;
    }

    /**
     * Method executed when cron runs in server - Update image from Bakidoo.rs
     */
    public function execute()
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/imageImportBakidoo_cron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        try {
            $startTime = $this->getCurrentDateTime();
            $logger->info('Import started at ' . $startTime);

            $this->imageImportHelper->execute();
            
            $endTime = $this->getCurrentDateTime();
            $logger->info('Import End at ' . $endTime);
            
            $duration = $this->getDuration($startTime, $endTime);
            $logger->info('Image Update took ' . $duration . ' minutes.');
        } catch (\Exception $th) {
            $logger->error($th->getMessage());
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
