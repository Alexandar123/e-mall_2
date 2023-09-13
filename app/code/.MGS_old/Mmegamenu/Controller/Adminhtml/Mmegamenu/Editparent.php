<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use MGS\Mmegamenu\Helper\Data;
use Magento\Framework\View\LayoutFactory;
use \Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;

class Editparent extends \MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
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
        // $this->_coreRegistry = $coreRegistry;
        parent::__construct($context,$coreRegistry,$fileFactory,$viewHelper,$layoutFactory,$resultLayoutFactory,$resultPageFactory);
    }

    /**
     * Edit sitemap
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('MGS\Mmegamenu\Model\Parents');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Menu no longer exists.'));
                $this->_redirect('adminhtml/mmegamenu/parents');
                return;
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('mmegamenu_parents', $model);

        // 5. Build edit form
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit %1', $model->getTitle()) : __('New Menu'),
            $id ? __('Edit %1', $model->getTitle()) : __('New Menu')
        )->_addContent(
            $this->_view->getLayout()->createBlock('MGS\Mmegamenu\Block\Adminhtml\Editparent')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Megamenu'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getTitle() : __('New Menu')
        );
        $this->_view->renderLayout();
    }
}
