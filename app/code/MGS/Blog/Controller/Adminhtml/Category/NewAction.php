<?php

namespace MGS\Blog\Controller\Adminhtml\Category;

class NewAction extends \MGS\Blog\Controller\Adminhtml\Blog
{
    public function execute()
    {
      $resultPage= $this->resultPageFactory->create();
      $resultPage->getConfig()->getTitle()->prepend(__('Manage Category'));

      return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::edit_category');
    }
}
