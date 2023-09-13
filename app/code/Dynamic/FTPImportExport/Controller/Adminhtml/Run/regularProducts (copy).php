<?php

/**
 * * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dynamic\FTPImportExport\Controller\Adminhtml\Run;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Tax\Model\TaxClass\Source\Product as ProductTaxClassSource;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Paypal\Block\Payflow\Link\Iframe;
use Magento\Store\Model\StoreManagerInterface;

class RegularProducts extends \Magento\Backend\App\Action
{
	protected $logger;
	private $productRepository;
	protected $helperData;
	protected $categoryFactory;
	protected $product;
	protected $ftp;
	protected $productTaxClassSource;
	protected $transportBuilder;
	protected $storeManager;
	protected $inlineTranslation;
	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param JsonFactory $resultJsonFactory
	 */
	public function __construct(
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Backend\App\Action\Context $context,
		\Dynamic\FTPImportExport\Helper\Data $helperData,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Catalog\Model\Product $product,
		\Magento\Framework\Filesystem\Io\Ftp $ftp,
		ProductTaxClassSource $productTaxClassSource,
		\Psr\Log\LoggerInterface $logger,
		TransportBuilder $transportBuilder,
		StoreManagerInterface $storeManager,
		StateInterface $state,
		\Magento\Framework\Message\ManagerInterface $messageManager
	) {
		$this->productRepository = $productRepository;
		$this->helperData = $helperData;
		$this->categoryFactory = $categoryFactory;
		$this->_product = $product;
		$this->ftp = $ftp;
		$this->productTaxClassSource = $productTaxClassSource;
		$this->logger = $logger;
		$this->transportBuilder = $transportBuilder;
		$this->storeManager = $storeManager;
		$this->inlineTranslation = $state;
		$this->messageManager = $messageManager;
		return parent::__construct($context);
	}

	/**
	 *
	 * @return \Magento\Framework\Controller\Result\Json
	 */
	public function execute()
	{
		$Filelines = $this->getFTPFileData();

		// echo "<pre>";
		// print_r($Filelines);
		// exit;
		if ($Filelines) {
			$adminreceiver = 'default@default.com';
			$adminlogdata = 0;
			if (isset($Filelines['adminemail'])) {
				$adminreceiver = $Filelines['adminemail'];
			}
			if (isset($Filelines['log_file_name'])) {
				$log_file_name = $Filelines['log_file_name'];
			} else {
				$log_file_name = explode(".", $Filelines['filename'])[0] . "_" . date('YmdHis') . "_log.txt";
			}
			if (!$Filelines['lines']) {

				file_put_contents($log_file_name, 'System did not find data to import' . $Filelines['filename'], FILE_APPEND);
				return;
			}

			file_put_contents($log_file_name, 'Import started at ' . Date("Y-m-d H:i:s") . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

			$taxId = -1;
			$taxClassess = $this->productTaxClassSource->getAllOptions();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
			foreach ($taxClassess as $k => $v) {
				if ($v['label'] == 'Taxable Goods') {
					$taxId = $v['value'];
				}
			}

			$product_Data = json_decode($Filelines['lines']);

			$log_counter = 0;
			$admin_log_counter = 0;

			foreach ($product_Data as $k => $y) {
				$product = get_object_vars($y);

				if ($product) {
					$adminreceiver = 'default@default.com';
					$adminlogdata = 0;
					if (isset($Filelines['adminemail'])) {
						$adminreceiver = $Filelines['adminemail'];
					}
					if (isset($Filelines['log_file_name'])) {
						$log_file_name = $Filelines['log_file_name'];
					} else {
						$log_file_name = explode(".", $Filelines['filename'])[0] . "_" . date('YmdHis') . "_log.txt";
					}
					if (!$Filelines['lines']) {

						file_put_contents($log_file_name, 'System did not find data to import' . $Filelines['filename'], FILE_APPEND);
						return;
					}

					file_put_contents($log_file_name, 'Import started at ' . Date("Y-m-d H:i:s") . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

					$taxId = -1;
					$taxClassess = $this->productTaxClassSource->getAllOptions();
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
					foreach ($taxClassess as $k => $v) {
						if ($v['label'] == 'Taxable Goods') {
							$taxId = $v['value'];
						}
					}

					$product_Data = json_decode($Filelines['lines']);

					$log_counter = 0;
					$admin_log_counter = 0;

					foreach ($product_Data as $k => $y) {
						$product = get_object_vars($y);

						//foreach ($product_array as $key => $product) {
						// echo "<pre>";
						// print_r($product['SalePrice']);
						if (empty($product['acIdent']) || empty($product['SalePrice'])) {
							continue;
						} //SKU and price should not be empty
						if (empty($product['acName'])) {
							continue;
						} //Name should not be empty
						$product_active_status = 0;
						if ($product['IsActiveProduct'] == "T") {
							$product_active_status = 1;
						}

						try {
							if ($this->_product->getIdBySku($product['acIdent'])) {
								//echo "exits";
								/*********************Editing of existing product*************************/
								$existingProduct = $this->loadMyProduct($product['acIdent']);

								try {
									$existingProduct->setName(trim($product['acName']));
									$existingProduct->setEanCode($product['acCode']);
									$existingProduct->setPrice($product['SalePrice']);
									$existingProduct->setStatus($product_active_status);
									$existingProduct->setWeight($product['acUM']);
									$existingProduct->setStoreId(0);
									$existingProduct->save();

									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Updated.. ;' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								} catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

									$existingProduct->setUrlKey($product['acName'] . rand(100, 1000));
									$existingProduct->setStoreId(0);
									$existingProduct->save();

									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED but product url edited and imported again  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

									$adminlogdata = 1;
									file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								} catch (\Magento\Framework\Exception\RuntimeException $e) {
									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

									$adminlogdata = 1;
									file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								} catch (\Exception $e) {
									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Unhandeled error occur  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

									$adminlogdata = 1;
									file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' Unhandeled error occur  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								}

								/*********************Editing of existing product*************************/
							} else {
								//echo "new";
								/*********************Adding of new product*************************/
								try {
									$new_product = $objectManager->create('\Magento\Catalog\Model\Product');
									$new_product->setSku($product['acIdent']); // Set your sku here
									$new_product->setName(trim($product['acName'])); // Name of new_product
									$new_product->setPrice($product['SalePrice']); // price of new_product
									$new_product->setEanCode($product['acCode']);
									$new_product->setVisibility(4);
									$new_product->setWeight($product['acUM']);
									$new_product->setWebsiteIds(array(1));
									$new_product->setStoreId(0);
									$new_product->setAttributeSetId(4); // Attribute set id
									$new_product->setStatus($product_active_status); // Status on 
									$taxId ? $new_product->setTaxClassId($taxId) : $new_product->setTaxClassId(0); // Tax class id
									$new_product->setTypeId('simple'); // type of new_product (simple/virtual/downloadable/configurable)
									$new_product->setStockData(array('use_config_manage_stock' => 0, 'manage_stock' => 1, 'is_in_stock' => 1, 'qty' => 999));

									$new_product->save();

									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Added.. ;' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								} catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED  (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
									$new_product->setUrlKey($DerivedProName . rand(100, 1000));
									$new_product->setStoreId(0);
									$new_product->save();

									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED but new_product url edited and imported again (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
									$adminlogdata = 1;

									file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								} catch (\Magento\Framework\Exception\RuntimeException $e) {
									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
									$adminlogdata = 1;

									file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								} catch (\Exception $e) {
									file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Unhandeled error occur (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
									$adminlogdata = 1;

									file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' Unhandeled error occur (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								}
								/*********************Adding of new new_product*************************/
							}
						} catch (\Exception $e) {
							echo 'Please check format of data';
						}
						//}
					}
					//exit;
					file_put_contents($log_file_name, 'Import ended at ' . Date("Y-m-d H:i:s") . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

					$MoveFileToHistory = $this->MoveReadFile($Filelines['filename'], $Filelines['conn'], $log_file_name, $adminreceiver, $adminlogdata);
					//foreach ($product_array as $key => $product) {
					// echo "<pre>";
					// print_r($product['SalePrice']);
					if (empty($product['acIdent']) || empty($product['SalePrice'])) {
						continue;
					} //SKU and price should not be empty
					if (empty($product['acName'])) {
						continue;
					} //Name should not be empty
					$product_active_status = 0;
					if ($product['IsActiveProduct'] == "T") {
						$product_active_status = 1;
					}

					try {
						if ($this->_product->getIdBySku($product['acIdent'])) {
							//echo "exits";
							/*********************Editing of existing product*************************/
							$existingProduct = $this->loadMyProduct($product['acIdent']);

							try {
								$existingProduct->setName(trim($product['acName']));
								$existingProduct->setEanCode($product['acCode']);
								$existingProduct->setPrice($product['SalePrice']);
								$existingProduct->setStatus($product_active_status);
								$existingProduct->setWeight($product['acUM']);
								$existingProduct->setStoreId(0);
								$existingProduct->save();

								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Updated.. ;' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							} catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

								$existingProduct->setUrlKey($product['acName'] . rand(100, 1000));
								$existingProduct->setStoreId(0);
								$existingProduct->save();

								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED but product url edited and imported again  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

								$adminlogdata = 1;
								file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							} catch (\Magento\Framework\Exception\RuntimeException $e) {
								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

								$adminlogdata = 1;
								file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							} catch (\Exception $e) {
								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Unhandeled error occur  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

								$adminlogdata = 1;
								file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' Unhandeled error occur  (update mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							}

							/*********************Editing of existing product*************************/
						} else {
							//echo "new";
							/*********************Adding of new product*************************/
							try {
								$new_product = $objectManager->create('\Magento\Catalog\Model\Product');
								$new_product->setSku($product['acIdent']); // Set your sku here
								$new_product->setName(trim($product['acName'])); // Name of new_product
								$new_product->setPrice($product['SalePrice']); // price of new_product
								$new_product->setEanCode($product['acCode']);
								$new_product->setVisibility(4);
								$new_product->setWeight($product['acUM']);
								$new_product->setWebsiteIds(array(1));
								$new_product->setStoreId(0);
								$new_product->setAttributeSetId(4); // Attribute set id
								$new_product->setStatus($product_active_status); // Status on 
								$taxId ? $new_product->setTaxClassId($taxId) : $new_product->setTaxClassId(0); // Tax class id
								$new_product->setTypeId('simple'); // type of new_product (simple/virtual/downloadable/configurable)
								$new_product->setStockData(array('use_config_manage_stock' => 0, 'manage_stock' => 1, 'is_in_stock' => 1, 'qty' => 999));

								$new_product->save();

								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Added.. ;' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							} catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED  (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								$new_product->setUrlKey($DerivedProName . rand(100, 1000));
								$new_product->setStoreId(0);
								$new_product->save();

								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED but new_product url edited and imported again (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								$adminlogdata = 1;

								file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							} catch (\Magento\Framework\Exception\RuntimeException $e) {
								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								$adminlogdata = 1;

								file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							} catch (\Exception $e) {
								file_put_contents($log_file_name, ++$log_counter . ' : ' . $product['acIdent'] . ' Unhandeled error occur (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
								$adminlogdata = 1;

								file_put_contents('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' Unhandeled error occur (adding mode);' . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);
							}
							/*********************Adding of new new_product*************************/
						}
					} catch (\Exception $e) {
						echo 'Please check format of data';
					}
				}
			}
			//exit;
			file_put_contents($log_file_name, 'Import ended at ' . Date("Y-m-d H:i:s") . PHP_EOL . '------------------------------------------------------' . PHP_EOL, FILE_APPEND);

			$MoveFileToHistory = $this->MoveReadFile($Filelines['filename'], $Filelines['conn'], $log_file_name, $adminreceiver, $adminlogdata);
		}
	}
	public function getFTPFileData()
	{
		$path = $this->helperData->getGeneralRegularProducts('product_directoryRP');
		$host = $this->helperData->getGeneralRegularProducts('display_hostRP');;
		$user = $this->helperData->getGeneralRegularProducts('display_usernameRP');
		$pwd = $this->helperData->getGeneralRegularProducts('display_passwordRP');
		$filename = $this->helperData->getGeneralRegularProducts('product_txtfileRP');
		$port = $this->helperData->getGeneralRegularProducts('display_portRP');
		$reportReceiver = $this->helperData->getGeneralRegularProducts('display_reportreceiverRP');

		try {
			$open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => false));

			// echo $open;
			// exit;

			if ($open) {
				$content = "";
				$HistoryFound = 1;
				if ($filename != "") {
					$path = $this->ftp->cd("/$path/");

					$list = $this->ftp->ls();
					foreach ($list as $k1 => $v1) {

						if ($v1['text'] == $filename) {
							$content = $this->ftp->read($v1['text']);
						}
					}
				} else {
					$path = $this->ftp->cd("/$path/");
					$list = $this->ftp->ls(); //echo "<pre>";print_r($list);die(); 
					foreach ($list as $k1 => $v1) {
						if ($v1['text'] != "History") {
							$content = $this->ftp->read($filename = $v1['text']);
						}
						if ($v1['text'] == "History") {
							$HistoryFound = 0;
						}
					}
				}
				if ($HistoryFound) {
					$this->ftp->mkdir("History");
				}

				if ($content == '') {
					return array('lines' => array(), 'filename' => '', 'conn' => $this->ftp);
				}

				$this->ftp->close();

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
	public function MoveReadFile($filename, $conn, $log_file_name, $adminreceiver, $adminlogdata)
	{
		$path = $this->helperData->getGeneralRegularProducts('product_directoryRP');
		$host = $this->helperData->getGeneralRegularProducts('display_hostRP');;
		$user = $this->helperData->getGeneralRegularProducts('display_usernameRP');
		$pwd = $this->helperData->getGeneralRegularProducts('display_passwordRP');
		$port = $this->helperData->getGeneralRegularProducts('display_portRP');

		try {
			$open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => false));
			if ($open) {
				$conn->cd("/$path/");
				$content = $conn->mv($filename, "History/" . $filename);
				$logname = '';
				$r = explode("/", $log_file_name);
				$logname = end($r);
				$content = $conn->write("History/" . $logname, $log_file_name);
				//$this->sendEmail($adminreceiver, $log_file_name, $adminlogdata);
				$this->ftp->close();
			}
		} catch (Exception $e) {
			$this->ftp->close();
			return false;
		}
	}
	public function loadMyProduct($sku)
	{
		return $this->productRepository->get($sku);
	}
	public function sendEmail($adminreceiver, $log_file_name, $adminlogdata)
	{
		if ($adminlogdata == 0) {
			return;
		}
		// this is an example and you can change template id,fromEmail,toEmail,etc as per your need.
		$templateId = 1; // template id
		$fromEmail = 'owner@domain.com';  // sender Email id
		$fromName = 'Admin';             // sender Name
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
