<?php

namespace MGS\StoreLocator\Controller\Adminhtml\Locator;

class Index extends \MGS\StoreLocator\Controller\Adminhtml\Locator {

    public function execute() {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Store Locator'));
        $this->_setActiveMenu('MGS_StoreLocator::locator_manage');

        $this->_view->renderLayout();
    }

}
