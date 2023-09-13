<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ThemeSettings\Block\Cart;

/**
 * Cart crosssell list
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell
{
    /**
     * Get crosssell products collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    protected function _getCollection()
    {
		$this->_maxItemCount = $this->_imageHelper->getStoreConfig("themesettings/shopping_cart/number_product");
		$collection = parent::_getCollection();
		$collection->setPageSize($this->_maxItemCount);
        
        return $collection;
    }
}
