<?php

namespace MGS\Brand\Controller\Brand;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use MGS\Brand\Model\Layer\Resolver;

class View extends \Magento\Framework\App\Action\Action
{
    protected $_request;
    protected $_response;
    protected $resultRedirectFactory;
    protected $resultFactory;
    protected $_brandModel;
    protected $_coreRegistry = null;
    private $layerResolver;
    protected $resultForwardFactory;
    protected $_brandHelper;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MGS\Brand\Model\Brand $brandModel,
        \Magento\Framework\Registry $coreRegistry,
        Resolver $layerResolver,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \MGS\Brand\Helper\Data $brandHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_brandModel = $brandModel;
        $this->layerResolver = $layerResolver;
        $this->_coreRegistry = $coreRegistry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_brandHelper = $brandHelper;
    }

    public function _initBrand()
    {
        $brandId = (int)$this->getRequest()->getParam('brand_id', false);
        if (!$brandId) {
            return false;
        }
        try {
            $brand = $this->_brandModel->load($brandId);
        } catch (\Exception $e) {
            return false;
        }
        $this->_coreRegistry->register('current_brand', $brand);
        return $brand;
    }

    public function execute()
    {
        if (!$this->_brandHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $brand = $this->_initBrand();
        if ($brand) {
            $this->layerResolver->create('brand');
            $page = $this->resultPageFactory->create();
            if ($this->_brandHelper->getConfig('view_page_settings/template')) {
                $page->getConfig()->setPageLayout($this->_brandHelper->getConfig('view_page_settings/template'));
            }
            $page->addHandle(['type' => 'MGS_BRAND_' . $brand->getId()]);
            if (($layoutUpdate = $brand->getLayoutUpdateXml()) && trim($layoutUpdate) != '') {
                $page->addUpdate($layoutUpdate);
            }
            $page->getConfig()->addBodyClass('page-products')
                ->addBodyClass('brand-' . $brand->getUrlKey());
            return $page;
        } elseif (!$this->getResponse()->isRedirect()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}