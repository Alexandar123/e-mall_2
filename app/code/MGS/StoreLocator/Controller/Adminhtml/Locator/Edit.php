<?php

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\StoreLocator\Controller\Adminhtml\Locator;

use MGS\StoreLocator\Model\StoreFactory;
use Magento\Backend\App\Action\Context;

class Edit extends \Magento\Backend\App\Action {

    protected $_storeFactory;
    protected $_coreRegistry;

    public function __construct(Context $context, StoreFactory $storeFactory, \Magento\Framework\Registry $coreRegistry) {
        parent::__construct($context);
        $this->_storeFactory = $storeFactory;
        $this->_coreRegistry = $coreRegistry;
    }
    
    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_addBreadcrumb(
            __('Store Locator'),
            __('Store Locator')
        )->_addBreadcrumb(
            __('Edit'),
            __('Edit')
        );
        return $this;
    }

    /**
     * @return void
     */
    public function execute() {
        $storeId = $this->getRequest()->getParam('id');
        /** @var \Magento\User\Model\User $model */
        $model = $this->_storeFactory->create();

        if ($storeId) {
            $model->load($storeId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Locator no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }
        // Restore previously entered form data from session
        $data = $this->_session->getLocator(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('locator', $model);
        if (isset($storeId)) {
            $breadcrumb = __('Edit Store');
        } else {
            $breadcrumb = __('New Store');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Stores'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ? __("Edit Store '%1'", $model->getName()) : __('New Store'));
        $this->_view->renderLayout();
    }

}
