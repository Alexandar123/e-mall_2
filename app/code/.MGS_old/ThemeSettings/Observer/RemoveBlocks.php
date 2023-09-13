<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ThemeSettings\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveBlocks implements ObserverInterface
{
	protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }
	
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
		
		/* Category Page */
		// Category Image Position
		$imageBlock = $layout->getBlock('category.image');
		$imageTopContentBlock = $layout->getBlock('category.image.top.content');
		$imageBottomContentBlock = $layout->getBlock('category.image.bottom.content');
		$imageBottomPage = $layout->getBlock('category.image.bottom.page');
		$imagePosition = $this->_scopeConfig->getValue('themesettings/category/image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		switch($imagePosition){
			case 'top':
				if($imageBlock && $imageBottomContentBlock && $imageBottomPage){
					$layout->unsetElement('category.image');
					$layout->unsetElement('category.image.bottom.content');
					$layout->unsetElement('category.image.bottom.page');
				}
				break;
			case 'bottom':
				if($imageBlock && $imageTopContentBlock && $imageBottomPage){
					$layout->unsetElement('category.image');
					$layout->unsetElement('category.image.top.content');
					$layout->unsetElement('category.image.bottom.page');
				}
				break;
			case 'page':
				if($imageBlock && $imageTopContentBlock && $imageBottomContentBlock){
					$layout->unsetElement('category.image');
					$layout->unsetElement('category.image.top.content');
					$layout->unsetElement('category.image.bottom.content');
				}
				break;
			default:
				if($imageTopContentBlock && $imageBottomContentBlock && $imageBottomPage){
					$layout->unsetElement('category.image.top.content');
					$layout->unsetElement('category.image.bottom.content');
					$layout->unsetElement('category.image.bottom.page');
				}
				break;
		}
		
		// Category Description Position
		$descriptionBlock = $layout->getBlock('category.description');
		$descriptionTopContentBlock = $layout->getBlock('category.description.top.content');
		$descriptionBottomContentBlock = $layout->getBlock('category.description.bottom.content');
		$descriptionBottomPage = $layout->getBlock('category.description.bottom.page');
		$descriptionPosition = $this->_scopeConfig->getValue('themesettings/category/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		switch($descriptionPosition){
			case 'top':
				if($descriptionBlock && $descriptionBottomContentBlock && $descriptionBottomPage){
					$layout->unsetElement('category.description');
					$layout->unsetElement('category.description.bottom.content');
					$layout->unsetElement('category.description.bottom.page');
				}
				break;
			case 'bottom':
				if($descriptionBlock && $descriptionTopContentBlock && $descriptionBottomPage){
					$layout->unsetElement('category.description');
					$layout->unsetElement('category.description.top.content');
					$layout->unsetElement('category.description.bottom.page');
				}
				break;
			case 'page':
				if($descriptionBlock && $descriptionTopContentBlock && $descriptionBottomContentBlock){
					$layout->unsetElement('category.description');
					$layout->unsetElement('category.description.top.content');
					$layout->unsetElement('category.description.bottom.content');
				}
				break;
			default:
				if($descriptionTopContentBlock && $descriptionBottomContentBlock && $descriptionBottomPage){
					$layout->unsetElement('category.description.top.content');
					$layout->unsetElement('category.description.bottom.content');
					$layout->unsetElement('category.description.bottom.page');
				}
				break;
		}
		
		// CMS Block Position
		$cmsBlock = $layout->getBlock('category.cms');
		$cmsTopContentBlock = $layout->getBlock('category.cms.top.content');
		$cmsBottomContentBlock = $layout->getBlock('category.cms.bottom.content');
		$cmsBottomPage = $layout->getBlock('category.cms.bottom.page');
		$cmsPosition = $this->_scopeConfig->getValue('themesettings/category/cms', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		switch($cmsPosition){
			case 'top':
				if($cmsBlock && $cmsBottomContentBlock && $cmsBottomPage){
					$layout->unsetElement('category.cms');
					$layout->unsetElement('category.cms.bottom.content');
					$layout->unsetElement('category.cms.bottom.page');
				}
				break;
			case 'bottom':
				if($cmsBlock && $cmsTopContentBlock && $cmsBottomPage){
					$layout->unsetElement('category.cms');
					$layout->unsetElement('category.cms.top.content');
					$layout->unsetElement('category.cms.bottom.page');
				}
				break;
			case 'page':
				if($cmsBlock && $cmsTopContentBlock && $cmsBottomContentBlock){
					$layout->unsetElement('category.cms');
					$layout->unsetElement('category.cms.top.content');
					$layout->unsetElement('category.cms.bottom.content');
				}
				break;
			default:
				if($cmsTopContentBlock && $cmsBottomContentBlock && $cmsBottomPage){
					$layout->unsetElement('category.cms.top.content');
					$layout->unsetElement('category.cms.bottom.content');
					$layout->unsetElement('category.cms.bottom.page');
				}
				break;
		}
		
		/* Product Details */
        $skuBlock = $layout->getBlock('product.info.sku');
        $stockSimpleBlock = $layout->getBlock('product.info.simple');
        $stockVirtualBlock = $layout->getBlock('product.info.virtual');
        $stockConfigurableBlock = $layout->getBlock('product.info.configurable');
        $stockGroupedBlock = $layout->getBlock('product.info.grouped.stock');
        $reviewBlock = $layout->getBlock('product.info.review');
        $wishlistBlock = $layout->getBlock('view.addto.wishlist');
        $compareBlock = $layout->getBlock('view.addto.compare');
        $mailtoBlock = $layout->getBlock('product.info.mailto');
        $shortDescriptionBlock = $layout->getBlock('product.info.overview');
        $relatedBlock = $layout->getBlock('catalog.product.related');
        $relatedSidebarBlock = $layout->getBlock('catalog.product.related.sidebar');
        $upsellBlock = $layout->getBlock('product.info.upsell');
        $upsellSidebarBlock = $layout->getBlock('product.info.upsell.sidebar');
        if ($skuBlock && !$this->_scopeConfig->getValue('themesettings/product_details/sku', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('product.info.sku');
        }
		
		if ($stockSimpleBlock && !$this->_scopeConfig->getValue('themesettings/product_details/stock_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('product.info.simple');
        }
		if ($stockVirtualBlock && !$this->_scopeConfig->getValue('themesettings/product_details/stock_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('product.info.virtual');
        }
		if ($stockConfigurableBlock && !$this->_scopeConfig->getValue('themesettings/product_details/stock_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('product.info.configurable');
        }
		if ($stockGroupedBlock && !$this->_scopeConfig->getValue('themesettings/product_details/stock_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('product.info.grouped.stock');
        }
		if ($reviewBlock && !$this->_scopeConfig->getValue('themesettings/product_details/reviews_summary', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('product.info.review');
        }
		if ($wishlistBlock && !$this->_scopeConfig->getValue('themesettings/product_details/wishlist', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('view.addto.wishlist');
        }
		if ($compareBlock && !$this->_scopeConfig->getValue('themesettings/product_details/compare', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('view.addto.compare');
        }
		if ($mailtoBlock && !$this->_scopeConfig->getValue('themesettings/product_details/email_to_friend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('product.info.mailto');
        }
		if ($shortDescriptionBlock && !$this->_scopeConfig->getValue('themesettings/product_details/short_description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('product.info.overview');
        }
		if($this->_scopeConfig->getValue('themesettings/product_details/related_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
			$relatedPosition = $this->_scopeConfig->getValue('themesettings/product_details/related_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			if($relatedPosition=='main_content'){
				$layout->unsetElement('catalog.product.related.sidebar');
			}else{
				$layout->unsetElement('catalog.product.related');
			}
		}else{
			if ($relatedBlock && $relatedSidebarBlock) {
				$layout->unsetElement('catalog.product.related');
				$layout->unsetElement('catalog.product.related.sidebar');
			}
		}
		
		if($this->_scopeConfig->getValue('themesettings/product_details/upsell_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
			$upsellPosition = $this->_scopeConfig->getValue('themesettings/product_details/upsell_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			if($upsellPosition=='main_content'){
				$layout->unsetElement('product.info.upsell.sidebar');
			}else{
				$layout->unsetElement('product.info.upsell');
			}
		}else{
			if ($upsellBlock && $upsellSidebarBlock) {
				$layout->unsetElement('product.info.upsell');
				$layout->unsetElement('product.info.upsell.sidebar');
			}	
		}
		
		/* Shopping Cart Page */
		$couponBlock = $layout->getBlock('checkout.cart.coupon');
		$crossselBlock = $layout->getBlock('checkout.cart.crosssell');
		if ($couponBlock && !$this->_scopeConfig->getValue('themesettings/shopping_cart/show_coupon', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('checkout.cart.coupon');
        }
		if ($crossselBlock && !$this->_scopeConfig->getValue('themesettings/shopping_cart/show_crosssell', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
			$layout->unsetElement('checkout.cart.crosssell');
        }
		
		/* Customer Links */
		// Group 1
		$dashboardLink = $layout->getBlock('customer-account-navigation-account-link');
		$dashboardConfig = $this->_scopeConfig->getValue('themesettings/customer/account_dashboard', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($dashboardLink && $dashboardConfig) {
			$layout->unsetElement('customer-account-navigation-account-link');
        }
		$orderLink = $layout->getBlock('customer-account-navigation-orders-link');
		$orderConfig = $this->_scopeConfig->getValue('themesettings/customer/orders', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($orderLink && $orderConfig) {
			$layout->unsetElement('customer-account-navigation-orders-link');
        }
		$downloadLink = $layout->getBlock('customer-account-navigation-downloadable-products-link');
		$downloadConfig = $this->_scopeConfig->getValue('themesettings/customer/downloadable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($downloadLink && $downloadConfig) {
			$layout->unsetElement('customer-account-navigation-downloadable-products-link');
        }
		$wishlistLink = $layout->getBlock('customer-account-navigation-wish-list-link');
		$wishlistConfig = $this->_scopeConfig->getValue('themesettings/customer/wishlist', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($wishlistLink && $wishlistConfig) {
			$layout->unsetElement('customer-account-navigation-wish-list-link');
        }
		$line1 = $layout->getBlock('customer-account-navigation-delimiter-1');
		if($line1 && $dashboardConfig && $orderConfig && $downloadConfig && $wishlistConfig){
			$layout->unsetElement('customer-account-navigation-delimiter-1');
		}
		
		
		// Group 2
		$addressLink = $layout->getBlock('customer-account-navigation-address-link');
		$addressConfig = $this->_scopeConfig->getValue('themesettings/customer/address_book', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($addressLink && $addressConfig) {
			$layout->unsetElement('customer-account-navigation-address-link');
        }
		$infoLink = $layout->getBlock('customer-account-navigation-account-edit-link');
		$infoConfig = $this->_scopeConfig->getValue('themesettings/customer/account_information', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($infoLink && $infoConfig) {
			$layout->unsetElement('customer-account-navigation-account-edit-link');
        }
		$paymentLink = $layout->getBlock('customer-account-navigation-my-credit-cards-link');
		$paymentConfig = $this->_scopeConfig->getValue('themesettings/customer/creditcart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($paymentLink && $paymentConfig) {
			$layout->unsetElement('customer-account-navigation-my-credit-cards-link');
        }
		$billingLink = $layout->getBlock('customer-account-navigation-billing-agreements-link');
		$billingConfig = $this->_scopeConfig->getValue('themesettings/customer/billing_agreements', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($billingLink && $billingConfig) {
			$layout->unsetElement('customer-account-navigation-billing-agreements-link');
        }
		$line2 = $layout->getBlock('customer-account-navigation-delimiter-2');
		if($line2 && $addressConfig && $infoConfig && $paymentConfig && $billingConfig){
			$layout->unsetElement('customer-account-navigation-delimiter-2');
		}
		
		// Group 3
		$gdprLink = $layout->getBlock('customer-account-navigation-gdpr-link');
		$line3 = $layout->getBlock('customer-account-navigation-delimiter-gdpr');
		$gdprConfig = $this->_scopeConfig->getValue('themesettings/customer/gdpr', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($gdprLink && $gdprConfig) {
			$layout->unsetElement('customer-account-navigation-gdpr-link');
			if ($line3) {
				$layout->unsetElement('customer-account-navigation-delimiter-gdpr');
			}
        }
		
		
		$newsletterLink = $layout->getBlock('customer-account-navigation-newsletter-subscriptions-link');
		$newsletterConfig = $this->_scopeConfig->getValue('themesettings/customer/newsletter', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($newsletterLink && $newsletterConfig) {
			$layout->unsetElement('customer-account-navigation-newsletter-subscriptions-link');
        }
		
		// Group 4
		$reviewLink = $layout->getBlock('customer-account-navigation-product-reviews-link');
		$reviewConfig = $this->_scopeConfig->getValue('themesettings/customer/reviews', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($reviewLink && $reviewConfig) {
			$layout->unsetElement('customer-account-navigation-product-reviews-link');
        }
		$newsletterLink = $layout->getBlock('customer-account-navigation-newsletter-subscriptions-link');
		$newsletterConfig = $this->_scopeConfig->getValue('themesettings/customer/newsletter', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($newsletterLink && $newsletterConfig) {
			$layout->unsetElement('customer-account-navigation-newsletter-subscriptions-link');
        }
    }
}
