<?php

namespace MGS\Brand\Model;

use Magento\Framework\DataObject\IdentityInterface;

class Brand extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    protected $_productCollectionFactory;
    protected $_storeManager;
    protected $_url;
    protected $_brandHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MGS\Brand\Model\Resource\Brand $resource = null,
        \MGS\Brand\Model\Resource\Brand\Collection $resourceCollection = null,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\UrlInterface $url,
        \MGS\Brand\Helper\Data $brandHelper,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_brandHelper = $brandHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('MGS\Brand\Model\Resource\Brand');
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('mgs_brand', ['eq' => $this->getOptionId()]);
        return $collection;
    }

    public function getUrl()
    {
        $url = $this->_storeManager->getStore()->getBaseUrl();
        $route = $this->_brandHelper->getConfig('general_settings/route');
        return $url . $route . '/' . $this->getUrlKey();
    }

    public function getSmallImageUrl()
    {
        $url = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'mgs_brand/no_image.png';
        $thumbnail = $this->getSmallImage();
        if ($thumbnail) {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $thumbnail;
        };
        return $url;
    }

    public function getImageUrl()
    {
        $url = false;
        $image = $this->getImage();
        if ($image) {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $image;
        };
        return $url;
    }
}
