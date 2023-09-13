<?php

namespace MGS\Brand\Controller\Adminhtml\Brand;

class MassStatus extends \MGS\Brand\Controller\Adminhtml\Brand
{
    public function execute()
    {
        $brandIds = $this->getRequest()->getParam('brand');
        if (!is_array($brandIds) || empty($brandIds)) {
            $this->messageManager->addError(__('Please select brand(s).'));
        } else {
            try {
                foreach ($brandIds as $id) {
                    $brand = $this->_objectManager->create('MGS\Brand\Model\Brand')->load($id);
                    $brand->setStatus($this->getRequest()->getParam('status'))->save();
                }
                $this->messageManager->addSuccess(__('Total of %1 brand(s) were changed status.', count($brandIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Brand::save_brand');
    }
}
