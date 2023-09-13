<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Products;

/**
 * Main contact form block
 */
class Sale extends \MGS\Fbuilder\Block\Products\AbstractProduct
{
    /**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getProductCollection($category, $attribute = null)
    {
        /**
 * @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection 
*/
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        
        //$collection->addCategoryFilter($category);
        if ($category->getId()) {
            $categoryIdArray = [$category->getId()];
            $categoryFilter = ['eq'=>$categoryIdArray];
            $collection->addCategoriesFilter($categoryFilter);
        }
        
        
        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addAttributeToSelect(['image', 'name', 'short_description'])
            ->addStoreFilter()
            ->addFinalPrice()
            ->addAttributeToSort('created_at', 'desc');
        
        $collection->getSelect()->where('price_index.final_price < price_index.price');
        
        //$this->_count = $collection->count();
        
        $collection->setPageSize($this->getLimit())
            ->setCurPage($this->getCurrentPage())
            ->setOrder('entity_id', 'DESC');

        return $collection;
    }
    
    public function getProductByCategories($categoryIds, $attribute = null)
    {
        /**
 * @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection 
*/
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        
        
        if ($categoryIds!='') {
            $categoryIdArray = explode(',', $categoryIds);
            if (count($categoryIdArray)>0) {
                $categoryFilter = ['eq'=>$categoryIdArray];
                $collection->addCategoriesFilter($categoryFilter);
            }
        }
        
        
        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addAttributeToSelect(['image', 'name', 'short_description'])
            ->addStoreFilter()
            ->addFinalPrice()
            ->addAttributeToSort('created_at', 'desc');
        
        $collection->getSelect()->where('price_index.final_price < price_index.price');

        $collection->setPageSize($this->getLimit())
            ->setCurPage($this->getCurrentPage())
            ->setOrder('entity_id', 'DESC');
        return $collection;
    }
    
    public function getAllProductCount()
    {
        //return $this->_count;
    }
    
    public function getCurrentPage()
    {
        if ($this->getCurPage()) {
            return $this->getCurPage();
        }
        return 1;
    }
    
    public function getProductsPerRow()
    {
        if ($this->hasData('per_row')) {
            return $this->getData('per_row');
        }
        return false;
    }
    
    public function getCustomClass()
    {
        if ($this->hasData('custom_class')) {
            return $this->getData('custom_class');
        }
    }
    
    public function getCategoryByIds()
    {
        $result = [];
        if ($this->hasData('category_ids')) {
            $categoryIds = $this->getData('category_ids');
            $categoryArray = explode(',', $categoryIds);
            if (count($categoryArray)>0) {
                foreach ($categoryArray as $categoryId) {
                    $category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
                    if ($category->getId()) {
                        $result[] = $category;
                    }
                    
                }
            }
        }
        return $result;
    }
}
