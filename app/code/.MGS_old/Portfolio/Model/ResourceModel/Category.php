<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Model\ResourceModel;

class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgs_portfolio_category', 'category_id');
    }
	
	/**
     * Process block data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['category_id = ?' => (int)$object->getId()];

        $this->getConnection()->delete($this->getTable('mgs_portfolio_category_items'), $condition);

        return parent::_beforeDelete($object);
    }
}
