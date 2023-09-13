<?php
/**
 * Newsletter subscriber grid collection
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\StoreLocator\Model\ResourceModel\Store\Grid;

class Collection extends \MGS\StoreLocator\Model\ResourceModel\Store\Collection
{
    /**
     * Sets flag for customer info loading on load
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        //$this->showCustomerInfo(true)->addSubscriberTypeField()->showStoreInfo();
        return $this;
    }
}
