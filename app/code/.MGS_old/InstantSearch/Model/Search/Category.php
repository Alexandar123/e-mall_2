<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Model\Search;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Search\Model\QueryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use MGS\InstantSearch\Helper\Data;
use MGS\InstantSearch\Model\Source\CategoryFields;
/**
 * Category model. Return category data used in search autocomplete
 */
class Category implements \MGS\InstantSearch\Model\SearchInterface
{

	/**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * Product constructor.
     *
     * @param CollectionFactory $categoryCollectionFactory
     * @param ObjectManager $objectManager
     * @param QueryFactory $queryFactory
     * @param Data $inSearchHelper
     */
    public function __construct(
        ObjectManager $objectManager,
        QueryFactory $queryFactory,
        CollectionFactory $categoryCollectionFactory,
        Data $inSearchHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->_queryFactory = $queryFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_inSearchHelper = $inSearchHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseData()
    {
        if($this->_inSearchHelper->isCategorySearch()){
            $queryText = $this->_queryFactory->get()->getQueryText();
            $categories = $this->getCategoryCollection($queryText);
            foreach ($categories as $_category) {
                $responseData['data'][] = $this->getCategoryData($_category);

            }
            $responseData['size'] = $categories->getSize();
            $responseData['url'] = ($categories->getSize() > 0) ? $this->_inSearchHelper->getResultUrl('instantsearch/category/result',$queryText) : '';
            return $responseData;
        }
        $responseData['size'] = 0;
        return $responseData;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Collection
     */
    private function getCategoryCollection($queryText)
    {
        $limit = $this->_inSearchHelper->getNumberResult();
        $categoryCollection = $this->_categoryCollectionFactory->create();
        $categoryCollection->addAttributeToFilter('name', array('like'=>"%{$queryText}%"));
        $categoryCollection->getSelect()->limit($limit);
        return $categoryCollection;
    }

    /**
     * Retrieve loaded category detail
     *
     * @return array
     */
    private function getCategoryData($_category)
    {
        $categoryData = [
            CategoryFields::NAME => $_category->getName(),
            CategoryFields::URL => $_category->getUrl()
        ];

        return $categoryData;
    }
}