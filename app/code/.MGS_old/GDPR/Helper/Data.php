<?php

namespace MGS\GDPR\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $storeManager;
	
	protected $_url;
	
	protected $_pageFactory;
	
	protected $_filterProvider;

    public function __construct(
        Context $context,
		\Magento\Framework\Url $url,
		\Magento\Cms\Model\PageFactory $pageFactory,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
		$this->_url = $url;
		$this->_pageFactory = $pageFactory;
		$this->_filterProvider = $filterProvider;
        parent::__construct($context);
    }

    public function getStoreConfig($path, $store = null)
    {
        if ($store == null || $store == '') {
            $store = $this->storeManager->getStore()->getId();
        }
        $store = $this->storeManager->getStore($store);
        $config = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $store);
        return $config;
    }
	
	public function getUrlBuilder($identifier){
		return $this->_url->getUrl($identifier);
	}
	
	function getPageContent($identifier){
		$page = $this->_pageFactory->create()->load($identifier);
		return $this->_filterProvider->getPageFilter()->filter($page->getContent());
	}

}