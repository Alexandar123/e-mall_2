<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Model\Search\Cms;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Search\Model\QueryFactory;
use MGS\InstantSearch\Helper\Data;
use \Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use MGS\InstantSearch\Model\Source\Cms\PageFields;
/**
 * Cms page model. Return cms page data used in search autocomplete
 */
class Page implements \MGS\InstantSearch\Model\SearchInterface
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
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $_pageCollectionFactory;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * Product constructor.
     *
     * @param Data $inSearchHelper
     * @param ObjectManager $objectManager
     * @param QueryFactory $queryFactory
     * @param CollectionFactory $pageCollectionFactory
     */
    public function __construct(
        ObjectManager $objectManager,
        QueryFactory $queryFactory,
        CollectionFactory $pageCollectionFactory,
        Data $inSearchHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->_queryFactory = $queryFactory;
        $this->_pageCollectionFactory = $pageCollectionFactory;
        $this->_inSearchHelper = $inSearchHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseData()
    {
        if($this->_inSearchHelper->isCmsPageSearch()){
            $queryText = $this->_queryFactory->get()->getQueryText();
            $pages = $this->getCmsPageCollection($queryText);
            foreach ($pages as $_page) {
                $responseData['data'][] = $this->getCmsPageData($_page);
            }

            $responseData['size'] = $pages->getSize();
            $responseData['url'] = ($pages->getSize() > 0) ? $this->_inSearchHelper->getResultUrl('instantsearch/page/result',$queryText) : '';
            return $responseData;
        }
        $responseData['size'] = 0;
        return $responseData;
    }

    /**
     * Retrieve loaded cms page collection
     *
     * @return Collection
     */
    private function getCmsPageCollection($queryText)
    {
        $limit = $this->_inSearchHelper->getNumberResult();
        $pageCollection = $this->_pageCollectionFactory->create();
        $pageCollection->addFieldToFilter(
                            ['title', 'content'],
                            [['like' => "%{$queryText}%"], ['like' => "%{$queryText}%"]]
                        )
                        ->addFieldToFilter('is_active', 1)->addStoreFilter($this->_inSearchHelper->getStoreId());
        $pageCollection->getSelect()->limit($limit);
        return $pageCollection;
    }

    /**
     * Retrieve loaded cms page detail
     *
     * @return array
     */
    private function getCmsPageData($_page)
    {
        $pageData = [
            PageFields::TITLE => $_page->getTitle(),
            PageFields::URL => $this->_inSearchHelper->getUrl($_page->getIdentifier())
        ];

        return $pageData;
    }
}