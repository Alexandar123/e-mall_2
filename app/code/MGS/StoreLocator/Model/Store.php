<?php
namespace MGS\StoreLocator\Model;

class Store extends \Magento\Framework\Model\AbstractModel {
    /**
     * Stores assigned to storelocator.
     *
     * @var array
     */
    protected $_stores = [];
    
    protected function _construct() {
        $this->_init('MGS\StoreLocator\Model\ResourceModel\Store');
    }
    
    public function getStores()
    {
        if (!$this->_stores) {
            $this->_stores = $this->_getResource()->getStores($this);
        }

        return $this->_stores;
    }

}
