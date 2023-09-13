<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\StoreLocator\Helper;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $scopeConfig;
	
	protected $_storeManager;
	
	public function __construct(
		\Magento\Framework\View\Element\Context $context, 
		\Magento\Store\Model\StoreManagerInterface $storeManager
	)
    {
        $this->scopeConfig = $context->getScopeConfig();
		$this->_storeManager = $storeManager;
    }
	
	public function getStore(){
		return $this->_storeManager->getStore();
	}
	
	/* Get system store config */
	public function getStoreConfig($node, $storeId = NULL){
		if($storeId != NULL){
			return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
		}
		return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
	}
}