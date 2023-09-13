<?php

namespace MGS\Blog\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Framework\DB\Select;

class Post extends AbstractDb
{
    protected $storeManager;
    protected $store;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Store $store,
        $connectionName = null
    )
    {
        $this->store = $store;
        $this->storeManager = $storeManager;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('mgs_blog_post', 'post_id');
    }

    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['post_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('mgs_blog_comment'), $condition);
        $this->getConnection()->delete($this->getTable('mgs_blog_post_store'), $condition);
        return parent::_beforeDelete($object);
    }

    protected function _beforeSave(AbstractModel $object)
    {
        $this->checkUrlKeyExits($object);
        return parent::_beforeSave($object);
    }

    protected function _afterSave(AbstractModel $object)
    {  
        $oldCategories = $this->lookupCategories($object->getId());
        $newCategories = (array)$object->getCategories();
        if (empty($newCategories)) {
            $newCategories = [];
        }
        $table = $this->getTable('mgs_blog_category_post');
        $insert = array_diff($newCategories, $oldCategories);
        $delete = array_diff($oldCategories, $newCategories);
        if ($delete) {
            $where = ['post_id = ?' => (int)$object->getId(), 'category_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $categoryId) {
                $data[] = ['post_id' => (int)$object->getId(), 'category_id' => (int)$categoryId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }

    public function load(AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'url_key';
        }
        return parent::load($object, $value, $field);
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $storeIds = [Store::DEFAULT_STORE_ID, (int)$object->getStoreId()];
            $select->join(
                ['mgs_blog_post_store' => $this->getTable('mgs_blog_post_store')],
                $this->getMainTable() . '.post_id = mgs_blog_post_store.post_id',
                []
            )->where(
                'status = ?',
                1
            )->where(
                'mgs_blog_post_store.store_id IN (?)',
                $storeIds
            )->order(
                'mgs_blog_post_store.store_id DESC'
            )->limit(
                1
            );
        }
        return $select;
    }

    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cps' => $this->getTable('mgs_blog_post_store')],
            'cp.post_id = cps.post_id',
            []
        )->where(
            'cp.url_key = ?',
            $identifier
        )->where(
            'cps.store_id IN (?)',
            $store
        );
        if (!is_null($isActive)) {
            $select->where('cp.status = ?', $isActive);
        }
        return $select;
    }

    protected function isNumericPostUrlKey(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('url_key'));
    }

    protected function isValidPostUrlKey(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('url_key'));
    }

    public function checkIdentifier($urlKey, $storeId)
    {
        $stores = [Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdentifierSelect($urlKey, $stores, 1);
        $select->reset(Select::COLUMNS)->columns('cp.post_id')->order('cps.store_id DESC')->limit(1);
        return $this->getConnection()->fetchOne($select);
    }

    public function lookupStoreIds($postId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('mgs_blog_post_store'),
            'store_id'
        )->where(
            'post_id = ?',
            (int)$postId
        );
        return $connection->fetchCol($select);
    }

    public function lookupCategories($postId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('mgs_blog_category_post'),
            'category_id'
        )->where(
            'post_id = ?',
            (int)$postId
        );
        return $connection->fetchCol($select);
    }

    public function checkUrlKeyExits(AbstractModel $object)
    {
        $connection = $this->getConnection();
      
        $select = $connection->select()->from(
            $this->getTable('mgs_blog_post'),
            'post_id'
        )
            ->where(
                'url_key = ?',
                $object->getUrlKey()
            )
            ->where(
                'post_id != ?',
                $object->getId()
            );
        $postIds = $connection->fetchCol($select);
        if (count($postIds) > 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('URL Key for specified store already exists.')
                );
        }
        return $this;
    }
}
