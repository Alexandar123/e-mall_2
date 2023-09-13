<?php

namespace Dynamic\FTPImportExport\Cron;

class OrderSync
{
    protected $orderstatushelper;

    public function __construct(
        \Dynamic\FTPImportExport\Helper\OrderStatusSync $orderstatushelper
    ) {
        $this->orderstatushelper = $orderstatushelper;
    }

    /**
     * Method executed when cron runs in server - ERP return order file to /DbToWeb location
     */
    public function execute()
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/OrderSync_cron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        try {
            $startTime = $this->getCurrentDateTime();
            $logger->info('Import started at ' . $startTime);

            $this->orderstatushelper->getOrderSyncOnFTPimport();
            
            $endTime = $this->getCurrentDateTime();
            $logger->info('Import End at ' . $endTime);
            
            $duration = $this->getDuration($startTime, $endTime);
            $logger->info('Order Sync ' . $duration . ' seconds.');
        } catch (\Exception $th) {
            $logger->info($th->getMessage());
        }
        return $this;
    }

    private function getCurrentDateTime() {
        date_default_timezone_set('Europe/Belgrade'); // Set the default timezone to CEST
        return date("Y-m-d H:i:s"); 
    }

    private function getDuration($startTime, $endTime)
    {
        $startDateTime = new \DateTime($startTime);
        $endDateTime = new \DateTime($endTime);
        $interval = $startDateTime->diff($endDateTime);
        return $interval->s;
    }
}

