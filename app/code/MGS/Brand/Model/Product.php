<?php

namespace MGS\Brand\Model;

class Product extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MGS\Brand\Model\Resource\Product');
    }
}
