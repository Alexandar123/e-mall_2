<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Fbuilder\Helper;

/**
 * Contact base helper
 */
class CategoryBuilder extends \MGS\Fbuilder\Helper\Builder
{
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $url,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Model\Attribute\ScopeOverriddenValue $scopeOverriddenValue
    ) {
        parent::__construct($storeManager, $url, $request, $context, $objectManager, $pageFactory, $customerSession, $filterProvider);
        $this->scopeOverriddenValue = $scopeOverriddenValue;
    }
    
    public function getScopeOverride($category)
    {
        $isOverriden = $this->scopeOverriddenValue->containsValue(
            \Magento\Catalog\Api\Data\CategoryInterface::class,
            $category,
            'description',
            $this->_storeManager->getStore()->getId()
        );
        return $isOverriden;
    }
}
