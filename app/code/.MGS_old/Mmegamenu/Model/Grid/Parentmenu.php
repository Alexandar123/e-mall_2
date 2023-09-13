<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backup types option array
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace MGS\Mmegamenu\Model\Grid;

class Parentmenu implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
		$this->_objectManager = $objectManager;
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Mmegamenu\Model\Parents');
	}

    /**
     * Return backup types array
     * @return array
     */
    public function toOptionArray()
    {
		$collection = $this->getModel()->getCollection();
		$result = [];
		if(count($collection)>0){
			foreach($collection as $menu){
				$result[$menu->getId()] = $menu->getTitle();
			}
		}
		return $result;
    }
}
