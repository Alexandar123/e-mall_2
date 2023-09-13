<?php

namespace Dynamic\FTPImportExport\Helper;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Helper\AbstractHelper;

class ImportImageBakidoo extends AbstractHelper
{
	protected $ftp;
	public function __construct(
        OrderRepositoryInterface $OrderRepositoryInterface,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Io\Ftp $ftp,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Dynamic\FTPImportExport\Helper\Data $helperData,
        \Magento\Sales\Model\Order $order,
        \Magento\Customer\Model\Customer $customer,
        CountryFactory $countryFactory,
        \Dynamic\FTPImportExport\Helper\OrderStatusSync $orderstatushelper,
        \Dynamic\FTPImportExport\Helper\ImportProduct $importProduct,
        Curl $curl,
        \Magento\Framework\Convert\Xml $convertXml,
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
        $this->importProduct = $importProduct;
        $this->curl = $curl;
        $this->convertXml = $convertXml;
        $this->directoryList = $directoryList;
        $this->file = $file;
        return parent::__construct($context);
    }

	public function execute()
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/imageImportBakidoo_execute.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Import Image Bakidoo start...");
        // echo "hello";
        // exit;
        // Process with orderSync file
        // $this->orderstatushelper->getOrderSyncOnFTPimport();
        // exit;
        /*26052023*/
        // $this->printProductsEANForProductsWhichDontHaveImage();
        // exit;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $xmlUrl = "https://www.bakidoo.com/webservis/externalsite/baki/gale_xml.php?seckey=dspfdory529&user=feed&pass=feed";

        try {
            // Fetch the XML data from the external URL
            $this->curl->get($xmlUrl);
            $xmlData = $this->curl->getBody();

            $xml = preg_replace('~\s*(<([^>]*)>[^<]*</\2>|<[^>]*>)\s*~', '$1', $xmlData);
            // Convert CDATA into xml nodes.
            $xmlObject = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

            $xmlData = (array) $xmlObject;
            $xmlData = (array) $xmlData['item'];
            if ($xmlData && count($xmlData) > 0) {
                // echo "<pre>";
                // print_r($xmlData);
                // exit;
                foreach ($xmlData as $key => $productdata) {
                    $productdata = (array) $productdata;
                    // $xmlToArray = $this->convertXml->xmlToAssoc($productdata);
                    try {
                        if (!empty($productdata['EAN']) && !empty($productdata['Slika'])) {
                            /** Manage Image **/
                            $productEAN = $productdata['EAN'];
                            $product = $objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('ean_code', $productEAN);

                            if ($product && $product->getImage() != 'no_selection') {
                                continue;
                            }

                            $imageUrl = $productdata['Slika'];
                            $tmpDir = $this->getMediaDirTmpDir();
                            $this->file->checkAndCreateFolder($tmpDir);
                            $newFileName = $tmpDir . baseName($imageUrl);

                            // Read image from remote URL and save it to tmp location.
                            $result = $this->file->read($imageUrl, $newFileName);

                            $imageType = array('image', 'small_image', 'thumbnail');
                            $visible = false;

                            if ($result) {
                                // var_dump($product);
                                // echo "result: " . $result . "</br>";
                                // echo "EAN From Bakidoo: " . $productEAN . "</br>";
                                if ($product) {
                                    $logger->info("start" . "</br>");
                                    $logger->info("SKU: " . $product->getSku() . "</br>");
                                    $product->addImageToMediaGallery($newFileName, $imageType, true, $visible);
                                    $logger->info("SKU: " . $product->getSku() . "</br>");
                                    $product->save();
                                }
                                unset($product);
                            }
                        }
                    } catch (\Exception $e) {
                        $message = __('An error occurred while fetching XML data from the external URL: %1', $e->getMessage());
                        $logger->info($message);
                    }
                }
            }
            $logger->info("Import Image Bakidoo End...");
            // exit;
        } catch (\Exception $e) {
            $message = __('An error occurred while fetching XML data from the external URL: %1', $e->getMessage());
            $logger->info($message);
        }
    }

    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp/';
    }

    public function printProductsEANForProductsWhichDontHaveImage()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $products = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->addAttributeToSelect('ean_code')
            ->addAttributeToFilter(
                [
                    ['attribute' => 'thumbnail', 'null' => true, 'left'],
                    ['attribute' => 'thumbnail', 'eq' => 'no_selection', 'left']
                ]
            );

        foreach ($products as $product) {

            if ($product->getThumbnail() != '' && $product->getThumbnail() != 'no_selection') {
            } else {
                // echo $product->getEanCode() . "<br>";
                $logger->info($product->getEanCode() . "<br>");
            }
        }
    }
}
