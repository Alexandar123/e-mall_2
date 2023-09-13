<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Model\ResourceModel;

class Slide extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mgs_lookbook_slide', 'slide_id');
    }
	
	protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if(is_array($object->getLookbooks())){
			$itemTable = $this->getTable('mgs_lookbook_slide_items');
			$condition = ['slide_id = ?' => (int)$object->getId()];

            $this->getConnection()->delete($itemTable, $condition);
			
			if(count($object->getLookbooks())>0){
				foreach ($object->getLookbooks() as $lookbookId=>$lookbook) {
					
					$itemArray = array();
					$itemArray['slide_id'] = $object->getId();
					$itemArray['lookbook_id'] = $lookbookId;
					$itemArray['position'] = $lookbook['position'];

					$this->getConnection()->insert($itemTable, $itemArray);
				}
			}
		}
		
		return parent::_afterSave($object);
    }
}
