<?php

namespace MGS\Blog\Controller\Adminhtml\Category;

use MGS\Blog\Controller\Adminhtml\Blog;

class Index extends Blog
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MGS_Blog::manage_category');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Categories'));
        $resultPage->addBreadcrumb(__('Blog'), __('Blog'));
        $resultPage->addBreadcrumb(__('Manage Categories'), __('Manage Categories'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::manage_category');
    }
}
