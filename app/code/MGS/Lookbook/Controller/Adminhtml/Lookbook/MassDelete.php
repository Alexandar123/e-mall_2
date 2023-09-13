<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Controller\Adminhtml\Lookbook;

use Magento\Backend\App\Action;

class MassDelete extends \MGS\Lookbook\Controller\Adminhtml\Lookbook
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
		$resultRedirect = $this->resultRedirectFactory->create();
        $ids = $this->getRequest()->getPost('ids');
		if(!is_array($ids)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($ids as $id) {
					$model = $this->_objectManager->create('MGS\Lookbook\Model\Lookbook')
						->load($id)
						->delete();
                }
				$this->messageManager->addSuccess(__('Total of %1 lookbook(s) were successfully deleted.', count($ids)));
                
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
