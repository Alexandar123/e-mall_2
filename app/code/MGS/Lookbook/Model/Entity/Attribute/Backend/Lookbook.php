<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Model\Entity\Attribute\Backend;

class Lookbook extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	/**
     * @var \MGS\Lookbook\Model\ResourceModel\Lookbook\CollectionFactory
     */
    protected $_lookbookCollectionFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \MGS\Lookbook\Model\ResourceModel\Lookbook\CollectionFactory $lookbookCollectionFactory
    ) {
        $this->_lookbookCollectionFactory = $lookbookCollectionFactory;
    }
	
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
			$result[] = ['value'=>'', 'label'=>__(' ')];
			
            $lookbooks = $this->_lookbookCollectionFactory->create();
			if(count($lookbooks)>0){
				foreach($lookbooks as $lookbook){
					$result[] = ['value'=>$lookbook->getId(), 'label'=>$lookbook->getName()];
				}
			}
			
			$this->_options = $result;

        }
        return $this->_options;
    }

}
