<?php

namespace MGS\StoreLocator\Controller\Adminhtml\Locator;

class MassStatus extends \Magento\Backend\App\Action {

    public function execute() {
        $locatorIds = $this->getRequest()->getParam('locator');
        if (!is_array($locatorIds)) {
            $this->messageManager->addError(__('Please select one or more store locator.'));
        } else {
            try {
                foreach ($locatorIds as $_id) {
                    $locator = $this->_objectManager->create('MGS\StoreLocator\Model\Store')->load($_id);
                    $locator->setStatus($this->getRequest()->getParam('status'))->save();
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) were changed status.', count($locatorIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

}
