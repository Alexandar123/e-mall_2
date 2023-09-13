<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Portfolio\Helper;

/**
 * Portfolio base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_request = $request;
    }


    /**
     * @param $content
     * @return array|string|string[]
     */
    public function decodeHtmlTag($content){
        $result = str_replace("&lt;","<",$content);
        $result = str_replace("&gt;",">",$result);
        $result = str_replace('&#34;','"',$result);
        $result = str_replace("&#39;","'",$result);
        return $result;
    }

    /**
     * @param null $perrow
     * @return string|void
     */
    public function getColClass($perrow = NULL){
        if(!$perrow){
            $settings = $this->getThemeSettings();
            $perrow = $settings['catalog']['per_row'];

            if($this->_request->getFullActionName() == 'catalog_category_view'){
                $category = $this->getCurrentCategory();
                $categoryPerrow = $category->getPerRow();
                if($categoryPerrow!=''){
                    $perrow = $categoryPerrow;
                }
            }

            if($this->_request->getFullActionName() == 'catalogsearch_result_index'){
                $perrow = $settings['catalogsearch']['per_row'];
            }

        }

        switch($perrow){
            case 2:
                return 'col-lg-6 col-md-6 col-sm-6 col-xs-6';
                break;
            case 3:
                return 'col-lg-4 col-md-4 col-sm-4 col-xs-6';
                break;
            case 4:
                return 'col-lg-3 col-md-3 col-sm-6 col-xs-6';
                break;
            case 5:
                return 'col-lg-custom-5 col-md-custom-5 col-sm-6 col-xs-6';
                break;
            case 6:
                return 'col-lg-2 col-md-2 col-sm-3 col-xs-6';
                break;
            case 7:
                return 'col-lg-custom-7 col-md-custom-7 col-sm-6 col-xs-6';
                break;
            case 8:
                return 'col-lg-custom-8 col-md-custom-8 col-sm-6 col-xs-6';
                break;
        }
        return;
    }

    /**
     * @return array[]
     */
    public function getThemeSettings(){
        return [
            'catalog'=>
                [
                    'per_row' => $this->getStoreConfig('mpanel/catalog/product_per_row'),
                    'featured' => $this->getStoreConfig('mpanel/catalog/featured'),
                    'hot' => $this->getStoreConfig('mpanel/catalog/hot'),
                    'ratio' => $this->getStoreConfig('mpanel/catalog/picture_ratio'),
                    'new_label' => $this->getStoreConfig('mpanel/catalog/new_label'),
                    'sale_label' => $this->getStoreConfig('mpanel/catalog/sale_label'),
                    'preload' => $this->getStoreConfig('mpanel/catalog/preload'),
                    'ajaxscroll' => $this->getStoreConfig('mpanel/catalog/ajaxscroll'),
                    'wishlist_button' => $this->getStoreConfig('mpanel/catalog/wishlist_button'),
                    'compare_button' => $this->getStoreConfig('mpanel/catalog/compare_button'),
                    'sub_categories' => $this->getStoreConfig('mpanel/catalog/sub_categories')
                ],
            'catalogsearch'=>
                [
                    'per_row' => $this->getStoreConfig('mpanel/catalogsearch/product_per_row')
                ],
            'catalog_brand'=>
                [
                    'per_row' => $this->getStoreConfig('brand/list_page_settings/product_per_row')
                ],
            'product_details'=>
                [
                    'sku' => $this->getStoreConfig('mpanel/product_details/sku'),
                    'reviews_summary' => $this->getStoreConfig('mpanel/product_details/reviews_summary'),
                    'wishlist' => $this->getStoreConfig('mpanel/product_details/wishlist'),
                    'compare' => $this->getStoreConfig('mpanel/product_details/compare'),
                    'preload' => $this->getStoreConfig('mpanel/product_details/preload'),
                    'short_description' => $this->getStoreConfig('mpanel/product_details/short_description'),
                    'upsell_products' => $this->getStoreConfig('mpanel/product_details/upsell_products')
                ],
            'product_tabs'=>
                [
                    'show_description' => $this->getStoreConfig('mpanel/product_tabs/show_description'),
                    'show_additional' => $this->getStoreConfig('mpanel/product_tabs/show_additional'),
                    'show_reviews' => $this->getStoreConfig('mpanel/product_tabs/show_reviews'),
                    'show_product_tag_list' => $this->getStoreConfig('mpanel/product_tabs/show_product_tag_list')
                ],
            'contact_google_map'=>
                [
                    'display_google_map' => $this->getStoreConfig('mpanel/contact_google_map/display_google_map'),
                    'api_key' => $this->getStoreConfig('mpanel/contact_google_map/api_key'),
                    'address_google_map' => $this->getStoreConfig('mpanel/contact_google_map/address_google_map'),
                    'html_google_map' => $this->getStoreConfig('mpanel/contact_google_map/html_google_map'),
                    'pin_google_map' => $this->getStoreConfig('mpanel/contact_google_map/pin_google_map')
                ],
            'banner_slider'=>
                [
                    'slider_tyle' => $this->getStoreConfig('mgstheme/banner_slider/slider_tyle'),
                    'id_reslider' => $this->getStoreConfig('mgstheme/banner_slider/id_reslider'),
                    'identifier_block' => $this->getStoreConfig('mgstheme/banner_slider/identifier_block'),
                    'banner_owl_auto' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_auto'),
                    'banner_owl_speed' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_speed'),
                    'banner_owl_loop' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_loop'),
                    'banner_owl_nav' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_nav'),
                    'banner_owl_dot' => $this->getStoreConfig('mgstheme/banner_slider/banner_owl_dot')
                ]
        ];
    }

    /**
     * @return mixed
     */
    public function getCurrentCategory(){

        $id = $this->_request->getParam('id');
        $this->_currentCategory = $this->getModel('Magento\Catalog\Model\Category')->load($id);
        return $this->_currentCategory;

    }

    /**
     * @param $node
     * @param null $storeId
     * @return mixed
     */
    public function getStoreConfig($node, $storeId = NULL){
        if($storeId != NULL){
            return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
        return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    /**
     * @param $model
     * @return mixed
     */
    public function getModel($model){
        return $this->_objectManager->create($model);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore(){
        return $this->_storeManager->getStore();
    }
}
