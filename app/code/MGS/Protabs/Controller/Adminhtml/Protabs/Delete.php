<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Protabs\Controller\Adminhtml\Protabs;

use Magento\Backend\App\Action;

class Delete extends \MGS\Protabs\Controller\Adminhtml\Protabs
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
		$resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
		if ($id) {
            try {
                $model = $this->_objectManager->create('MGS\Protabs\Model\Protabs');
                $model->setId($id);
                $model->load($id);
				$title =  $model->getTitle();
				$model->delete();
				$this->messageManager->addSuccess(__('You deleted the item "%1".', $title));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
