<?php

namespace Dynamic\OrderEmails\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
	const XML_PATH_REGULAR_PRODUCTS = 'regularProducts/';//regularProducts
	const XML_PATH_EXORDERS = 'exportOrder/';
	const XML_PATH_STOCK = 'regularStock/';
	const XML_PATH_CATEGORY = 'regularCategory/';
	const XML_PATH_REGULAR_CATEGORY = 'regularCategory/';//regularProducts

	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	public function getGeneralRegularProducts($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_REGULAR_PRODUCTS .'general/'. $code, $storeId);
	}
	public function getOrderExportData($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_EXORDERS .'general/'. $code, $storeId);
	}
	public function getGeneralStock($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_STOCK .'general/'. $code, $storeId);
	}
	public function getGeneralCategory($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_CATEGORY .'general/'. $code, $storeId);
	}
	public function getGeneralCategoryInfo($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_REGULAR_CATEGORY .'general/'. $code, $storeId);
	}
}