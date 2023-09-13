<?php

namespace Dynamic\FTPImportExport\Helper;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;


class ImportStock extends AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Dynamic\FTPImportExport\Helper\Data $helperData,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\Io\Ftp $ftp,
        \Psr\Log\LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $stockItem
    ) {
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
        $this->_product = $product;
        $this->ftp = $ftp;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->messageManager = $messageManager;
        $this->itemFactory = $itemFactory;
        $this->stockItem = $stockItem;
        return parent::__construct($context);
    }

    public function import($checkisFilename = false)
    {
        $Filelines = $this->getFTPFileData($checkisFilename);

        // echo "<pre>";
        // print_r($Filelines);
        // exit;
        if ($Filelines) {
            foreach ($Filelines['lines'] as $key => $value) {
                $this->importStock($key, $value, $Filelines['conn'], $Filelines['adminemail']);
            }
        }
    }

    private function importStock($filename, $content, $conn, $adminemail)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/importStock.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $adminreceiver = 'default@default.com';
        $adminlogdata = 0;
        if (isset($adminemail)) {
            $adminreceiver = $adminemail;
        }

        if (!$content) {
            $logger->info("content empty");
            return;
        }

        $logger->info('Import started at ' . Date("Y-m-d H:i:s"));


        // $product_Data = json_decode($content);
        $product_Data = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content), true);

        if (empty($product_Data)) {
            return;
        }
        $log_counter = 0;
        $admin_log_counter = 0;
        // print_r($product_Data);die;

        foreach ($product_Data as $k => $product) {
            // $product = get_object_vars($y);
            if ($product) {
                $acIdent = $product['acIdent'];
                try {
                    if ($this->_product->getIdBySku($acIdent)) {
                        /*********************Editing of existing product*************************/
                        $existingProduct = $this->loadMyProduct($acIdent);
                        // print_r($existingProduct);die;  
                        try {
                            $qty = (int)$product['anStock'];
                            $stockData = [
                                'use_config_manage_stock' => 0,
                                'manage_stock' => 1,
                                'is_in_stock' => $qty > 0,
                                'qty' => $qty
                            ];

                            $existingProduct->setStockData($stockData);

                            $existingProduct->save();
                            $logger->info(++$log_counter . 'acIdent : ' . $acIdent . ', New Quantity: ' . $product['anStock']);

                        } catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {

                            $logger->info(++$log_counter . ' : ' . $acIdent . ' URL rewrite ERROR HAPPENED (update mode);');

                            $existingProduct->setUrlKey($product['acName'] . rand(100, 1000));
                            $existingProduct->setStoreId(0);
                            $existingProduct->save();
                            $logger->info(++$log_counter . ' : ' . $acIdent . ' URL rewrite ERROR HAPPENED but product url edited and imported again  (update mode);');
                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                            $logger->info(++$log_counter . ' : ' . $acIdent . 'SKU linking ERROR HAPPENED  (update mode);');
                        } catch (\Magento\Framework\Exception\RuntimeException $e) {
                            $logger->info(++$log_counter . ' : ' . $acIdent . ' Runtime exception ERROR HAPPENED  (update mode);');
                        } catch (\Exception $e) {
                            $logger->info(++$log_counter . ' : ' . $acIdent . $e->getMessage());
                        }

                        /*********************Editing of existing product*************************/
                    }
                } catch (\Exception $e) {
                    echo 'Please check format of data';
                    print_r($e->getMessage());
                    $logger->error("Please check format of data: " . $e->getMessage());
                }
                //}
            }
        }
        //exit;
        $logger->info('Import ended at ' . Date("Y-m-d H:i:s"));

        $MoveFileToHistory = $this->MoveReadFile($filename, $conn, $adminreceiver, $adminlogdata);
    }

    // private function importStock($filename, $content, $conn, $adminemail)
    // {
    //     $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/importStock.log');
    //     $logger = new \Zend\Log\Logger();
    //     $logger->addWriter($writer);
    //     $adminreceiver = 'default@default.com';
    //     $adminlogdata = 0;
    //     if (isset($adminemail)) {
    //         $adminreceiver = $adminemail;
    //     }

    //     if (!$content) {
    //         $logger->info("content is empty");
    //         return;
    //     }

    //     $logger->info('Import started at ' . Date("Y-m-d H:i:s"));

    //     $productData = json_decode($content, true);

    //     if (empty($productData)) {
    //         return;
    //     }

    //     $logCounter = 0;
    //     $adminLogCounter = 0;

    //     foreach ($productData as $product) {
    //         if ($product) {
    //             try {
    //                 $sku = $product['acIdent'];
    //                 $qty = (int) $product['anStock'];

    //                 // Load the product by SKU
    //                 $existingProduct = $this->loadMyProduct($sku);

    //                 if ($existingProduct && $qty >= 0) {
    //                     // Update the product stock data
    //                     $stockData = [
    //                         'use_config_manage_stock' => 0,
    //                         'manage_stock' => 1,
    //                         'is_in_stock' => $qty > 0 ? 1 : 0,
    //                         // Set is_in_stock based on the quantity
    //                         'qty' => $qty
    //                     ];

    //                     $existingProduct->setStockData($stockData);
    //                     $existingProduct->save();

    //                     $logger->info(++$logCounter . ' SKU: ' . $sku . ', New Quantity: ' . $qty);
    //                 }
    //             } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    //                 $logger->info(++$logCounter . ' SKU ' . $sku . ' does not exist.');
    //             } catch (\Exception $e) {
    //                 $logger->info(++$logCounter . ' SKU ' . $sku . ': ' . $e->getMessage());
    //             }
    //         }
    //     }

    //     $logger->info('Import ended at ' . Date("Y-m-d H:i:s"));

    //     $this->MoveReadFile($filename, $conn, $adminreceiver, $adminlogdata);
    // }

    private function getFTPFileData($checkisFilename)
    {
        $path = $this->helperData->getGeneralStock('stock_directoryStock');
        $host = $this->helperData->getGeneralStock('display_hostStock');
        $user = $this->helperData->getGeneralStock('display_usernameStock');
        $pwd = $this->helperData->getGeneralStock('display_passwordStock');
        $filename = "";
        if ($checkisFilename) {
            $filename = $this->helperData->getGeneralStock('stock_txtfileStock');
        }
        $port = $this->helperData->getGeneralStock('display_portStock');
        $reportReceiver = $this->helperData->getGeneralStock('display_reportreceiverStock');

        // echo $port;
        // exit;

        try {
            $open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => true));

            if ($open) {
                $content = [];
                $HistoryFound = 1;
                if ($filename != "") {
                    $path = $this->ftp->cd("/$path/");

                    $list = $this->ftp->ls();
                    foreach ($list as $k1 => $v1) {

                        if ($v1['text'] == $filename) {
                            $content[$v1['text']] = $this->ftp->read($v1['text']);
                        }
                    }
                } else {
                    $path = $this->ftp->cd("/$path/");
                    $list = $this->ftp->ls();
                    // echo "<pre>";print_r($list);die(); 
                    $filename = [];
                    foreach ($list as $k1 => $v1) {
                        if ($v1['text'] != "History") {
                            /*Remove currepted data from file*/
                            if (strpos($v1['text'], 'Stock_DIFF') !== false) {
                                $datadd = trim($this->ftp->read($filename[] = $v1['text']));
                                $datadd = utf8_decode($datadd);
                                $datadd = str_replace('??', '', $datadd);
                                $datadd = stripslashes(html_entity_decode($datadd));
                                // echo "<pre>";print_r($list);die(); 

                                // $product_Data = json_decode($content);

                                // print_r($product_Data);

                                $content[$v1['text']] = $datadd;
                                // $content[$v1['text']] = $this->ftp->read($filename[] = $v1['text']);
                            }
                            if (strpos($v1['text'], 'Stock_FULL') !== false) {
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
    private function MoveReadFile($filename, $conn, $adminreceiver, $adminlogdata)
    {
        $path = $this->helperData->getGeneralStock('stock_directoryStock');
        $host = $this->helperData->getGeneralStock('display_hostStock');
        ;
        $user = $this->helperData->getGeneralStock('display_usernameStock');
        $pwd = $this->helperData->getGeneralStock('display_passwordStock');
        $port = $this->helperData->getGeneralStock('display_portStock');

        try {
            $open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => true));
            if ($open) {
                $conn->cd("/$path/");
                $content = $conn->mv($filename, "History/" . $filename);
                //$this->sendEmail($adminreceiver, $log_file_name, $adminlogdata);
                $this->ftp->close();
            }
        } catch (Exception $e) {
            $this->ftp->close();
            return false;
        }
    }
    private function loadMyProduct($sku)
    {
        return $this->productRepository->get($sku);
    }
    private function sendEmail($adminreceiver, $log_file_name, $adminlogdata)
    {
        if ($adminlogdata == 0) {
            return;
        }
        // this is an example and you can change template id,fromEmail,toEmail,etc as per your need.
        $templateId = 1; // template id
        $fromEmail = 'owner@domain.com'; // sender Email id
        $fromName = 'Admin'; // sender Name
        $toEmail = $adminreceiver; // receiver email id

        try {
            // template variables pass here
            $templateVars = [
                'msg' => file_get_contents('adminlogdata.txt')
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
