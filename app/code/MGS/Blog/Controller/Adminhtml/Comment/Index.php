<?php

namespace MGS\Blog\Controller\Adminhtml\Comment;

use MGS\Blog\Controller\Adminhtml\Blog;

class Index extends Blog
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MGS_Blog::manage_comment');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Comments'));
        $resultPage->addBreadcrumb(__('Blog'), __('Blog'));
        $resultPage->addBreadcrumb(__('Manage Categories'), __('Manage Comments'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::manage_comment');
    }
}
