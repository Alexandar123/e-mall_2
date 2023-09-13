<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Block\SearchResult;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use \Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use MGS\InstantSearch\Helper\Data;
/**
 * Search result block
 */
class ListPage extends Template
{
	/**
     * Cms Page Collection
     *
     * @var AbstractCollection
     */
    protected $pageCollection;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    private $currentType = 'cms_page';

    private $limit = 12;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $_pageCollectionFactory;
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
        CollectionFactory $pageCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context,$data);
        $this->_queryFactory = $queryFactory;
        $this->_inSearchHelper = $inSearchHelper;
        $this->_pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * Retrieve loaded cms page collection
     *
     * @return AbstractCollection
     */
    protected function _getPageCollection()
    {
        if(null === $this->pageCollection){
            $limit = $this->getPositionLimit() ? $this->getPositionLimit() : $this->limit;
            $queryText = $this->_queryFactory->get()->getQueryText();
            $pageCollection = $this->_pageCollectionFactory->create();
            $pageCollection->addFieldToFilter(
                            ['title', 'content'],
                            [['like' => "%{$queryText}%"], ['like' => "%{$queryText}%"]]
                        )
                        ->addFieldToFilter('is_active', 1)->addStoreFilter($this->_inSearchHelper->getStoreId());
            $pageCollection->getSelect()->limit($limit);
            $this->pageCollection = $pageCollection;
        }
        return $this->pageCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedPageCollection()
    {
        return $this->_getPageCollection();
    }

    /*
     * return number result
     * @return string
     */
    public function getPositionLimit()
    {
        $limits = $this->getAvailableLimit();
        $defaultLimit = $this->getDefaultPerPageValue();
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0];
        }
        $limit = $defaultLimit;
        return $limit;

    }

    /**
     * Retrieve default per page values
     *
     * @return string (comma separated)
     */
    public function getDefaultPerPageValue()
    {
        return $this->_inSearchHelper->getDefaultLimitPerPageValue($this->currentType);
    }

    /**
     * Retrieve available limits for current view mode
     *
     * @return array
     */
    public function getAvailableLimit()
    {
        return $this->_inSearchHelper->getAvailableLimit($this->currentType);
    }

    /*
     * @return string
     */
    public function getViewMoreLabel()
    {
        return $this->_inSearchHelper->getViewMoreLabel();
    }

    /**
     * Retrieve result page url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @param   string $query
     * @return  string
     */
    public function getResultUrl()
    {
        $query = $this->_queryFactory->get()->getQueryText();
        $url = 'instantsearch/page/result';
        return $this->_inSearchHelper->getResultUrl($url,$query);
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
}