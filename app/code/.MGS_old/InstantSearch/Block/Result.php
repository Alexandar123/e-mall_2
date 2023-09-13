<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;
use Magento\Catalog\Model\Layer\Resolver;
use MGS\InstantSearch\Helper\Data;

/**
 * Search result block
 */
class Result extends Template
{
    /**
     * Catalog Product collection
     *
     * @var Collection
     */
    protected $productCollection;

    /**
     * Catalog collection
     *
     * @var Collection
     */
    protected $categoryCollection;

    /**
     * Page collection
     *
     * @var Collection
     */
    protected $pageCollection;

    /**
     * Post collection
     *
     * @var Collection
     */
    protected $postCollection;

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
     * Catalog search data
     *
     * @var Data
     */
    protected $_catalogSearchData;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

	/**
     * @param Context $context
     * @param LayerResolver $layerResolver
     * @param QueryFactory $queryFactory
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param PageCollectionFactory $pageCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        QueryFactory $queryFactory,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        Data $inSearchHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_layerResolver = $layerResolver;
        $this->_queryFactory = $queryFactory;
        $this->_catalogSearchData = $catalogSearchData;
        $this->_inSearchHelper = $inSearchHelper;
    }

    /**
     * Retrieve query model object
     *
     * @return \Magento\Search\Model\Query
     */
    protected function _getQuery()
    {
        return $this->_queryFactory->get();
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = $this->getSearchQueryText();
        $this->pageConfig->getTitle()->set($title);
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'search',
                ['label' => $title, 'title' => $title]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Get search query text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
        return __("Search results for: '%1'", $this->_catalogSearchData->getEscapedQueryText());
    }

    /**
     * Retrieve No Result or Minimum query length Text
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getNoResultText()
    {
        if ($this->_catalogSearchData->isMinQueryLength()) {
            return __('Minimum Search query length is %1', $this->_getQuery()->getMinQueryLength());
        }
        return $this->_getData('no_result_text');
    }

    /**
     * Retrieve Note messages
     *
     * @return array
     */
    public function getNoteMessages()
    {
        return $this->_catalogSearchData->getNoteMessages();
    }

    /**
     * Retrieve search list block
     *
     * @return ListProduct
     */
    public function getProductListBlock()
    {
        return $this->getChildBlock('search_result_list_product');
    }

    /**
     * Retrieve Search result list HTML output
     *
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('search_result_list_product');
    }

    /**
     * Retrieve search list block
     *
     * @return ListCategory
     */
    public function getCategoryListBlock()
    {
        return $this->getChildBlock('search_result_list_category');
    }

    /**
     * Retrieve Search result list HTML output
     *
     * @return string
     */
    public function getCategoryListHtml()
    {
        return $this->getChildHtml('search_result_list_category');
    }

    /**
     * Retrieve search list block
     *
     * @return ListPage
     */
    public function getPageListBlock()
    {
        return $this->getChildBlock('search_result_list_cms_page');
    }

    /**
     * Retrieve Search result list HTML output
     *
     * @return string
     */
    public function getPageListHtml()
    {
        return $this->getChildHtml('search_result_list_cms_page');
    }

    /**
     * Retrieve search list block
     *
     * @return ListBlog
     */
    public function getBlogListBlock()
    {
        return $this->getChildBlock('search_result_list_blog');
    }

    /**
     * Retrieve Search result list HTML output
     *
     * @return string
     */
    public function getBlogListHtml()
    {
        return $this->getChildHtml('search_result_list_blog');
    }

    /**
     * Retrieve loaded product collection
     *
     * @return Collection
     */
    protected function _getProductCollection()
    {
        if (null === $this->productCollection) {
            $this->productCollection = $this->getProductListBlock()->getLoadedProductCollection();
        }
        return $this->productCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Collection
     */
    protected function _getCategoryCollection()
    {
        if (null === $this->categoryCollection) {
            $this->categoryCollection = $this->getCategoryListBlock()->getLoadedCategoryCollection();
        }
        return $this->categoryCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Collection
     */
    protected function _getPageCollection()
    {
        if (null === $this->pageCollection) {
            $this->pageCollection = $this->getPageListBlock()->getLoadedPageCollection();
        }
        return $this->pageCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Collection
     */
    protected function _getBlogCollection()
    {
        if (null === $this->postCollection) {
            $this->postCollection = $this->getBlogListBlock()->getLoadedPostCollection();
        }
        return $this->postCollection;
    }

    /**
     * Retrieve search result count
     *
     * @return string
     */
    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            if($this->_getProductCollection()->getSize()){
                $size = $this->_getProductCollection()->getSize();
                $this->setResultCount($size);
                return $this->getData('result_count');
            }
            if($this->_getCategoryCollection()->getSize()){
                $size = $this->_getCategoryCollection()->getSize();
                $this->setResultCount($size);
                return $this->getData('result_count');
            }
            if($this->_getPageCollection()->getSize()){
                $size = $this->_getPageCollection()->getSize();
                $this->setResultCount($size);
                return $this->getData('result_count');
            }
            if($this->_getBlogCollection()->getSize()){
                $size = $this->_getBlogCollection()->getSize();
                $this->setResultCount($size);
                return $this->getData('result_count');
            }
        }
        return $this->getData('result_count');
    }

    /**
     * get search type
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->_inSearchHelper->getSearchType();
    }
}