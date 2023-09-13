<?php

namespace MGS\Blog\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Framework\DB\Select;

class Category extends AbstractDb
{
    protected $storeManager;
    protected $store;

    public function __construct(
        Context $context,
        Store $store,
        StoreManagerInterface $storeManager,
        $connectionName = null
    )
    {
        $this->store = $store;
        $this->storeManager = $storeManager;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('mgs_blog_category', 'category_id');
    }
  
    protected function _beforeSave(AbstractModel $object)
    {
        $this->checkUrlKeyExits($object);
        return parent::_beforeSave($object);
    }

    public function checkUrlKeyExits(AbstractModel $object)
    {
        $stores = $object->getStores();
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('mgs_blog_category'),
            'category_id'
        )
            ->where(
                '`url_key` = ?',
                $object->getUrlKey()
            )
            ->where(
                '`category_id` != ?',
                $object->getId()
            );
        $categoryIds = $connection->fetchCol($select);
        if (count($categoryIds) > 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('URL Key for specified store already exists.')
                );
             }
        return $this;
    }

    public function _afterSave(AbstractModel $object) {
        return parent::_afterSave($object);
    } 
}
