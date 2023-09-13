<?php

namespace MGS\Fbuilder\Block\Social;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Instagram extends Template
{
    
    protected $_generateHelper;
    protected $_storeManager;
    public function __construct(
        Context $context,
        \MGS\Fbuilder\Helper\Generate $_generateHelper
    ) {
        $this->_generateHelper = $_generateHelper;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context);
    }
    
    public function getStoreConfig($node, $storeId = null)
    {
        return $this->_generateHelper->getStoreConfig($node);
    }
    
    public function getWidgetInstagramData()
    {
        $result = [];
        $instagramToken = $this->getStoreConfig('fbuilder/social/instagram_access_token');
        $instagramData = $this->getStoreConfig('fbuilder/social/instagram_data');
        if ($instagramToken != '' && $instagramData != '') {
            return $instagramData;
        }
        
        return $result;
    }
}
