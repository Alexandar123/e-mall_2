<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Block\SearchResult;
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
     * Post Collection
     *
     * @var AbstractCollection
     */
    protected $postCollection;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    private $currentType = 'blog';

    private $limit = 12;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterf‌​ace
     */
    protected $_storeManager;

	/**
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param Data $inSearchHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        Data $inSearchHelper,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context,$data);
        $this->_queryFactory = $queryFactory;
        $this->_inSearchHelper = $inSearchHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve loaded post collection
     *
     * @return AbstractCollection
     */
    protected function _getPostCollection()
    {
        if(null === $this->postCollection){
            $limit = $this->getPositionLimit() ? $this->getPositionLimit() : $this->limit;
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
            $postCollection->getSelect()->limit($limit);
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

    /**
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
        $url = 'instantsearch/blog/result';
        return $this->_inSearchHelper->getResultUrl($url,$query);
    }

    /**
     * @param \MGS\Blog\Model\Post $_post
     * @return string
     */
    public function getImageThumbnailPost($_post){
		return $this->_inSearchHelper->getImageThumbnailPost($_post, true);
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