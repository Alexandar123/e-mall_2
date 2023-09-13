<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Controller\Adminhtml\Portfolio;

class Edit extends \MGS\Portfolio\Controller\Adminhtml\Portfolio
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
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('MGS\Portfolio\Model\Portfolio');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This post no longer exists.'));
                $this->_redirect('adminhtml/portfolio/index');
                return;
            }
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('portfolio_portfolio', $model);
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Portfolio') : __('Add Item'),
            $id ? __('Edit Item') : __('Add Item')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Portfolio'));
        $this->_view->getPage()->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('Edit Item'));
        $this->_view->getLayout()->getBlock('portfolio_edit');
        $this->_view->renderLayout();
    }
}
