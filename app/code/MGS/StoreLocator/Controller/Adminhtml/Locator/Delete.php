<?php

namespace MGS\StoreLocator\Controller\Adminhtml\Locator;

class Delete extends \Magento\Backend\App\Action {
    public function execute() {
        $locatorId = $this->getRequest()->getParam('id', false);
        if ($locatorId) {
            try {
                $locator = $this->_objectManager->create('MGS\StoreLocator\Model\Store')->load($locatorId);
                $locator->delete();
                $this->messageManager->addSuccess(__('A Store locator has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

}
