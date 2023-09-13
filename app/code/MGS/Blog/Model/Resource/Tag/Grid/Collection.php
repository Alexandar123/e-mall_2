<?php

namespace MGS\Blog\Model\Resource\Tag\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('MGS\Blog\Model\Tag', 'MGS\Blog\Model\Resource\Tag');
        $this->_map['fields']['tag_id'] = 'main_table.tag_id';
    }
}
