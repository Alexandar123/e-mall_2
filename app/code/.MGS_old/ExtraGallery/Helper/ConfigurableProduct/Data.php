<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\ExtraGallery\Helper\ConfigurableProduct;

use Magento\Catalog\Model\Product;

/**
 * Contact base helper
 */
class Data extends \Magento\ConfigurableProduct\Helper\Data
{
    /**
     * Catalog Image Helper
     *
     * @var Image
     */
    protected $imageHelper;

	public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->imageHelper = $imageHelper;
		$this->_scopeConfig = $scopeConfig;
    }
    
    
    /**
     * Retrieve collection of gallery images
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Model\Product\Image[]|null
     */
    public function getGalleryImages(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
		$largeSize = $this->getImageSizeForDetails();
		$mediumSize = $this->getImageSize();
		$minSize = $this->getImageMinSize();
        $zoom_magnify = $this->getStoreConfig('extragallery/general/zoom_magnify');
        $zoom_magnify = $zoom_magnify ? $zoom_magnify : 1.5;
        
        
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                /** @var $image \Magento\Catalog\Model\Product\Image */
                $image->setData(
                    'small_image_url',
                    $this->imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'zoom_image_url',
                    $this->imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }

        return $images;
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $images = $this->getGalleryImages($product);
            if ($images) {
                foreach ($images as $image) {
                    
                    $vd_url = $image->getData('video_url') ? $image->getData('video_url') : "";
                    
                    $options['images'][$productId][] =
                        [
                            'thumb' => $image->getData('small_image_url'),
                            'img' => $image->getData('medium_image_url'),
                            'full' => $image->getData('large_image_url'),
                            'zoom' => $image->getData('zoom_image_url'),
                            'caption' => $image->getLabel(),
                            'position' => $image->getPosition(),
                            'media_type' => str_replace('external-', '', $image->getData('media_type')),
                            'videoUrl' => $vd_url,
                            'isMain' => $image->getFile() == $product->getImage(),
                        ];
                }
            }
            foreach ($this->getAllowAttributes($currentProduct) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }
        return $options;
    }
	
	public function getStoreConfig($node){
		return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/* Get product image size */
	public function getImageSize(){
		$ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
		$maxWidth = $this->getStoreConfig('mpanel/catalog/max_width_image');
		$result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth));
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth*2));
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 1.5)));
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 4) / 3));
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth/2));
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*2) / 3));
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*3) / 4));
                break;
        }

        return $result;
	}
	
	public function getImageSizeForDetails() {
		$ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
		$maxWidth = $this->getStoreConfig('mpanel/catalog/max_width_image_detail');
        $result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth));
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth*2));
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 1.5)));
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth * 4) / 3));
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => round($maxWidth), 'height' => round($maxWidth/2));
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*2) / 3));
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => round($maxWidth), 'height' => round(($maxWidth*3) / 4));
                break;
        }

        return $result;
    }
	
	public function getImageMinSize() {
        $ratio = $this->getStoreConfig('mpanel/catalog/picture_ratio');
        $result = [];
        switch ($ratio) {
            // 1/1 Square
            case 1:
                $result = array('width' => 120, 'height' => 120);
                break;
            // 1/2 Portrait
            case 2:
                $result = array('width' => 120, 'height' => 240);
                break;
            // 2/3 Portrait
            case 3:
                $result = array('width' => 120, 'height' => 180);
                break;
            // 3/4 Portrait
            case 4:
                $result = array('width' => 120, 'height' => 160);
                break;
            // 2/1 Landscape
            case 5:
                $result = array('width' => 120, 'height' => 60);
                break;
            // 3/2 Landscape
            case 6:
                $result = array('width' => 120, 'height' => 80);
                break;
            // 4/3 Landscape
            case 7:
                $result = array('width' => 120, 'height' => 90);
                break;
        }

        return $result;
    }
	
	
}