<?php

namespace MGS\Brand\Model\Rewrite\ResourceModel;

class Store extends \Magento\Store\Model\ResourceModel\Store {

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object) {
        $table = $this->getTable('mgs_brand');
        $delete = $this->lookupBrandIds($object->getId());
        if ($delete) {
            $where = ['brand_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        return parent::_beforeDelete($object);
    }

    public function lookupBrandIds($storeId) {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
                        $this->getTable('mgs_brand_store'), 'brand_id'
                )->where(
                'store_id = ?', (int) $storeId
        );
        return $connection->fetchCol($select);
    }

}
