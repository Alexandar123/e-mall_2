<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Panel;

/**
 * Main contact form block
 */
class ProductDescription extends HomeContent
{
    public function getSections()
    {
        $productId = $this->getProduct()->getId();
        $isOverriden = $this->getScopeOverride() ? $this->getScopeOverride() : '0';
        $sectionCollection = $this->_sectionCollectionFactory->create();
        $sectionCollection->addFieldToFilter('page_type', 'product')->addFieldToFilter('product_id', $productId)->setOrder('block_position', 'ASC');
        if ($isOverriden==1) {
            $sectionCollection->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId());
        } else {
            $sectionCollection->addFieldToFilter('store_id', 0);
        }
        return $sectionCollection;
    }
}
