<?php
/**
 * Copyright � 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Simple product data view
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace MGS\ExtraGallery\Block\Product\View;

use Magento\Framework\Data\Collection;
use Magento\Framework\Json\EncoderInterface;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
		\MGS\ThemeSettings\Helper\Config $themeHelper,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $arrayUtils,  $jsonEncoder, $data);
		$this->themeHelper = $themeHelper;
    }

	protected function _prepareLayout()
    {
		$type = $this->themeHelper->getStoreConfig('extragallery/general/glr_type');
		$galleryRight = $this->themeHelper->getStoreConfig('extragallery/general/gallery_right');

		$product = $this->getProduct();
		if($product->getData('extragallery_glr_type')){
			$type = $product->getData('extragallery_glr_type');
		}
		if($galleryRight){
			$this->pageConfig->addBodyClass('gallery-float-right');
		}

		switch ($type) {
			case 1:
				$this->pageConfig->addBodyClass('extra-gallery-sticky');
				break;
			case 2:
				$this->pageConfig->addBodyClass('extra-gallery-grid');
				break;
			case 3:
				$this->pageConfig->addBodyClass('extra-gallery-fullwidth');
				break;
		}

        return parent::_prepareLayout();
    }
    /**
     * Retrieve collection of gallery images
     *
     * @return Collection
     */
    public function getGalleryImages()
    {
        $product = $this->getProduct();
        $images = $product->getMediaGalleryImages();
        $zoom_magnify = 1.5;
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
				if($this->isMainImage($image)){
					$image->setData('is_base_image', 1);
				}else{
					$image->setData('is_base_image', 0);
				}

                if($this->isDegreeImage($image) && $product->getData('mgs_j360')){
                    $image->setData('degree_image', 1);
                }else{
                    $image->setData('degree_image', 0);
                }

                if($this->isArImage($image)  && $product->getData('mgs_arimage')){
                    $image->setData('ar_image', 1);
                }else{
                    $image->setData('ar_image', 0);
                }

                /* @var \Magento\Framework\DataObject $image */
                $image->setData(
                    'small_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_medium')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_large')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'image_zoom',
                    $this->_imageHelper->init($product, 'product_page_image_large')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }

        return $images;
    }

    /**
     * Is 360 degree image
     *
     * @param \Magento\Framework\DataObject $image
     * @return bool
     */
    public function isDegreeImage($image)
    {
        $product = $this->getProduct();
        return $product->getThumbDegreeImage() == $image->getFile();
    }

    /**
     * Is 3d image
     *
     * @param \Magento\Framework\DataObject $image
     * @return bool
     */
    public function isArImage($image)
    {
        $product = $this->getProduct();
        return $product->getThumbArImage() == $image->getFile();
    }
}
