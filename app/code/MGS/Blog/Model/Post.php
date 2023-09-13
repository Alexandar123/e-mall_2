<?php

namespace MGS\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use MGS\Blog\Model\Resource\Post as PostResource;
use MGS\Blog\Model\Resource\Post\Collection;
use MGS\Blog\Helper\Data;
use Magento\Framework\UrlInterface;
use MGS\Blog\Model\Category;
use MGS\Blog\Model\Comment;

class Post extends AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    protected $storeManager;
    protected $blogHelper;
    protected $storeId;
    protected $resource;

    protected $category;

    protected $comment;

    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        PostResource $resource = null,
        Collection $resourceCollection = null,
        Data $blogHelper,
        Category $category,
        Comment $comment,
        \Magento\Store\Model\StoreManagerInterface $storeId,
        array $data = []
    )
    {
        $this->resource = $resource;
        $this->storeId = $storeId;
        $this->storeManager = $storeManager;
        $this->blogHelper = $blogHelper;
        $this->category = $category;
        $this->comment = $comment;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('MGS\Blog\Model\Resource\Post');
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getPostUrlWithNoCategory()
    {
        $route = $this->blogHelper->getRoute();
        return $route . '/' . $this->getUrlKey();
    }

    public function getTitleStore($postId) {

        return $this->storeId;
    }

    public function getPostUrlWithCategory($categoryId)
    {
        $route = $this->blogHelper->getRoute();
        return $route . '/' . $this->category->load($categoryId)->getUrlKey() . '/' . $this->getUrlKey();
    }

    public function getThumbnailUrl()
    {
        $url = $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . 'mgs_blog/no_image.png';
        $thumbnail = $this->getThumbnail();
        if ($thumbnail) {
            $url = $this->storeManager->getStore()->getBaseUrl(
                    UrlInterface::URL_TYPE_MEDIA
                ) .'mgs_blog/'. $thumbnail;
        };
        return $url;
    }

    public function getImageUrl()
    {
        $url = false;
        $image = $this->getImage();
        if ($image) {
            $url = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) .'mgs_blog/'.$image;
        };
        return $url;
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData('created_at', $createdAt);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData('updated_at', $updatedAt);
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    public function setPublishedAt($publishedAt){
        return $this->setData('published_at', $publishedAt);
    }

    public function getPublishedAt(){
        return $this->getData('published_at');
    }


    // /**
    //  * @inheritdoc
    //  */
    // public function getStoreId()
    // {
    //     if (!$this->hasStoreId()) {
    //         return $this->_storeManager->getStore()->getId();
    //     }
    //     return (int)$this->_getData(self::KEY_STORE_ID);
    // }

    // /**
    //  * @inheritdoc
    //  */
    // public function setStoreId($storeId)
    // {
    //     $this->setData(self::KEY_STORE_ID, (int)$storeId);
    //     return $this;
    // }

    /**
     * Retrieve store where customer was created
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->getStoreId());
    }

    /**
     * Set store to customer
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore(\Magento\Store\Model\Store $store)
    {
        $this->setStoreId($store->getId());
        return $this;
    }

    public function getCatetories()
    {
        $catetories = $this->category->getCollection()
            // ->addPostFilter($this->getId())
            ->addStoreFilter($this->storeManager->getStore()->getId());
        return $catetories;
    }

    public function getCommentCount()
    {
        $comments = $this->comment->getCollection()
            ->addFieldToFilter('post_id', ['eq' => $this->getId()])
			->addFieldToFilter('status', ['eq' => 1]);
        return count($comments);
    }
}
