<?php

namespace Dynamic\FTPImportExport\Controller\Index;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Psr\Log\LoggerInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $logger;

    public function __construct(
        OrderRepositoryInterface $OrderRepositoryInterface,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Filesystem\Io\Ftp $ftp,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Dynamic\FTPImportExport\Helper\Data $helperData,
        \Magento\Sales\Model\Order $order,
        \Magento\Customer\Model\Customer $customer,
        CountryFactory $countryFactory,
        \Dynamic\FTPImportExport\Helper\OrderStatusSync $orderstatushelper,
        \Dynamic\FTPImportExport\Helper\ImportCategory $importCategory,
        \Dynamic\FTPImportExport\Helper\ImportProduct $importProduct,
        \Dynamic\FTPImportExport\Helper\ImportStock $importStock,
        Curl $curl,
        LoggerInterface $logger, // Fix: Changed argument to LoggerInterface
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->orderRepository = $OrderRepositoryInterface;
        $this->storeManager = $storeManager;
        $this->ftp = $ftp;
        $this->attributeSet = $attributeSet;
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
        $this->order = $order;
        $this->customer = $customer;
        $this->countryFactory = $countryFactory;
        $this->orderstatushelper = $orderstatushelper;
        $this->importCategory = $importCategory;
        $this->importProduct = $importProduct;
        $this->importStock = $importStock;
        $this->curl = $curl;
        $this->productFactory = $productFactory;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $this->logger->info("Importing products...");
            $this->importProduct->import();
            
            $this->logger->info("Importing categories...");
            $this->importCategory->import();

            $this->logger->info("Importing stock...");
            $this->importStock->import();
        } catch (\Exception $e) {
            // Log any exceptions that occur during the execution
            $this->logger->error("An error occurred: " . $e->getMessage());
            // Handle the exception as needed
        }
    }
}

