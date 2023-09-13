<?php
namespace Dynamic\FTPImportExport\Cron;

class ImportCategory {

    protected $_logger;
 
    protected $importCategory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Dynamic\FTPImportExport\Helper\ImportCategory $importCategory
    ) {
        $this->importCategory = $importCategory;
        $this->_logger = $logger;
    }

    /**
     * Method executed when cron runs in server
     */
    public function execute() {
        $this->importCategory->import();
        return $this;
    }
}