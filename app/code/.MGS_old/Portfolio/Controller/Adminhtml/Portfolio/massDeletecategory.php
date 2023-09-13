<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Controller\Adminhtml\Portfolio;

use Magento\Backend\App\Action;

class massDeletecategory extends \MGS\Portfolio\Controller\Adminhtml\Portfolio
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
            $this->messageManager->addError(__('Please select categories.'));
        } else {
            try {
                foreach ($ids as $id) {
					$model = $this->_objectManager->create('MGS\Portfolio\Model\Category')
						->load($id)
						->delete();
                }
				$this->messageManager->addSuccess(__('Total of %1 categories were successfully deleted.', count($ids)));
                
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
		
        return $resultRedirect->setPath('adminhtml/portfolio/category');
    }
}
