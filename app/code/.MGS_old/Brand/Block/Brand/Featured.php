<?php

namespace MGS\Brand\Block\Brand;

use Magento\Customer\Model\Context as CustomerContext;

class Featured extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_brandHelper;
    protected $_brand;
    protected $httpContext;
    protected $_catalogProductVisibility;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Brand\Helper\Data $brandHelper,
        \MGS\Brand\Model\Brand $brand,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        array $data = []
    )
    {
        $this->_brand = $brand;
        $this->_coreRegistry = $registry;
        $this->_brandHelper = $brandHelper;
        $this->httpContext = $httpContext;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enabled')) return;
        parent::_construct();        
        $brand = $this->_brand;
        $brandCollection = $brand->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('is_featured', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('sort_order', 'ASC');
        $this->setCollection($brandCollection);
    }    

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_collection;
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_brandHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getProductCount(\MGS\Brand\Model\Brand $brand)
    {
        $collection = $brand->getProductCollection();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        return count($collection);
    }

}
