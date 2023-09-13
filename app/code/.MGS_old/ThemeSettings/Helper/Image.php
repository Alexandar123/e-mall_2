<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ThemeSettings\Helper;

/**
 * Catalog image helper
 *
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Image extends \Magento\Catalog\Helper\Image
{
	/**
     * @var \Magento\Framework\Config\View
     */
    protected $_imageId;

	
    /**
     * Initialize Helper to work with Image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return $this
     */
    public function init($product, $imageId, $attributes = [])
    {
        $this->_reset();

        $this->attributes = array_merge(
            $this->getConfigView()->getMediaAttributes('Magento_Catalog', self::MEDIA_TYPE_CONFIG_NODE, $imageId),
            $attributes
        );
		
		$this->_imageId = $imageId;

        $this->setProduct($product);
        $this->setImageProperties();
        $this->setWatermarkProperties();

        return $this;
    }
	
	public function getStoreConfig($path){
		return $this->scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

    /**
     * Set image properties
     *
     * @return $this
     */
    protected function setImageProperties()
    {
		
		switch($this->_imageId){
			case 'product_page_main_image':
			case 'product_page_image_medium':
			case 'product_page_main_image_default':
			case 'product_page_image_medium_no_frame':
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
				if($product && ($product->getMgsImageDimention()!='')){
					$dimention = $product->getMgsImageDimention();
				}else{
					$dimention = $this->getStoreConfig("themesettings/product_image_dimention/detail_big");
				}
				
				$arrDimention = explode('x',$dimention);
				if(($dimention!='') && (count($arrDimention)>0)){
					$width = trim($arrDimention[0]);
					if(isset($arrDimention[1])){
						$height = trim($arrDimention[1]);
					}else{
						$height = $width;
					}
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($width);
					$this->_getModel()->setHeight($height);
				}else{
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($this->getWidth());
					$this->_getModel()->setHeight($this->getHeight());
				}
				break;
			case 'product_page_more_views':
			case 'product_page_image_small':
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
				if($product && ($product->getMgsImageDimentionMoreView()!='')){
					$dimention = $product->getMgsImageDimentionMoreView();
				}else{
					$dimention = $this->getStoreConfig("themesettings/product_image_dimention/detail_small");
				}
				$arrDimention = explode('x',$dimention);
				if(($dimention!='') && (count($arrDimention)>0)){
					$width = trim($arrDimention[0]);
					if(isset($arrDimention[1])){
						$height = trim($arrDimention[1]);
					}else{
						$height = $width;
					}
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($width);
					$this->_getModel()->setHeight($height);
				}else{
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($this->getWidth());
					$this->_getModel()->setHeight($this->getHeight());
				}
				break;
			case 'product_page_image_large':
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
				if($product && ($product->getMgsImageDimentionMoreView()!='')){
					$dimention = $product->getMgsImageDimentionMoreView();
				}else{
					$dimention = $this->getStoreConfig("themesettings/product_image_dimention/detail_big");
				}
				$arrDimention = explode('x',$dimention);
				if(($dimention!='') && (count($arrDimention)>0)){
					$width = trim($arrDimention[0]);
					if(isset($arrDimention[1])){
						$height = trim($arrDimention[1]);
					}else{
						$height = $width;
					}
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($width);
					$this->_getModel()->setHeight($height);
				}else{
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($this->getWidth());
					$this->_getModel()->setHeight($this->getHeight());
				}
				break;
			case 'category_page_grid':
			case 'product_base_image':
			case 'cart_cross_sell_products':
			case 'new_products_content_widget_grid':
			case 'recently_compared_products_grid_content_widget':
			case 'recently_viewed_products_grid_content_widget':
			case 'recently_compared_products_images_names_widget':
			case 'product_comparison_list':
			case 'wishlist_thumbnail':
			case 'category_page_grid_swatches':
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
				$request = $objectManager->get('Magento\Framework\App\RequestInterface');
				if($request->getParam('dimention')){
					$dimention = $request->getParam('dimention');
				}else{
					if($category && $category->getImageDimentionGrid()){
						$dimention = $category->getImageDimentionGrid();
					}else{
						$dimention = $this->getStoreConfig("themesettings/product_image_dimention/default_grid");
					}
				}
				$arrDimention = explode('x',$dimention);
				if(($dimention!='') && (count($arrDimention)>0)){
					$width = trim($arrDimention[0]);
					if(isset($arrDimention[1])){
						$height = trim($arrDimention[1]);
					}else{
						$height = $width;
					}
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($width);
					$this->_getModel()->setHeight($height);
				}else{
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($this->getWidth());
					$this->_getModel()->setHeight($this->getHeight());
				}
				
				break;
			case 'category_page_list':
			case 'recently_compared_products_list_content_widget':
			case 'recently_viewed_products_list_content_widget':
			case 'category_page_list_swatches':
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
				$request = $objectManager->get('Magento\Framework\App\RequestInterface');
				if($request->getParam('dimention')){
					$dimention = $request->getParam('dimention');
				}else{
					if($category && $category->getImageDimentionList()){
						$dimention = $category->getImageDimentionList();
					}else{
						$dimention = $this->getStoreConfig("themesettings/product_image_dimention/default_grid");
					}
				}
				
				$dimention = $this->scopeConfig->getValue("themesettings/product_image_dimention/default_list",\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				$arrDimention = explode('x',$dimention);
				if(($dimention!='') && (count($arrDimention)>0)){
					$width = trim($arrDimention[0]);
					if(isset($arrDimention[1])){
						$height = trim($arrDimention[1]);
					}else{
						$height = $width;
					}
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($width);
					$this->_getModel()->setHeight($height);
				}else{
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($this->getWidth());
					$this->_getModel()->setHeight($this->getHeight());
				}
				break;
			case 'mini_cart_product_thumbnail':
				$dimention = $this->getStoreConfig("themesettings/product_image_dimention/mini_cart");
				$arrDimention = explode('x',$dimention);
				if(($dimention!='') && (count($arrDimention)>0)){
					$width = trim($arrDimention[0]);
					if(isset($arrDimention[1])){
						$height = trim($arrDimention[1]);
					}else{
						$height = $width;
					}
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($width);
					$this->_getModel()->setHeight($height);
				}else{
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($this->getWidth());
					$this->_getModel()->setHeight($this->getHeight());
				}
				break;
			case 'cart_page_product_thumbnail':
				$dimention = $this->getStoreConfig("themesettings/product_image_dimention/shopping_cart");
				$arrDimention = explode('x',$dimention);
				if(($dimention!='') && (count($arrDimention)>0)){
					$width = trim($arrDimention[0]);
					if(isset($arrDimention[1])){
						$height = trim($arrDimention[1]);
					}else{
						$height = $width;
					}
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($width);
					$this->_getModel()->setHeight($height);
				}else{
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($this->getWidth());
					$this->_getModel()->setHeight($this->getHeight());
				}
				break;
			case 'product_small_list':
				$dimention = $this->getStoreConfig("themesettings/product_image_dimention/small_list");
				$arrDimention = explode('x',$dimention);
				if(($dimention!='') && (count($arrDimention)>0)){
					$width = trim($arrDimention[0]);
					if(isset($arrDimention[1])){
						$height = trim($arrDimention[1]);
					}else{
						$height = $width;
					}
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($width);
					$this->_getModel()->setHeight($height);
				}else{
					$this->_getModel()->setDestinationSubdir($this->getType());
					$this->_getModel()->setWidth($this->getWidth());
					$this->_getModel()->setHeight($this->getHeight());
				}
				break;
			case 'category_page_grid-1':
				$this->_getModel()->setDestinationSubdir($this->getType());
				break;
			default:
				$this->_getModel()->setDestinationSubdir($this->getType());
				$this->_getModel()->setWidth($this->getWidth());
				$this->_getModel()->setHeight($this->getHeight());
				break;
		}
		
        // Set 'keep frame' flag
        $frame = $this->getFrame();
        if (!empty($frame)) {
            $this->_getModel()->setKeepFrame(true);
        }

        // Set 'constrain only' flag
        $constrain = $this->getAttribute('constrain');
        if (!empty($constrain)) {
            $this->_getModel()->setConstrainOnly($constrain);
        }

        // Set 'keep aspect ratio' flag
        $aspectRatio = $this->getAttribute('aspect_ratio');
        if (!empty($aspectRatio)) {
            $this->_getModel()->setKeepAspectRatio($aspectRatio);
        }

        // Set 'transparency' flag
        $transparency = $this->getAttribute('transparency');
        if (!empty($transparency)) {
            $this->_getModel()->setKeepTransparency($transparency);
        }

        // Set background color
        $background = $this->getAttribute('background');
        if (!empty($background)) {
            $this->_getModel()->setBackgroundColor($background);
        }

        return $this;
    }
	
	/**
     *
     * Calculate image ratio
     * @return float|int
     */
    public function getRatio()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
		$request = $objectManager->get('Magento\Framework\App\RequestInterface');
		if($request->getParam('dimention')){
			$dimention = $request->getParam('dimention');
		}else{
			if($category && $category->getImageDimentionGrid()){
				$dimention = $category->getImageDimentionGrid();
			}else{
				$dimention = $this->getStoreConfig("themesettings/product_image_dimention/default_grid");
			}
		}
		$arrDimention = explode('x',$dimention);
		if(($dimention!='') && (count($arrDimention)>0)){
			$width = trim($arrDimention[0]);
			if(isset($arrDimention[1])){
				$height = trim($arrDimention[1]);
			}else{
				$height = $width;
			}
			
			if ($width && $height) {
				return $height / $width;
			}
		}else{
			if ($this->getWidth() && $this->getHeight()) {
				return $this->getHeight() / $this->getWidth();
			} else {
				return 1;
			}
		}
        return 1;
    }

}
