<?php

namespace Dynamic\FTPImportExport\Controller\ImageImportBakidoo;

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

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

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
        \Magento\Framework\Convert\Xml $convertXml,
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
        $this->convertXml = $convertXml;
        $this->productFactory = $productFactory;
        $this->directoryList = $directoryList;
        $this->file = $file;
        return parent::__construct($context);
    }

    public function execute()
    {
         //echo "hello";
        // exit;
        // $this->importProduct->import();
        // $this->importCategory->import();
        // exit;
        // $this->importStock->import();
        // exit;
        // Process with orderSync file
        // $this->orderstatushelper->getOrderSyncOnFTPimport();
        // exit;
        /*26052023*/
         $this->printProductsEANForProductsWhichDontHaveImage();
         exit;

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

                            if ($product && ($product->getImage() != null && $product->getImage() != 'no_selection')) {
                                continue;
                            }
			
                            $imageUrl = $productdata['Slika'];
                            $tmpDir = $this->getMediaDirTmpDir();
                            //$this->file->checkAndCreateFolder($tmpDir);
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
                                    echo "start" . "</br>";
                                    echo "SKU: " . $product->getSku() . "</br>";
                                    $product->addImageToMediaGallery($newFileName, $imageType, true, $visible);
                                    echo "SKU: " . $product->getSku() . "</br>";
                                    $product->save();
                                }
                                unset($product);
                            }
                        }
                    } catch (\Exception $e) {
                        $message = __('An error occurred while fetching XML data from the external URL: %1', $e->getMessage());
                        echo $message;
                    }
                }
            }
            // exit;
        } catch (\Exception $e) {
            $message = __('An error occurred while fetching XML data from the external URL: %1', $e->getMessage());
            echo $message;
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
	echo "Number of products without image: " . $products->count() . '<br>';
        foreach ($products as $product) {

            if ($product->getThumbnail() != '' && $product->getThumbnail() != 'no_selection') {
            } else {
                echo $product->getEanCode() . "<br>";
            }
        }
    }

    private function getFTPFileData()
    {
        $path = '/DbToWeb';
        $host = '45.80.134.144';
        $user = 'KorisnikFTP';
        $pwd = 'Koliko321';
        $filename = "";

        // you can see we copy same deatils from the local seetup.
        if (0) {
            $filename = $this->helperData->getGeneralStock('stock_txtfileStock');
        }
        $port = 21;
        $reportReceiver = $this->helperData->getGeneralStock('display_reportreceiverStock');

        // echo $port;
        // exit;

        try {
            $open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => true));

            // https://e-mall.rs/dynamic_ftpimportexport this our live server

            // https://e-mall.rs/dynamic_ftpimportexport this url using check the script 

            // look at that response... connection are build successfully between two server.

            if ($open) {
                $content = [];
                $HistoryFound = 1;
                if ($filename != "") {
                    $path = $this->ftp->cd("/$path/");
                    echo $path;
                    exit;

                    $list = $this->ftp->ls();
                    foreach ($list as $k1 => $v1) {

                        if ($v1['text'] == $filename) {
                            $content[$v1['text']] = $this->ftp->read($v1['text']);
                        }
                    }
                } else {
                    $path = $this->ftp->cd("/$path/");

                    $list = $this->ftp->ls();
                    echo "<pre>";
                    echo "files lists";
                    print_r($list);
                    exit;

                    // here we try to get files and folders.

                    $filename = [];
                    foreach ($list as $k1 => $v1) {
                        if ($v1['text'] != "History") {
                            /*Remove currepted data from file*/
                            if (strpos($v1['text'], 'OrderStatus') !== false) {
                                $datadd = trim($this->ftp->read($filename[] = $v1['text']));
                                $datadd = utf8_decode($datadd);
                                $datadd = str_replace('??', '', $datadd);
                                $datadd = stripslashes(html_entity_decode($datadd));

                                // $product_Data = json_decode($content);
                                // print_r($product_Data);
                                $content[$v1['text']] = $datadd;
                                // $content[$v1['text']] = $this->ftp->read($filename[] = $v1['text']);

                            }
                        }
                        if ($v1['text'] == "History") {
                            $HistoryFound = 0;
                        }
                    }
                }
                if ($HistoryFound) {
                    $this->ftp->mkdir("History");
                }

                if (empty($content)) {
                    return array('lines' => array(), 'filename' => '', 'conn' => $this->ftp);
                }

                $this->ftp->close();
                // echo "<pre>";
                // print_r($content);
                // print_r($filename);
                // print_r($reportReceiver);
                // die;
                return array('lines' => $content, 'filename' => $filename, 'conn' => $this->ftp, 'adminemail' => $reportReceiver);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            $this->ftp->close();
            return false;
        } catch (LocalizedException $e) {
            // echo "LocalizedException";
            // exit;
            $this->ftp->close();
            return false;
        }
    }
    public function getMostViewedProductscategory($id)
    {
        if ($id != 1 || $id != 2) {
            echo "<pre>";
            print_r($id);
            exit;
        }
    }
    public function exportOrder()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Controller_Exportorder.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $path = $this->helperData->getOrderExportData('product_directoryEO');
        $host = $this->helperData->getOrderExportData('display_hostEO');
        $user = $this->helperData->getOrderExportData('display_usernameEO');
        $pwd = $this->helperData->getOrderExportData('display_passwordEO');
        //$OrderId = $this->helperData->getOrderExportData('product_ordernumberEO');
        $port = $this->helperData->getOrderExportData('display_portDiP');
        $OrderId = 41;
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
        // $OrderId = $order->getId();
        if ($OrderId > 0) {
            $open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => true));
            // echo $open;
            // exit;
            $logger->info($open);
            if ($open) {
                $order = $this->order->load($OrderId);
                if (!$order->getId()) {
                    echo "system could not find order with order id : " . $OrderId;
                    return;
                }

                $orderIncrementId = $order->getIncrementId();
                $customer_email = $order->getCustomerEmail();

                /* get Billing details */
                $billingaddress = $order->getBillingAddress();
                $billingname = $billingaddress->getName();
                $billingcity = $billingaddress->getCity();
                $billingstreet = $billingaddress->getStreet();
                $billingpostcode = $billingaddress->getPostcode();
                $billingtelephone = $billingaddress->getTelephone();
                $billingstate = $billingaddress->getRegion();
                $bill_countryName = '';
                $country = $this->countryFactory->create()->loadByCode($billingaddress->getCountryId());
                if ($country) {
                    $bill_countryName = $country->getName();
                }

                /* get shipping details */

                $shippingaddress = $order->getShippingAddress();
                $shipname = $shippingaddress->getName();
                $shippingcity = $shippingaddress->getCity();
                $shippingstreet = $shippingaddress->getStreet();
                $shippingpostcode = $shippingaddress->getPostcode();
                $shippingtelephone = $shippingaddress->getTelephone();
                $shippingstate = $shippingaddress->getRegion();
                $shipp_countryName = '';
                $country = $this->countryFactory->create()->loadByCode($shippingaddress->getCountryId());
                if ($country) {
                    $shipp_countryName = $country->getName();
                }

                /* get payment method name */
                $payment = $order->getPayment();
                $methodCode = $payment->getMethod();

                $orderItems = $order->getAllItems();
                $Items = [];
                foreach ($orderItems as $k => $orderItem) {
                    $Items[] = array("acIdent" => $orderItem->getSku(), "anQty" => $orderItem->getQtyOrdered(), "anRTPrice" => $orderItem->getBasePrice(), "anSalePrice" => $orderItem->getBasePriceInclTax(), "anDiscount" => $orderItem->getBaseDiscountAmount());
                }

                /* prepare export array to convert json */
                $orderarray = [
                    "OrderId" => $order->getId(),
                    "OrderDate" => $order->getCreatedAt(),
                    "acPayMethod" => $methodCode ? $methodCode : "",
                    "acDelivery" => $order->getShippingMethod() ? $order->getShippingMethod() : "",
                    "acDiscountCode" => $order->getCouponCode() ? $order->getCouponCode() : "",
                    "acBuyer" => [
                        [
                            "acSubject" => $billingname . " " . $order->getCustomerId(),
                            "acName2" => $billingname,
                            "acCode" => "",
                            "acRegNo" => "",
                            "acPost" => $billingpostcode ? $billingpostcode : "",
                            "acAddress" => (isset($billingstreet[0]) ? $billingstreet[0] : '') . " " . (isset($billingstreet[1]) ? $billingstreet[1] : ''),
                            "acPhone" => $billingtelephone ? $billingtelephone : "",
                            "acEmail" => $customer_email
                        ]
                    ],
                    "acDeliveryAddress" => [
                        [
                            "acName" => $shipname,
                            "acPost" => $shippingpostcode,
                            "acAddress" => (isset($billingstreet[0]) ? $billingstreet[0] : '') . " " . (isset($billingstreet[1]) ? $billingstreet[1] : ''),
                            "acPhone" => $shippingtelephone ? $shippingtelephone : $shippingtelephone
                        ]
                    ],
                    "Items" => $Items
                ];
                // echo "<pre>";
                // print_r(json_encode($orderarray));
                // exit;



                $st = json_encode($orderarray);
                $logger->info($st);

                $this->ftp->write($path . '/Orders/' . 'Order_data_#' . $order->getIncrementId() . '.txt', $st, $mode = null);
                $this->ftp->close();
                // echo "Please check FTP server and find Order_data_#" . $order->getIncrementId() . ".txt file";
                // return;
            }
        } else {
            // echo "system could not find order";
            return;
        }
    }
}
