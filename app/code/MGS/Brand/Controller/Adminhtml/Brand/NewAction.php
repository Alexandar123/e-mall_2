<?php

namespace MGS\Brand\Controller\Adminhtml\Brand;

class NewAction extends \MGS\Brand\Controller\Adminhtml\Brand
{
    public function execute()
    {
        $this->_forward('edit');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Brand::edit_brand');
    }
}
