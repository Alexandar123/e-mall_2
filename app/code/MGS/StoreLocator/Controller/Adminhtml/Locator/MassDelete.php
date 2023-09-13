<?php

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\StoreLocator\Controller\Adminhtml\Locator;

class MassDelete extends \Magento\Backend\App\Action {
    public function execute() {
        $locatorIds = $this->getRequest()->getParam('locator');
        if (!is_array($locatorIds)) {
            $this->messageManager->addError(__('Please select one or more locator.'));
        } else {
            try {
                foreach ($locatorIds as $_id) {
                    $locator = $this->_objectManager->create(
                                    'MGS\StoreLocator\Model\store'
                            )->load(
                            $_id
                    );
                    $locator->delete();
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) were deleted.', count($locatorIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

}
