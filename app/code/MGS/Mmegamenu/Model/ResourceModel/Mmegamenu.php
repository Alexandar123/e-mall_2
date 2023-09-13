<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Mmegamenu extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('mgs_megamenu', 'megamenu_id');
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
       
        $newStores = (array)$object->getStores();
		
		$table = $this->getTable('mgs_megamenu_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = ['megamenu_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['megamenu_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }
		return parent::_afterSave($object);
    }
	
	public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('mgs_megamenu_store'),
            'store_id'
        )->where(
            'megamenu_id = :megamenu_id'
        );

        $binds = [':megamenu_id' => (int)$id];

        return $connection->fetchCol($select, $binds);
    }
	
	/**
     * Process block data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['megamenu_id = ?' => (int)$object->getId()];

        $this->getConnection()->delete($this->getTable('mgs_megamenu_store'), $condition);

        return parent::_beforeDelete($object);
    }
	
	/**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $categories = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $categories);
            $object->setData('stores', $categories);
        }

        return parent::_afterLoad($object);
    }
}
