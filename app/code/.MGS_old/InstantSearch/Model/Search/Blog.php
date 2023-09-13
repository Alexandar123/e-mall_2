<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Model\Search;
use Magento\Search\Model\QueryFactory;
use MGS\InstantSearch\Helper\Data;
use MGS\InstantSearch\Model\Source\BlogFields;
use Magento\Framework\ObjectManagerInterface;
/**
 * Blog model. Return blog data used in search autocomplete
 */
class Blog implements \MGS\InstantSearch\Model\SearchInterface
{

	/**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterf‌​ace
     */
    protected $_storeManager;

    /**
     * @var Data
     */
    protected $_inSearchHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Blog constructor.
     *
     * @param \MGS\Blog\Model\Post $postFactory
     * @param StoreManagerInterf‌​ace $storeManager
     * @param QueryFactory $queryFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Data $inSearchHelper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        ObjectManagerInterface $objectManager,
        Data $inSearchHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_queryFactory = $queryFactory;
        $this->_inSearchHelper = $inSearchHelper;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseData()
    {
        if($this->_inSearchHelper->isModuleEnabled('MGS_Blog') && $this->_inSearchHelper->isOutputEnabled('MGS_Blog') && $this->_inSearchHelper->isBlogSearch()){
            $queryText = $this->_queryFactory->get()->getQueryText();
            $posts = $this->getPostCollection($queryText);
            foreach ($posts as $_post) {
                $responseData['data'][] = $this->getBlogData($_post);

            }

            $responseData['size'] = $posts->getSize();
            $responseData['url'] = ($posts->getSize() > 0) ? $this->_inSearchHelper->getResultUrl('instantsearch/blog/result',$queryText) : '';
            return $responseData;
        }
        $responseData['size'] = 0;
        return $responseData;
    }

    /**
     * Retrieve loaded post collection
     *
     * @return Collection
     */
    private function getPostCollection($queryText)
    {
        $limit = $this->_inSearchHelper->getNumberResult();
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
        return $postCollection;
    }

    /**
     * Retrieve loaded category detail
     *
     * @return array
     */
    private function getBlogData($_post)
    {
        $postData = [
            BlogFields::NAME => $_post->getTitle(),
            BlogFields::SHORT_CONTENT => $_post->getShortContent(),
            BlogFields::THUMBNAIL => $this->getImageThumbnailPost($_post),
            BlogFields::URL => $_post->getPostUrlWithNoCategory()
        ];

        return $postData;
    }
	/**
     * @param \MGS\Blog\Model\Post $_post
     * @return string
     */
    public function getImageThumbnailPost($_post){
        return $this->_inSearchHelper->getImageThumbnailPost($_post);
    }
}