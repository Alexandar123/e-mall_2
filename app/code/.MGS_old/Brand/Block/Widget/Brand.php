<?php

namespace MGS\Brand\Block\Widget;

class Brand extends AbstractWidget
{
    protected $_brand;
    protected $_coreRegistry = null;
    protected $_brandHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Brand\Helper\Data $brandHelper,
        \MGS\Brand\Model\Brand $brand,
        array $data = []
    )
    {
        $this->_brand = $brand;
        $this->_brandHelper = $brandHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $brandHelper);
    }

    public function _toHtml()
    {
        if (!$this->_brandHelper->getConfig('general_settings/enabled')) return;
        $template = $this->getConfig('template');
        $this->setTemplate($template);
        return parent::_toHtml();
    }

    public function getBrandCollection()
    {
        $brandIds = $this->getConfig('brand_ids');
        $collection = $this->_brand->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId());
        $brandIds = explode(',', $brandIds);
        if (is_array($brandIds)) {
            $collection->addFieldToFilter('brand_id', array('in' => $brandIds));
        }
        $collection->setOrder('sort_order', 'ASC');
        return $collection;
    }
}