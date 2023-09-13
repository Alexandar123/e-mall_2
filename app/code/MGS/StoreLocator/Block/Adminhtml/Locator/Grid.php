<?php

namespace MGS\StoreLocator\Block\Adminhtml\Locator;

class Grid extends \Magento\Backend\Block\Widget\Grid {

    /**
     * @var \MGS\StoreLocator\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MGS\StoreLocator\Model\StoreFactory $storeFactory
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \MGS\StoreLocator\Model\StoreFactory $storeFactory, array $data = []
    ) {
        $this->_storeFactory = $storeFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Prepare collection for grid
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection() {
        foreach($this->getCollection() as $storelocator) {
            $storelocator->setStore($storelocator->getStores());
        }

        return parent::_prepareCollection();
    }

}
