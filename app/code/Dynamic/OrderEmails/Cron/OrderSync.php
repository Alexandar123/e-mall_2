<?php

namespace Dynamic\OrderEmails\Cron;

class OrderSync
{

    protected $_logger;
    protected $orderstatushelper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Dynamic\OrderEmails\Helper\OrderStatusSync $orderstatushelper
    ) {
        $this->orderstatushelper = $orderstatushelper;
        $this->_logger = $logger;
    }

    /**
     * Method executed when cron runs in server
     */
    public function execute()
    {
        $this->_logger->debug('Running Cron from order sync Start');
        try {
            $this->orderstatushelper->getOrderSyncOnFTPimport();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $this->_logger->debug('Running Cron from order sync End');
        return $this;
    }
}
