<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Panel;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Main contact form block
 */
class Toppanel extends Template
{
    
    protected $helper;
    
    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    
    public function __construct(
        Template\Context $context,
        \MGS\Fbuilder\Helper\Builder $helper,
        \Magento\Cms\Model\PageFactory $pageFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = false;
        $this->_pageFactory = $pageFactory;
        $this->helper = $helper;
    }
    
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    
    public function getCmsPageId()
    {
        if ($this->getRequest()->getFullActionName()=='cms_index_index') {
            $pageIdentifier = $this->helper->getStoreConfig('web/default/cms_home_page', $this->_storeManager->getStore()->getId());
            $arrIdentifier = explode('|', $pageIdentifier);

            $page = $this->_pageFactory->create()->setStoreId($this->_storeManager->getStore()->getId())->load($arrIdentifier[0]);
            return $page->getId();
        } elseif ($this->getRequest()->getFullActionName()=='catalog_product_view') {
            return $this->getRequest()->getParam('id');
        } else {
            return $this->getRequest()->getParam('page_id');
        }
    }
    
    public function getHelper()
    {
        return $this->helper;
    }
    
    /**
     * Returns customer id from session
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->helper->getCustomerId();
    }
    
    public function getCustomer()
    {
        return $this->helper->getCustomer();
    }

    protected function getCacheLifetime()
    {
        return null;
    }
    
    public function getPublishUrl()
    {
        if ($this->getRequest()->getFullActionName()=='catalog_product_view') {
            return $this->getUrl('fbuilder/index/publish', ['store_id'=>$this->getStoreId(),'product_id'=>$this->getRequest()->getParam('id'),'referrer'=>$this->helper->getCurrentBase64Url()]);
        } elseif ($this->getRequest()->getFullActionName()=='catalog_category_view') {
            return $this->getUrl('fbuilder/index/publish', ['store_id'=>$this->getStoreId(),'category_id'=>$this->getRequest()->getParam('id'),'referrer'=>$this->helper->getCurrentBase64Url()]);
        } else {
            return $this->getUrl('fbuilder/index/confirm', ['store_id'=>$this->getStoreId(),'page_id'=>$this->getCmsPageId(), 'page'=>$this->getRequest()->getFullActionName(),'referrer'=>$this->helper->getCurrentBase64Url()]);
        }
    }

    // protected function _toHtml()
    // {$pageName = $this->getRequest()->getFullActionName();
    //     if($pageName == 'cms_index_index' || $pageName == 'cms_page_view' || $pageName == 'catalog_category_view' || $pageName == 'catalog_product_view'){
    //         return parent::_toHtml();
    //     }
    //     return '';
    // }
}
