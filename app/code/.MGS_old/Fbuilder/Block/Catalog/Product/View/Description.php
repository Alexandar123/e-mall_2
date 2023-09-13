<?php
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Catalog\Product\View;

/**
 * Class Description
 */
class Description
{
    public function __construct(
        \MGS\Fbuilder\Helper\Builder $panelHelper,
        \Magento\Framework\View\Layout $layout,
        \Magento\Catalog\Model\Attribute\ScopeOverriddenValue $scopeOverriddenValue,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_panelHelper = $panelHelper;
        $this->_layout = $layout;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->_storeManager = $storeManager;
    }
    
    public function afterGetProduct(\Magento\Catalog\Block\Product\View\Description $subject, $product)
    {
        $canUsePanel = $this->_panelHelper->acceptToUsePanel();
        if ($canUsePanel) {
            $isOverriden = $this->scopeOverriddenValue->containsValue(
                \Magento\Catalog\Api\Data\ProductInterface::class,
                $product,
                'description',
                $this->_storeManager->getStore()->getId()
            );
            $description = $this->_layout->createBlock('MGS\Fbuilder\Block\Panel\ProductDescription')->setCanUsePanel($canUsePanel)->setProduct($product)->setScopeOverride($isOverriden)->setTemplate('panel/product_description.phtml')->toHtml();
            $product->setDescription($description);
        }
		
		if ($product->getDescription()) { 
			$productDescription = $this->_panelHelper->getContentByShortcode($product->getDescription());
			$product->setDescription($productDescription);
		}
        return $product;
    }
}
