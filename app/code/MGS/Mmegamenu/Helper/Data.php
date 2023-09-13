<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mmegamenu\Helper;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Framework\Registry $registry
     */

    protected $_registry;

    protected $_objectManager;
    
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectmanager;
    }
    
	public function getStoreConfig($node){
		return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
    
	public function getCurentCateId(){
        $category = $this->_registry->registry('current_category');
        return $category;
	}

    public function getObjectManager(){
        return $this->_objectManager;
    }

    public function decodeHtmlTag($content){
		$result = str_replace("&lt;","<",$content);
		$result = str_replace("&gt;",">",$result);
		$result = str_replace('&#34;','"',$result);
		$result = str_replace("&#39;","'",$result);
		return $result;
	}
	
}