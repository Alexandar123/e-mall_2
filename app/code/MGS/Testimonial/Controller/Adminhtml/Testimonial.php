<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Testimonial\Controller\Adminhtml;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use MGS\Mmegamenu\Helper\Data;
use Magento\Framework\View\LayoutFactory;
use \Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;

abstract class Testimonial extends \Magento\Backend\App\Action
{
    protected $_coreRegistry = null;
    protected $layoutFactory;
    protected $_fileFactory;
    protected $_viewHelper;
    protected $resultLayoutFactory;
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        Data $viewHelper,
        LayoutFactory $layoutFactory,
        ResultLayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory
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

	/**
     * Init actions
     *
     * @return $this
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'MGS_Testimonial::testimonial_manage'
        )->_addBreadcrumb(
            __('Testimonial'),
            __('Testimonial')
        );
        return $this;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Testimonial::testimonial');
    }
}
