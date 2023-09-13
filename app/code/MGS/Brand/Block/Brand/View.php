<?php

namespace MGS\Brand\Block\Brand;

class View extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_catalogLayer;
    protected $_brandHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \MGS\Brand\Helper\Data $brandHelper,
        array $data = []
    )
    {
        $this->_brandHelper = $brandHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _addBreadcrumbs()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $brandRoute = $this->_brandHelper->getConfig('general_settings/route');
        $pageTitle = $this->_brandHelper->getConfig('list_page_settings/title');
        $brand = $this->getCurrentBrand();
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        $breadcrumbs->addCrumb(
            'mgs_brand',
            [
                'label' => $pageTitle,
                'title' => $pageTitle,
                'link' => $baseUrl . $brandRoute
            ]
        );
        $breadcrumbs->addCrumb(
            'brand',
            [
                'label' => $brand->getName(),
                'title' => $brand->getName(),
                'link' => ''
            ]
        );
    }

    public function getCurrentBrand()
    {
        $brand = $this->_coreRegistry->registry('current_brand');
        if ($brand) {
            $this->setData('current_brand', $brand);
        }
        return $brand;
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_brandHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    protected function _prepareLayout()
    {
        $brand = $this->getCurrentBrand();
        $pageTitle = $brand->getName();
        $metaKeywords = $brand->getMetaKeywords();
        $metaDescription = $brand->getMetaDescription();
        $this->_addBreadcrumbs();
        if ($pageTitle) {
            $this->pageConfig->getTitle()->set($pageTitle);
        }
        if ($metaKeywords) {
            $this->pageConfig->setKeywords($metaKeywords);
        }
        if ($metaDescription) {
            $this->pageConfig->setDescription($metaDescription);
        }
        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}