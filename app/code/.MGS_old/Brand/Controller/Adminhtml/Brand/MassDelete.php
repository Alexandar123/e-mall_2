<?php

namespace MGS\Brand\Controller\Adminhtml\Brand;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use MGS\Brand\Model\Resource\Brand\CollectionFactory;


class MassDelete extends \Magento\Backend\App\Action
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
        $collectionSize = $collection->getSize();
        foreach ($collection as $brand) {
            $optionManagement = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\OptionManagement');
            $optionManagement->delete(\Magento\Catalog\Model\Product::ENTITY, 'mgs_brand', $brand->getOptionId());
            $brand->delete();
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Brand::delete_brand');
    }
}
