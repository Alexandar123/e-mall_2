<?php

namespace MGS\Blog\Controller\Adminhtml\Comment;

use MGS\Blog\Controller\Adminhtml\Blog;

class Grid extends Blog
{
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
