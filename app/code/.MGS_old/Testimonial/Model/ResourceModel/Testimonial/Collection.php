<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Testimonial\Model\ResourceModel\Testimonial;

use MGS\Testimonial\Model\ResourceModel\TestimonialCollection;

/**
 * Testimonial resource model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends TestimonialCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MGS\Testimonial\Model\Testimonial', 'MGS\Testimonial\Model\ResourceModel\Testimonial');
        $this->_map['fields']['testimonial_id'] = 'main_table.testimonial_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    protected function _afterLoad()
    {
        $this->performAfterLoad('mgs_testimonial_store', 'testimonial_id');
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }

    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('mgs_testimonial_store', 'testimonial_id');
    }
}
