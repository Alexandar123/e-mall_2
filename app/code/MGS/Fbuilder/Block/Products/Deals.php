<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Products;

use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Main contact form block
 */
class Deals extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    protected $_count;
    
    protected $_date;
    
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
    
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    /**
     * @param Context                                                        $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility                      $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context                            $httpContext
     * @param array                                                          $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_objectManager = $objectManager;
        $this->httpContext = $httpContext;
        $this->urlHelper = $urlHelper;
        $this->formKey = $formKey;
        $this->_date = $date;
        parent::__construct(
            $context,
            $data
        );
    }
    
    public function getModel($model)
    {
        return $this->_objectManager->create($model);
    }
    
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
        
        if ($category->getId()) {
            $categoryIdArray = [$category->getId()];
            $categoryFilter = ['eq'=>$categoryIdArray];
            $collection->addCategoriesFilter($categoryFilter);
        }

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addAttributeToSelect(['image', 'name', 'short_description'])
            ->addStoreFilter()
            ->addFinalPrice()
            ->addAttributeToSort('created_at', 'desc')
            ->addAttributeToFilter('special_to_date', ['notnull'=>true]);
        
        $collection->getSelect()->where('price_index.final_price < price_index.price');

        $collection->setPageSize($this->getProductsCount())
            ->setCurPage($this->getCurrentPage());
        return $collection;
    }
    
    public function getProductByCategories($categoryIds, $attribute = null)
    {
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
            ->addAttributeToSort('created_at', 'desc')
            ->addAttributeToFilter('special_to_date', ['notnull'=>true]);
        
        $collection->getSelect()->where('price_index.final_price < price_index.price');

        $collection->setPageSize($this->getProductsCount())
            ->setCurPage($this->getCurrentPage());
        
        //echo $collection->getSelect(); die();
        return $collection;
    }
    
    public function getAllProductCount()
    {
        //return $this->_count;
    }
    
    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (!$this->hasData('limit')) {
            return parent::getProductsCount();
        }
        return $this->getData('limit');
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
    
    public function getCurrentDate()
    {
        return $this->_date->gmtDate();
    }
    
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
    
    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
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
