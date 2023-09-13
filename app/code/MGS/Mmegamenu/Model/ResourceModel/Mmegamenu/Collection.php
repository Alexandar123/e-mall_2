<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Model\ResourceModel\Mmegamenu;

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
        $this->_init('MGS\Mmegamenu\Model\Mmegamenu', 'MGS\Mmegamenu\Model\ResourceModel\Mmegamenu');
    }
	
	public function addStoreFilter($store, $adminStore = true) {
		$stores = array();

		if ($store instanceof \Magento\Store\Model\Store) {
            $stores[] = (int)$store->getId();
        }
		
		$stores[] = 0;
		$storeTable = $this->getTable('mgs_megamenu_store');
        $this->getSelect()->join(
                        array('stores' => $storeTable),
                        'main_table.megamenu_id = stores.megamenu_id',
                        array()
                )
                ->where('stores.store_id in (?)', ($adminStore ? $stores : $store));
        return $this;
    }
}
