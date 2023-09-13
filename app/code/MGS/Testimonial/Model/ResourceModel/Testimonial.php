<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Testimonial\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Testimonial extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
	
	/**
     * @var \Magento\Framework\Stdlib\DateTime
     */
	protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
		$this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }
	
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('testimonial', 'testimonial_id');
    }

    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['testimonial_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('mgs_testimonial_store'), $condition);
        return parent::_beforeDelete($object);
    }
	
	protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreationTime($this->_date->gmtDate());
        }

        $object->setUpdateTime($this->_date->gmtDate());

        return parent::_beforeSave($object);
    }
	
	
	protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores =  (array)$object->getStoreId();
        $table = $this->getTable('mgs_testimonial_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = ['testimonial_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['testimonial_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }

    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $storeIds = [Store::DEFAULT_STORE_ID, (int)$object->getStoreId()];
            $select->join(
                ['mgs_testimonial_store' => $this->getTable('mgs_testimonial_store')],
                $this->getMainTable() . '.testimonial_id = mgs_testimonial_store.testimonial_id',
                []
            )->where(
                'status = ?',
                1
            )->where(
                'mgs_testimonial_store.store_id IN (?)',
                $storeIds
            )->order(
                'mgs_testimonial_store.store_id DESC'
            )->limit(
                1
            );
        }
        return $select;
    }

    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        // $select = $this->getConnection()->select()->from(
        //     ['cp' => $this->getMainTable()]
        // )->join(
        //     ['cps' => $this->getTable('mgs_testimonial_store')],
        //     'cp.testimonial_id = cps.testimonial_id',
        //     []
        // )->where(
        //     'cp.name = ?',
        //     $identifier
        // )->where(
        //     'cps.store_id IN (?)',
        //     $store
        // );
        // if (!is_null($isActive)) {
        //     $select->where('cp.status = ?', $isActive);
        // }
        // return $select;
    }

    public function lookupStoreIds($testimonialId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('mgs_testimonial_store'),
            'store_id'
        )->where(
            'testimonial_id = ?',
            (int)$testimonialId
        );
        return $connection->fetchCol($select);
    }

    public function checkUrlKeyExits(AbstractModel $object)
    {
        $stores = $object->getStores();
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('testimonial'),
            'testimonial_id'
        )
            ->where(
                'name = ?',
                $object->getName()
            )
            ->where(
                'testimonial_id != ?',
                $object->getId()
            );
        $postIds = $connection->fetchCol($select);
        if (count($postIds) > 0 && is_array($stores)) {
            if (in_array('0', $stores)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Name for specified store already exists.')
                );
            }
            $stores[] = '0';
            $select = $connection->select()->from(
                $this->getTable('mgs_testimonial_store'),
                'testimonial_id'
            )
                ->where(
                    'testimonial_id IN (?)',
                    $postIds
                )
                ->where(
                    'store_id IN (?)',
                    $stores
                );
            $result = $connection->fetchCol($select);
            if (count($result) > 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Name for specified store already exists.')
                );
            }
        }
        return $this;
    }
}
