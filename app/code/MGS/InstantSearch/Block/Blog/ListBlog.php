<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Block\Blog;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MGS\InstantSearch\Helper\Data;
use Magento\Framework\ObjectManagerInterface;

/**
 * Search result block
 */
class ListBlog extends Template
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'MGS\InstantSearch\Block\SearchList\Toolbar';
    /**
     * Posts Collection
     *
     * @var AbstractCollection
     */
    protected $postCollection;

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
    private $currentType = 'blog';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param Data $inSearchHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     * @param array $data
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        Data $inSearchHelper,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context,$data);
        $this->_queryFactory = $queryFactory;
        $this->_inSearchHelper = $inSearchHelper;
        $this->_catalogSearchData = $catalogSearchData;
        $this->objectManager = $objectManager;
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
        return __("Posts Search results for: '%1'", $this->_catalogSearchData->getEscapedQueryText());
    }

    /**
     * Retrieve loaded post collection
     *
     * @return AbstractCollection
     */
    protected function _getPostCollection()
    {
        if(null === $this->postCollection){
            $queryText = $this->_queryFactory->get()->getQueryText();
            $postFactory = $this->objectManager->create('MGS\Blog\Model\Post');
            $postCollection = $postFactory->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter(['title','short_content','content'], 
                    [
                        ['like'=>"%{$queryText}%"],
                        ['like'=>"%{$queryText}%"],
                        ['like'=>"%{$queryText}%"]
                    ]
                )
                ->addStoreFilter($this->_storeManager->getStore()->getId());
            $this->postCollection = $postCollection;
        }
        return $this->postCollection;
    }

    /**
     * Retrieve loaded post collection
     *
     * @return AbstractCollection
     */
    public function getLoadedPostCollection()
    {
        return $this->_getPostCollection();
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
        $collection = $this->_getPostCollection();
        // set collection to toolbar and apply sort
        $toolbar->setCurrentType($this->currentType);
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        $this->_getPostCollection()->load();
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

    /**
     * @param \MGS\Blog\Model\Post $_post
     * @return string
     */
    public function getImageThumbnailPost($_post){
        return $this->_inSearchHelper->getImageThumbnailPost($_post, true);
    }
}