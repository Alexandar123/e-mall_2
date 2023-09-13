<?php

namespace MGS\Blog\Model\Resource\Category;

use MGS\Blog\Model\Resource\CategoryCollection;

class Collection extends CategoryCollection
{
    protected $_idFieldName = 'category_id';
    protected $_previewFlag;

    protected function _construct()
    {
        $this->_init('MGS\Blog\Model\Category', 'MGS\Blog\Model\Resource\Category');
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->getSelect()
        ->join(
            ['category_table' => $this->getTable('mgs_blog_category_store')],
            'main_table.category_id = category_table.category_id',
            []
        )
        ->where('category_table.store_id IN('.$store.',0)');
        return $this;
    }

    public function addPostFilter($postId)
    {
        $this->getSelect()
            ->join(
                ['category_table' => $this->getTable('mgs_blog_category_post')],
                'main_table.category_id = category_table.category_id',
                []
            )
            ->where('category_table.post_id = ? IN', $postId);
        return $this;
    }

}
