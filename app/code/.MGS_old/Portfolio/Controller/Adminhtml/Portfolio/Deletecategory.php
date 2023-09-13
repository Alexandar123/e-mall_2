<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Controller\Adminhtml\Portfolio;

use Magento\Backend\App\Action;

class Deletecategory extends \MGS\Portfolio\Controller\Adminhtml\Portfolio
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
                $model = $this->_objectManager->create('MGS\Portfolio\Model\Category');
                $model->setId($id);
                $model->load($id);
				$title =  $model->getCategoryName();
				$model->delete();
				$this->messageManager->addSuccess(__('You deleted the category "%1".', $title));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('adminhtml/portfolio/category');
    }
}
