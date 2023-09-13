<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Block;

/**
 * Main contact form block
 */
class Vertical extends Abstractmenu
{
	public function getMegamenuItems(){
		$store = $this->getStore();
		$menuCollection = $this->getModel('MGS\Mmegamenu\Model\Mmegamenu')
			->getCollection()
			->distinct(true)
			->addStoreFilter($store)
			->addFieldToFilter('parent_id', $this->getMenuId())
			->addFieldToFilter('status', 1)
			->setOrder('position', 'ASC')
		;
		foreach($menuCollection as $key => $item) {
			$temp = $item->getMegamenuId();
            $update = $this->getMegamenuByStore($store->getId(), $temp);
            foreach ($update as $key =>$value) {
                $item[$key] = $value['value'];
            }
		} 
		return $menuCollection;
	}
	public function getMegamenuByStore($store, $megamenu_id) {
		$resource = $this->getResourceModel('MGS\Mmegamenu\Model\ResourceModel\Mmegamenu');
        $table = $resource->getTable('mgs_megamenu_update');
        $connection = $resource->getConnection();
        $sql = "SELECT `field`, `value` 
                 FROM `$table` 
                 WHERE `scope_id`= $store
                 AND `megamenu_id`= $megamenu_id ";
        $megamenu = $connection->fetchAssoc($sql);
        return $megamenu;
    }
}

