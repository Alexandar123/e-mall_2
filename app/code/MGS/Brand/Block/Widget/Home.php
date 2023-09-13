<?php

namespace MGS\Brand\Block\Widget;

class Home extends \Magento\Framework\View\Element\Template
{
    protected $_brand;
    protected $_coreRegistry = null;
    protected $_brandHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MGS\Brand\Model\Brand $brand,
        array $data = []
    )
    {
        $this->_brand = $brand;
        parent::__construct($context, $data);
    }

    public function getBrandCollection()
    {
        $collection = $this->_brand->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
			->setPageSize($this->getLimit());
		
		if($this->getBrandBy()=='featured'){
			$collection->addFieldToFilter('is_featured', 1);
		}
       
        $collection->setOrder('sort_order', 'ASC');
        return $collection;
    }
}