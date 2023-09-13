<?php

namespace MGS\Blog\Controller\Adminhtml\Post;

use MGS\Blog\Controller\Adminhtml\Blog;

class NewAction extends Blog
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Posts'));
        return $resultPage;
    }

    
}
