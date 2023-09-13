<?php

namespace MGS\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use MGS\Blog\Model\Resource\Tag as TagResource;
use MGS\Blog\Model\Resource\Tag\Collection;
use MGS\Blog\Helper\Data;

class Tag extends AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    protected $storeManager;
    protected $blogHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        TagResource $resource = null,
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
        $this->_init('MGS\Blog\Model\Resource\Tag');
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
