<?php
/**
 * Copyright © 2017 Sam Granger. All rights reserved.
 *
 * @author Sam Granger <sam.granger@gmail.com>
 */

namespace MGS\ThemeSettings\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config;
use Magento\Store\Model\StoreManagerInterface;


class ProcessThemeSettingConfig implements ObserverInterface
{
    protected $config;
    protected $storeManager;
    protected $scopeConfig;
	protected $_request;
	protected $_registry;


    public function __construct(
        \Magento\Framework\View\Page\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\App\Request\Http $request
    ){
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;

		$this->_request = $request;
		$this->_registry = $registry;
    }

    public function execute(Observer $observer){
        $storeId = $this->storeManager->getStore()->getId();
		$configLayout = $this->getStoreConfig('themesettings/general/layout', $storeId);
		$width = $this->getStoreConfig('themesettings/general/width', $storeId);
		$parallaxFooter = $this->getStoreConfig('themesettings/footer/parallax', $storeId);
        $lazyLoad = $this->getStoreConfig('themesettings/general/lazy_load', $storeId);

		$this->config->addBodyClass($width);

        $this->config->addBodyClass($configLayout);

		if($parallaxFooter){
			$this->config->addBodyClass('parallax-footer');
		}

		if($lazyLoad){
			$this->config->addBodyClass('lazy-loading-img');
		}


		$layout = $observer->getLayout();
		$fullActionName = $this->_request->getFullActionName();

		if($this->getStoreConfig('themesettings/header/header_version', $storeId) == 'header7'){
			$this->config->addBodyClass('left-side');
		}

		if($this->getStoreConfig('themesettings/header/header_absolute', $storeId)){
			$this->config->addBodyClass('header_absolute');
		}

		/* Catalog Search Page */
		if($fullActionName=='catalogsearch_result_index'){
			$searchPageLayout = $this->getStoreConfig('themesettings/catalog_search/layout', $storeId);
			if($searchPageLayout!=''){
				$layout->getUpdate()->addHandle($searchPageLayout);
			}
		}

		/* Category Page */
		if($fullActionName=='catalog_category_view'){
			$category = $this->_registry->registry('current_category');
			if($category->getFullWidth()){
				$this->config->addBodyClass('category-fullwidth');
			}

			$_categoryLayout = $category->getPageLayout();
			$categoryPageLayout = $this->getStoreConfig('themesettings/category/layout', $storeId);
			if(($_categoryLayout=='') && ($categoryPageLayout!='') && (!$category->getIsLanding())){
				$layout->getUpdate()->addHandle($categoryPageLayout);
			}

			if($category->getIsLanding()){
				$this->config->addBodyClass('category-landing');
				$this->config->addBodyClass('landing-'.$category->getCateLandingType());
				$layout->getUpdate()->addHandle('themesetting_onecolumn_empty_custom');
			}
		}

		/* Product Details Page */
		if($fullActionName=='catalog_product_view'){
			$product = $this->_registry->registry('current_product');
			// Page Layout
			$_productLayout = $product->getPageLayout();
			$productPageLayout = $this->getStoreConfig('themesettings/product_details/layout', $storeId);
			if(($_productLayout=='') && ($productPageLayout!='')){
				$layout->getUpdate()->addHandle($productPageLayout);
			}

			// Template
			$template = $this->getStoreConfig('themesettings/product_details/default_template', $storeId);
			if($product->getMgsTemplate() && $product->getMgsTemplate()!=''){
				$template = $product->getMgsTemplate();
			}
			$layout->getUpdate()->addHandle($template);
		}

		/* Contact Us Page */
		if($fullActionName=='contact_index_index'){
			// Page Layout
			$pageLayout = $this->getStoreConfig('themesettings/contact/layout', $storeId);
			if($pageLayout!=''){
				$layout->getUpdate()->addHandle($pageLayout);
			}
		}

    }

	public function getStoreConfig($node, $storeId = NULL){
		return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
	}
}
