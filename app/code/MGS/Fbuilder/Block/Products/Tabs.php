<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Products;

/**
 * Main contact form block
 */
class Tabs extends \MGS\Fbuilder\Block\Products\AbstractProduct
{
    public function getAttributes()
    {
        $result = [];
        if ($this->hasData('attributes')) {
            $attributeCodes = $this->getData('attributes');
            $attributeArray = explode(',', $attributeCodes);
            if (count($attributeArray)>0) {
                foreach ($attributeArray as $attributeCode) {
                    $result[] = $attributeCode;
                }
            }
        }
        return $result;
    }
    
    public function getLabels()
    {
        $result = [];
        if ($this->hasData('labels')) {
            $labels = $this->getData('labels');
            $result = explode(',', $labels);
        }
        return $result;
    }
    
    public function getTabs()
    {
        $tabs = [];
        if ($this->hasData('tabs')) {
            $tabs = $this->getData('tabs');
            $tabs = explode(',', $tabs);
        }
        return $tabs;
    }
    
    public function existAttribute($attribute)
    {
        $attribute = $this->_attributeCollection->create()->addVisibleFilter()
            ->addFieldToFilter('attribute_code', $attribute)
            ->addFieldToFilter('backend_type', 'int')
            ->addFieldToFilter('frontend_input', 'boolean')
            ->getFirstItem();
        if ($attribute->getId()) {
            return true;
        }
        return false;
    }
}
