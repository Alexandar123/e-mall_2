<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Model\ResourceModel;

class Stores extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgs_portfolio_category_items', 'entity_id');
    }
	
	public function joinFilter($collection){
		$tableName = $this->getTable('mgs_portfolio_category');
		$collection->getSelect()->joinLeft(array('cat'=>$tableName), 'main_table.category_id = cat.category_id', array('cat.category_name as name'));
		return $collection;
	}
}
