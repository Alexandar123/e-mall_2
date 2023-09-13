<?php

namespace MGS\Blog\Controller\Adminhtml\Comment;

use MGS\Blog\Controller\Adminhtml\Blog;

class MassStatus extends Blog
{
    public function execute()
    {
        $commentIds = $this->getRequest()->getParam('comment');
        if (!is_array($commentIds) || empty($commentIds)) {
            $this->messageManager->addError(__('Please select comment(s).'));
        } else {
            try {
                foreach ($commentIds as $id) {
                    $comment = $this->_objectManager->create('MGS\Blog\Model\Comment')->load($id);
                    $comment->setStatus($this->getRequest()->getParam('status'))->save();
                }
                $this->messageManager->addSuccess(__('Total of %1 comment(s) were changed status.', count($commentIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('blog/comment/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::save_comment');
    }
}
