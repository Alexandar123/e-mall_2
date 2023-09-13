<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog category model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace MGS\ThemeSettings\Model\ResourceModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Category extends \Magento\Catalog\Model\ResourceModel\Category
{
    /**
     * Return child categories
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getChildrenCategories($category)
    {
		$collection = parent::getChildrenCategories($category);
		$collection->addAttributeToSelect(
            'fbuilder_thumbnail'
        )->addAttributeToSelect(
            'fbuilder_icon'
        )->addAttributeToSelect(
            'fbuilder_font_class'
        )->addAttributeToSelect(
            'description'
        );

        return $collection;
    }
}
