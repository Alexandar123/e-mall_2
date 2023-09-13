<?php

namespace MGS\Brand\Controller\Adminhtml\Brand;

class Delete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('brand_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_objectManager->create('MGS\Brand\Model\Brand');
                $model->load($id);
                $optionManagement = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\OptionManagement');
                $optionManagement->delete(\Magento\Catalog\Model\Product::ENTITY, 'mgs_brand', $model->getOptionId());
                $model->delete();
                $this->messageManager->addSuccess(__('The brand has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['brand_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a brand to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Brand::delete_brand');
    }
}
