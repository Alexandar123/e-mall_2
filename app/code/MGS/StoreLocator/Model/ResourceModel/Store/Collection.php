<?php

namespace MGS\StoreLocator\Model\ResourceModel\Store;

use Magento\Store\Model\StoreManagerInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    public function _construct() {
        $this->_init('MGS\StoreLocator\Model\Store', 'MGS\StoreLocator\Model\ResourceModel\Store');
        $this->_map['fields']['store']   = 'store_table.store_id';
    }
	
    public function addDistanceFilter($data = []) {
        $this->getSelect()->where('3959 * acos(cos( radians('.$data['lat_search'].')) * cos( radians( lat ) ) * cos( radians( lng ) - radians('.$data['long_search'].')) + sin( radians('.$data['lat_search'].') ) * sin( radians( lat ) ) ) < '.$data['radius']);
        return $this;
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
        return $this;
    }

    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('store_locator_store')],
                'main_table.id = store_table.storelocator_id',
                []
            );
        }
        return parent::_renderFiltersBefore();
    }

}
