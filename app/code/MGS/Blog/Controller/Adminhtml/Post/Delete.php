<?php

namespace MGS\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;

class Delete extends Action
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('post_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_objectManager->create('MGS\Blog\Model\Post');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The post has been deleted.'));
                return $resultRedirect->setPath('blog/post/index');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('blog/post/edit', ['post_id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a post to delete.'));
        return $resultRedirect->setPath('blog/post/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::delete_post');
    }
}
