<?php

namespace Dynamic\FTPImportExport\Helper;

use Exception;
use Magento\Tax\Model\TaxClass\Source\Product as ProductTaxClassSource;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\CategoryProductLinkInterface;
use Magento\Framework\DataObject;

class ImportProduct extends AbstractHelper
{
	protected $ftp;
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Dynamic\FTPImportExport\Helper\Data $helperData,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categorycollectionFactory,
		\Magento\Catalog\Model\Product $product,
		\Magento\Framework\Filesystem\Io\Ftp $ftp,
		ProductTaxClassSource $productTaxClassSource,
		\Psr\Log\LoggerInterface $logger,
		TransportBuilder $transportBuilder,
		StoreManagerInterface $storeManager,
		StateInterface $state,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		CategoryLinkManagementInterface $categoryLinkManagement
	) {
		$this->productRepository = $productRepository;
		$this->helperData = $helperData;
		$this->categorycollectionFactory = $categorycollectionFactory;
		$this->_product = $product;
		$this->ftp = $ftp;
		$this->productTaxClassSource = $productTaxClassSource;
		$this->logger = $logger;
		$this->transportBuilder = $transportBuilder;
		$this->storeManager = $storeManager;
		$this->inlineTranslation = $state;
		$this->messageManager = $messageManager;
		$this->categoryLinkManagement = $categoryLinkManagement;
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
				$this->importProduct($key, $value, $Filelines['conn'], $Filelines['adminemail']);
			}
		}
	}

	private function importProduct($filename, $content, $conn, $adminemail)
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/importProduct.log');
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
		$taxId = -1;
		$taxClassess = $this->productTaxClassSource->getAllOptions();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
		foreach ($taxClassess as $k => $v) {
			if ($v['label'] == 'Taxable Goods') {
				$taxId = $v['value'];
			}
		}

		// Log the JSON data before decoding
		$logger->info('JSON data before decoding: ' . $content);
		// Attempt to decode the JSON data
		$product_Data = $this->getProductData($content, $logger, $filename);

		if (empty($product_Data)) {
			return;
		}
		$log_counter = 0;
		$admin_log_counter = 0;
		// print_r($product_Data);die;

		foreach ($product_Data as $k => $product) {
			// $product = get_object_vars($y);

			if ($product) {
				//foreach ($product_array as $key => $product) {
				// echo "<pre>";
				// print_r($product['SalePrice']);
				//acIdent, SKU and price should not be empty
				if (empty($product['acIdent']) || empty($product['SalePrice']) || empty($product['acName'])) {
					continue;
				}
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
							$categoryAclassicUniqueId = array();
							if (isset($product['acClassif'])) {
								$categoryAclassicUniqueId[] = $product['acClassif'];
							}
							if (isset($product['acCLassif2'])) {
								$categoryAclassicUniqueId[] = $product['acCLassif2'];
							}

							$existingProduct->setName(trim($product['acName']));
							$existingProduct->setEanCode($product['acCode']);
							$existingProduct->setPrice($product['SalePrice']);
							$existingProduct->setStatus($product_active_status);
							$existingProduct->setWeight($product['acUM']);
							$existingProduct->setStoreId(0);
							$existingProduct->save();

							$this->assignedProductToCategory($product['acIdent'], $categoryAclassicUniqueId);
							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . ' Updated.. ;');
						} catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {

							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED (update mode);');

							$existingProduct->setUrlKey($product['acName'] . rand(100, 1000));
							$existingProduct->setStoreId(0);
							$existingProduct->save();
							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED but product url edited and imported again  (update mode);');
						} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED  (update mode);');

							$adminlogdata = 1;
							$logger->info(++$admin_log_counter . ' : ' . $product['acIdent'] . ' SKU linking ERROR HAPPENED  (update mode);');
						} catch (\Magento\Framework\Exception\RuntimeException $e) {

							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED  (update mode);');

							$adminlogdata = 1;
							$logger->info('adminlogdata.txt', ++$admin_log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED  (update mode);');
						} catch (\Exception $e) {
							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . $e->getMessage());
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
							// $new_product->setWeight($product['acUM']);
							$new_product->setWebsiteIds(array(1));
							$new_product->setStoreId(0);
							$new_product->setAttributeSetId(4); // Attribute set id
							$new_product->setStatus($product_active_status); // Status on 
							$taxId ? $new_product->setTaxClassId($taxId) : $new_product->setTaxClassId(0); // Tax class id
							$new_product->setTypeId('simple'); // type of new_product (simple/virtual/downloadable/configurable)
							$new_product->setStockData(array('use_config_manage_stock' => 0, 'manage_stock' => 1, 'is_in_stock' => 0, 'qty' => 0));
							$new_product->save();

							$categoryAclassicUniqueId = array();
							if (isset($product['acClassif'])) {
								$categoryAclassicUniqueId[] = $product['acClassif'];
							}
							if (isset($product['acCLassif2'])) {
								$categoryAclassicUniqueId[] = $product['acCLassif2'];
							}
							$this->assignedProductToCategory($product['acIdent'], $categoryAclassicUniqueId);

							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . ' Added.. ;');
						} catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {

							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED  (adding mode);');
							//$new_product->setUrlKey($DerivedProName . rand(100, 1000));
							$new_product->setStoreId(0);
							$new_product->save();

							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . ' URL rewrite ERROR HAPPENED but new_product url edited and imported again (adding mode);');
						} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . 'SKU linking ERROR HAPPENED (adding mode);');
							$adminlogdata = 1;
						} catch (\Magento\Framework\Exception\RuntimeException $e) {

							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . ' Runtime exception ERROR HAPPENED (adding mode);');
						} catch (\Exception $e) {
							$logger->info(++$log_counter . ' : ' . $product['acIdent'] . $e->getMessage());
						}
						/*********************Adding of new new_product*************************/
					}
				} catch (\Exception $e) {
					$logger->error('Please check format of data');
					print_r($e->getMessage());
				}
				//}
			}
		}
		//exit;

		$logger->info('Import ended at ' . Date("Y-m-d H:i:s"));

		$MoveFileToHistory = $this->MoveReadFile($filename, $conn, $adminreceiver, $adminlogdata);
	}

	private function cleanJsonData($content)
	{
		// Remove control characters
		$content = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content);
	
		// Unescape escaped double quotes (\" -> ")
		$content = str_replace('\\"', '"', $content);
	
		return $content;
	}

	private function getProductData($content, $logger, $filename)
	{
		// Log the JSON data before decoding
		$logger->info('JSON data before decoding: ' . $content);

		// Clean up the JSON data
		$cleaned_content = $this->cleanJsonData($content);

		// Decode JSON
		$product_Data = json_decode($cleaned_content, true);
		$jsonErrorCode = json_last_error();
		$jsonErrorMessage = json_last_error_msg();

		if ($jsonErrorCode !== JSON_ERROR_NONE) {
			// Handle JSON decoding error and provide detailed error information
			$message = "Filename: " . $filename . "\n Message: " . $jsonErrorCode . " : " . $jsonErrorMessage;
			$this->sendEmailWithError('JSON decoding error (Filename, Code, and message: ' . $message . ')');
			die;
		}
		return $product_Data;
	}




	private function getFTPFileData($checkisFilename)
	{
		$path = $this->helperData->getGeneralRegularProducts('product_directoryRP');
		$host = $this->helperData->getGeneralRegularProducts('display_hostRP');
		;
		$user = $this->helperData->getGeneralRegularProducts('display_usernameRP');
		$pwd = $this->helperData->getGeneralRegularProducts('display_passwordRP');
		$filename = "";
		if ($checkisFilename) {
			$filename = $this->helperData->getGeneralRegularProducts('product_txtfileRP');
		}
		$port = $this->helperData->getGeneralRegularProducts('display_portRP');
		$reportReceiver = $this->helperData->getGeneralRegularProducts('display_reportreceiverRP');

		try {
			$open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => true));

			// echo $open;
			// exit;

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
					$list = $this->ftp->ls(); //echo "<pre>";print_r($list);die(); 
					$filename = [];
					foreach ($list as $k1 => $v1) {
						if ($v1['text'] != "History") {
							/*Remove currepted data from file*/
							if (strpos($v1['text'], 'Products_DIFF') !== false) {

								$datadd = trim($this->ftp->read($filename[] = $v1['text']));
								$datadd = utf8_decode($datadd);
								$datadd = str_replace('??', '', $datadd);
								$datadd = stripslashes(html_entity_decode($datadd));


								// $product_Data = json_decode($content);

								// print_r($product_Data);

								$content[$v1['text']] = $datadd;
								// $content[$v1['text']] = $this->ftp->read($filename[] = $v1['text']);
							} elseif (strpos($v1['text'], 'Products_FULL') !== false) {
								$datadd = trim($this->ftp->read($filename[] = $v1['text']));
								$datadd = utf8_decode($datadd);
								$datadd = str_replace('??', '', $datadd);
								$datadd = stripslashes(html_entity_decode($datadd));

								// $product_Data = json_decode($content);

								// print_r($product_Data);

								$content[$v1['text']] = $datadd;
								// $content[$v1['text']] = $this->ftp->read($filename[] = $v1['text']);
							} elseif (strpos($v1['text'], 'Products_NEW') !== false) {
								$datadd = trim($this->ftp->read($filename[] = $v1['text']));
								$datadd = utf8_decode($datadd);
								$datadd = str_replace('??', '', $datadd);
								$datadd = stripslashes(html_entity_decode($datadd));

								// $product_Data = json_decode($content);

								// print_r($product_Data);

								$content[$v1['text']] = $datadd;
								// $content[$v1['text']] = $this->ftp->read($filename[] = $v1['text']);
							}

							if ($v1['text'] == "History") {
								$HistoryFound = 0;
							}
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
		$path = $this->helperData->getGeneralRegularProducts('product_directoryRP');
		$host = $this->helperData->getGeneralRegularProducts('display_hostRP');
		;
		$user = $this->helperData->getGeneralRegularProducts('display_usernameRP');
		$pwd = $this->helperData->getGeneralRegularProducts('display_passwordRP');
		$port = $this->helperData->getGeneralRegularProducts('display_portRP');

		try {
			$open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => true));
			if ($open) {
				$conn->cd("/$path/");
				$content = $conn->mv($filename, "History/" . $filename);
				$logname = '';

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

	private function sendEmailWithError($message)
	{
		$toEmail = 'alexandar.gordic@gmail.com'; // Receiver Email Address
		// Sender information
		$fromEmail = $this->scopeConfig->getValue('trans_email/ident_general/email');
		$fromName = $this->scopeConfig->getValue('trans_email/ident_general/name');

		try {
			$transport = $this->transportBuilder
				->setTemplateIdentifier(15)
				->setTemplateOptions(['area' => 'frontend', 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
				->setTemplateVars(['data' => new DataObject(['message' => $message])])
				->setFrom(['email' => $fromEmail, 'name' => $fromName])
				->addTo($toEmail)
				->getTransport();

			$transport->sendMessage();
		} catch (\Exception $e) {
			// Handle the exception
			echo $e->getMessage();
		}
	}

	/**
	 * Assigned Product to single/multiple Category
	 *
	 * @param string $productSku
	 * @param int[] $categoryIds
	 * @return bool
	 */
	public function assignedProductToCategory(string $productSku, array $categoryAclassicUniqueId)
	{
		$hasProductAssignedSuccess = false;
		try {
			$categoryIds = [];
			if ($categoryAclassicUniqueId) {
				$categories = $this->categorycollectionFactory->create()
					->addAttributeToSelect('*')->addAttributeToSelect('dd_acclassif');
				foreach ($categoryAclassicUniqueId as $value) {
					foreach ($categories as $category) {
						if ($category->getDdAcclassif() == $value) {
							$categoryIds[] = $category->getId();
						}
					}
				}
			}
			// echo "<pre>";
			// print_r($categoryAclassicUniqueId);
			// print_r($productSku);

			$hasProductAssignedSuccess = $this->categoryLinkManagement->assignProductToCategories($productSku, $categoryIds);
			//print_r($hasProductAssignedSuccess);

			//echo $hasProductAssignedSuccess;

			// exit;
		} catch (\Exception $exception) {
			throw new \Exception($exception->getMessage());
		}

		return $hasProductAssignedSuccess;
	}
}
