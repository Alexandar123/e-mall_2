<?php

namespace MGS\Brand\Model\Resource\Product;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('MGS\Brand\Model\Product', 'MGS\Brand\Model\Resource\Product');
    }
}
