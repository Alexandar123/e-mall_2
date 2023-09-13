<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Model\ResourceModel\Item;

/**
 * Mmegamenu resource model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MGS\Lookbook\Model\Item', 'MGS\Lookbook\Model\ResourceModel\Item');
    }
}
