<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\InstantSearch\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class CatalogSearchLayoutUpdate
 * @package MGS\InstantSearch\Observer
 */
class CatalogSearchLayoutUpdate implements ObserverInterface
{

    /**
     * @var \MGS\InstantSearch\Helper\Data
     */
    private $_instantHelper;

    /**
     * @var string
     */
    private $searchHandle = 'instant_search';

    /**
     * @param \MGS\InstantSearch\Helper\Data $instantHelper
     */
    public function __construct(
        \MGS\InstantSearch\Helper\Data $instantHelper
    ) {
        $this->_instantHelper = $instantHelper;
    }

    /**
     * Add swatches
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_instantHelper->isEnableFrontend() && ($this->_instantHelper->isBlogSearch() && 
            $this->_instantHelper->isModuleEnabled('MGS_Blog') || $this->_instantHelper->isCategorySearch() 
            || $this->_instantHelper->isCmsPageSearch() || $this->_instantHelper->isProductSearch())){
            $observer->getEvent()
                ->getLayout()
                ->getUpdate()
                ->addHandle($this->searchHandle);
        }
    }
}
