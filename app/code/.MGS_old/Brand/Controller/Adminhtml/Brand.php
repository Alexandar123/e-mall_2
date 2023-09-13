<?php

namespace MGS\Brand\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\LayoutFactory;

abstract class Brand extends \Magento\Backend\App\Action
{
    protected $_coreRegistry = null;
    protected $layoutFactory;
    protected $_fileFactory;
    protected $_viewHelper;
    protected $resultLayoutFactory;
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \MGS\Brand\Helper\Data $viewHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_viewHelper = $viewHelper;
        $this->layoutFactory = $layoutFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('MGS_Brand::manage_brand')->_addBreadcrumb(__('Shop By Brand'), __('Shop By Brand'))->_addBreadcrumb(__('Manage Brands'), __('Manage Brands'));
        return $this;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Brand::brand');
    }
}
