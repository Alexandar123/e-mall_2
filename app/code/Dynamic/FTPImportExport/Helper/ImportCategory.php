<?php

namespace Dynamic\FTPImportExport\Helper;

use Magento\Tax\Model\TaxClass\Source\Product as ProductTaxClassSource;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;


class ImportCategory extends AbstractHelper
{
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Filesystem\Io\Ftp $ftp,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Dynamic\FTPImportExport\Helper\Data $helperData
	) {
		$this->ftp = $ftp;
		$this->logger = $logger;
		$this->messageManager = $messageManager;
		$this->categoryFactory = $categoryFactory;
		$this->helperData = $helperData;
		return parent::__construct($context);
	}

	public function import()
	{

		$Filelines = $this->getFTPFileData();
		// echo "<pre>";
		// print_r($Filelines);
		// exit;
		if ($Filelines) {

			foreach ($Filelines['lines'] as $key => $value) {

				$this->importCategory($key, $value, $Filelines['conn'], $Filelines['adminemail']);
			}
		}
	}

	private function importCategory($filename, $content, $conn, $adminemail)
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/importCategory.log');
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

		$category_Data = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content), true);
		if (empty($category_Data)) {
			return;
		}
		$log_counter = 0;
		$admin_log_counter = 0;

		$parentId = 2;
		$parentCategory = $this->categoryFactory->create()->load($parentId);

		foreach ($category_Data as $k => $v) {
			$category = $this->categoryFactory->create();
			$catName = $v['acName'];
			$cat = $category->getCollection()
				->addAttributeToFilter('name', $catName)
				->getFirstItem();

			if (!$cat->getId()) {

				if (empty($catName)) {
					continue;
				}
				try {

					$category->setPath($parentCategory->getPath())
						->setParentId($parentId)
						->setName($catName)
						->setDdAnqid($v['anQId'])
						->setDdAcclassif($v['acClassif'])
						->setIsActive(true);
					$category->save();
				} catch (\Exception $e) {
					$logger->info('Import started at ' . $e->getMessage());
				}
			}
		}
		$logger->info('Import ended at ' . Date("Y-m-d H:i:s"));

		$MoveFileToHistory = $this->MoveReadFile($filename, $conn, $adminreceiver, $adminlogdata);
	}
	private function MoveReadFile($filename, $conn, $adminreceiver, $adminlogdata)
	{
		$path = $this->helperData->getGeneralCategoryInfo('category_directoryRC');
		$host = $this->helperData->getGeneralCategoryInfo('display_hostRC');;
		$user = $this->helperData->getGeneralCategoryInfo('display_usernameRC');
		$pwd = $this->helperData->getGeneralCategoryInfo('display_passwordRC');
		$port = $this->helperData->getGeneralCategoryInfo('display_portRC');
		$reportReceiver = $this->helperData->getGeneralCategoryInfo('display_reportreceiverRC');
		//echo "need to move this file --".$filename;exit;
		try {
			$open = $this->ftp->open(array('port' => $port, 'host' => $host, 'user' => $user, 'password' => $pwd, 'ssl' => false, 'passive' => true));
			if ($open) {
				$conn->cd("/$path/");
				$content = $conn->mv($filename, "History/" . $filename);
				$this->ftp->close();
			}
		} catch (Exception $e) {
			$this->ftp->close();
			return false;
		}
	}
	private function getFTPFileData()
	{
		$path = $this->helperData->getGeneralCategoryInfo('category_directoryRC');
		$host = $this->helperData->getGeneralCategoryInfo('display_hostRC');;
		$user = $this->helperData->getGeneralCategoryInfo('display_usernameRC');
		$pwd = $this->helperData->getGeneralCategoryInfo('display_passwordRC');
		$port = $this->helperData->getGeneralCategoryInfo('display_portRC');
		$reportReceiver = $this->helperData->getGeneralCategoryInfo('display_reportreceiverRC');

		$filename = $this->helperData->getGeneralCategoryInfo('category_txtfileRC');

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
					$list = $this->ftp->ls();
					// echo "<pre>";
					// print_r($list);
					// die();
					$filename = [];
					foreach ($list as $k1 => $v1) {
						if ($v1['text'] != "History") {
							if (strpos($v1['text'], 'Categories_DIFF') !== false) {
								/*Remove currepted data from file*/
								$datadd = trim($this->ftp->read($filename[] = $v1['text']));
								$datadd = utf8_decode($datadd);
								$datadd = str_replace('??', '', $datadd);
								$datadd = stripslashes(html_entity_decode($datadd));
								$content[$v1['text']] = $datadd;
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
				return array('lines' => $content, 'filename' => $filename, 'conn' => $this->ftp, 'adminemail' => $reportReceiver);
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
			$this->ftp->close();
			return false;
		} catch (LocalizedException $e) {
			$this->ftp->close();
			return false;
		}
	}
}
