<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Block\Category;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use MGS\InstantSearch\Helper\Data;
/**
 * Search result block
 */
class ListCategory extends Template
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'MGS\InstantSearch\Block\SearchList\Toolbar';
    /**
     * Category Collection
     *
     * @var AbstractCollection
     */
    protected $categoryCollection;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * Catalog search data
     *
     * @var Data
     */
    protected $_catalogSearchData;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;
    private $currentType = 'category';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;
    /**
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param Data $inSearchHelper
     * @param CollectionFactory $categoryCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        Data $inSearchHelper,
        CollectionFactory $categoryCollectionFactory,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        array $data = []
    ) {
        parent::__construct($context,$data);
        $this->_queryFactory = $queryFactory;
        $this->_inSearchHelper = $inSearchHelper;
        $this->_catalogSearchData = $catalogSearchData;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
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
     * Get category search query text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
        return __("Category Search results for: '%1'", $this->_catalogSearchData->getEscapedQueryText());
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    protected function _getCategoryCollection()
    {
        if(null === $this->categoryCollection){
            $queryText = $this->_queryFactory->get()->getQueryText();
            $categoryCollection = $this->_categoryCollectionFactory->create();
            $categoryCollection->addAttributeToFilter('name', array('like'=>"%{$queryText}%"));
            $this->categoryCollection = $categoryCollection;
        }
        return $this->categoryCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedCategoryCollection()
    {
        return $this->_getCategoryCollection();
    }

    /**
     * Retrieve Toolbar block
     *
     * @return \MGS\InstantSearch\Block\SearchList\Toolbar
     */
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $block;
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        // called prepare sortable parameters
        $collection = $this->_getCategoryCollection();
        // set collection to toolbar and apply sort
        $toolbar->setCurrentType($this->currentType);
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        $this->_getCategoryCollection()->load();
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }
}