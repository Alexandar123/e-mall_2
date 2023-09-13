<?php
namespace MGS\StoreLocator\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use MGS\StoreLocator\Model\Store as ModelStoreLocator;

class Store extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_date;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    protected function _construct()
    {
        $this->_init('store_locator', 'id'); //store_locator <=> table_name
    }

    protected function _beforeSave(AbstractModel $storeLocator)
    {
        if ($storeLocator->isObjectNew()) {
            $storeLocator->setCreatedAt($this->_date->gmtDate());
        }

        $storeLocator->setUpdatedAt($this->_date->gmtDate());

        return parent::_beforeSave($storeLocator);
    }

    protected function _afterSave(AbstractModel $storeLocator)
    {
        $connection = $this->getConnection();
        $connection->delete($this->getTable('store_locator_store'), ['storelocator_id = ?' => $storeLocator->getId()]);

        $stores = $storeLocator->getStoreIds();
        if (!is_array($stores)) {
            $stores = [];
        }

        foreach ($stores as $storeId) {
            $data = [];
            $data['store_id'] = $storeId;
            $data['storelocator_id'] = $storeLocator->getId();
            $connection->insert($this->getTable('store_locator_store'), $data);
        }
        return $this;
    }
	
	
     public function getStores(ModelStoreLocator $storeLocator)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('store_locator_store'),
            'store_id'
        )->where(
            'storelocator_id = :storelocator_id'
        );

        if (!($result = $connection->fetchCol($select, ['storelocator_id' => $storeLocator->getId()]))) {
            $result = [];
        }

        return $result;
    }

}
