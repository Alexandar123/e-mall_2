<?php

namespace MGS\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use MGS\Blog\Model\Resource\Comment as CommentResource;
use MGS\Blog\Model\Resource\Comment\Collection;
use MGS\Blog\Helper\Data;

class Comment extends AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    protected $storeManager;
    protected $blogHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        CommentResource $resource = null,
        Collection $resourceCollection = null,
        Data $blogHelper,
        array $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->blogHelper = $blogHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('MGS\Blog\Model\Resource\Comment');
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Approved'), self::STATUS_DISABLED => __('Unapproved')];
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
}
