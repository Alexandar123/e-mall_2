<?php

namespace MGS\Brand\Controller\Adminhtml\Brand;

class Edit extends \MGS\Brand\Controller\Adminhtml\Brand
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('brand_id');
        $model = $this->_objectManager->create('MGS\Brand\Model\Brand');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This brand no longer exists.'));
                $this->_redirect('brand/*');
                return;
            }
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_brand', $model);
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Brand') : __('Add New Brand'),
            $id ? __('Edit Brand') : __('Add New Brand')
        );
        $this->_view->getPage()->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('Add New Brand'));
        $this->_view->getLayout()->getBlock('brand_edit');
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Brand::edit_brand');
    }
}
