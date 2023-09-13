<?php

namespace MGS\Blog\Controller\Adminhtml\Category;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use MGS\Blog\Model\Resource\Category\CollectionFactory;
use Magento\Backend\App\Action;

class MassEnable extends Action
{
    protected $filter;
    protected $collectionFactory;

    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection as $item) {
            $item->setStatus(1);
            $item->save();
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been enabled.', $collection->getSize()));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::save_category');
    }
}
