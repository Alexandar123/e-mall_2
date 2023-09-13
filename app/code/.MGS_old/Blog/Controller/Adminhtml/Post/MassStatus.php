<?php

namespace MGS\Blog\Controller\Adminhtml\Post;

use MGS\Blog\Controller\Adminhtml\Blog;

class MassStatus extends Blog
{
    public function execute()
    {
        $postIds = $this->getRequest()->getParam('post');
        if (!is_array($postIds) || empty($postIds)) {
            $this->messageManager->addError(__('Please select post(s).'));
        } else {
            try {
                foreach ($postIds as $id) {
                    $post = $this->_objectManager->create('MGS\Blog\Model\Post')->load($id);
                    $post->setStatus($this->getRequest()->getParam('status'))->save();
                }
                $this->messageManager->addSuccess(__('Total of %1 post(s) were changed status.', count($postIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('blog/post/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::save_post');
    }
}
