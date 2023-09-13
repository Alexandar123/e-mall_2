<?php

namespace MGS\Blog\Model\Resource\Tag;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('MGS\Blog\Model\Tag', 'MGS\Blog\Model\Resource\Tag');
    }
}
