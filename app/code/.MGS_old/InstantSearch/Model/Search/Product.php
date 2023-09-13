<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Model\Search;
use \Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Search\Model\QueryFactory;
use Magento\Catalog\Model\Layer\Resolver;
use MGS\InstantSearch\Model\Source\ProductFields;
use MGS\InstantSearch\Helper\Data;
use \Magento\Search\Helper\Data as SearchHelper;
/**
 * Product model. Return product data used in search autocomplete
 */
class Product implements \MGS\InstantSearch\Model\SearchInterface
{
	/**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $_layerResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var Data
     */
    protected $_inSearchHelper;
    /**
     * @var \Magento\Search\Helper\Data
     */
    protected $_searchHelper;

    /**
     * Product constructor.
     *
     * @param LayerResolver $layerResolver
     * @param ObjectManager $objectManager
     * @param QueryFactory $queryFactory
     * @param SearchHelper $searchHelper
     * @param Data $inSearchHelper
     */
    public function __construct(
        Resolver $layerResolver,
        ObjectManager $objectManager,
        QueryFactory $queryFactory,
        SearchHelper $searchHelper,
        Data $inSearchHelper
    ) {
        $this->_layerResolver = $layerResolver;
        $this->_objectManager = $objectManager;
        $this->_queryFactory = $queryFactory;
        $this->_inSearchHelper = $inSearchHelper;
        $this->_searchHelper = $searchHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseData()
    {
        if($this->_inSearchHelper->isProductSearch()){
            $queryText = $this->_queryFactory->get()->getQueryText();
            $productCollection = $this->getProductCollection($queryText);

            foreach ($productCollection as $product) {
                $responseData['data'][] = $this->getProductData($product);
            }

            $responseData['size'] = $productCollection->getSize();
            $responseData['url'] = ($productCollection->getSize() > 0) ? $this->_searchHelper->getResultUrl($queryText) : '';
            return $responseData;
        }
        $responseData['size'] = 0;
        return $responseData;
    }

    public function getProductCollection($queryText)
    {
        $limit = $this->_inSearchHelper->getNumberResult();
    	$this->_layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
    	$productCollection = $this->_layerResolver->get()
            ->getProductCollection()
            ->addSearchFilter($queryText);
        $productCollection->getSelect()->limit($limit);
        return $productCollection;
    }

    private function getProductData($product)
    {
    	/**
    	* @var \MGS\InstantSearch\Block\Product\ProductAggregator $product
    	*/
        $_product = $this->_objectManager->create('MGS\InstantSearch\Block\Product\ProductAggregator')
            ->setProduct($product);

        $productData = [
            ProductFields::NAME => $_product->getName(),
            ProductFields::IMAGE => $_product->getSmallImage(),
            ProductFields::PRICE => $_product->getPrice(),
            ProductFields::URL => $_product->getUrl()
        ];
        if($this->_inSearchHelper->showShortDescriptionProductSearch()){
            $productData[] = [ProductFields::SHORT_DESCRIPTION => $_product->getShortDescription()];
        }
        if($this->_inSearchHelper->showReviewProductSearch()){
            $productData[] = [ProductFields::REVIEWS_RATING => $_product->getReviewsRating()];
        }
        return $productData;
    }
}