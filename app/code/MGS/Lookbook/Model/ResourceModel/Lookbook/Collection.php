<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Model\ResourceModel\Lookbook;

/**
 * Mmegamenu resource model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MGS\Lookbook\Model\Lookbook', 'MGS\Lookbook\Model\ResourceModel\Lookbook');
    }
	
	public function addSliderFilter($sliderId){
		$itemTable = $this->getTable('mgs_lookbook_slide_items');
        $this->getSelect()->join(
                        ['items' => $itemTable],
                        'main_table.lookbook_id = items.lookbook_id',
                        array()
                )
                ->where('items.slide_id ='.$sliderId)
				->order('items.position ASC');
        return $this;
	}
}
