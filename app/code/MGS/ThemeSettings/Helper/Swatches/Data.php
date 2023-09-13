<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\ThemeSettings\Helper\Swatches;

/**
 * Class Helper Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Swatches\Helper\Data
{
    /**
     * Method getting full media gallery for current Product
     * Array structure: [
     *  ['image'] => 'http://url/pub/media/catalog/product/2/0/blabla.jpg',
     *  ['mediaGallery'] => [
     *      galleryImageId1 => simpleProductImage1.jpg,
     *      galleryImageId2 => simpleProductImage2.jpg,
     *      ...,
     *      ]
     * ]
     * @param ModelProduct $product
     * @return array
     */
    // public function getProductMediaGallery(\Magento\Catalog\Model\Product $product):array
    // {
    //     $baseImage = null;
    //     $gallery = [];

    //     $mediaGallery = $product->getMediaGalleryEntries();
	// 	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	// 	$request = $objectManager->get('Magento\Framework\App\RequestInterface');
		
    //     foreach ($mediaGallery as $mediaEntry) {
    //         if ($mediaEntry->isDisabled()) {
    //             continue;
    //         }

    //         if (in_array('image', $mediaEntry->getTypes(), true)) {
    //             $baseImage = $mediaEntry->getFile();
    //         } elseif (!$baseImage) {
    //             $baseImage = $mediaEntry->getFile();
    //         }

	// 		if($viewmode = $request->getParam('view_mode')){
	// 			$gallery[$mediaEntry->getId()] = $this->getAllSizeImagesCustom($product, $mediaEntry->getFile(), $viewmode);
	// 		}else{
	// 			$gallery[$mediaEntry->getId()] = $this->getAllSizeImages($product, $mediaEntry->getFile());
	// 		}
            
    //     }

    //     if (!$baseImage) {
    //         return [];
    //     }
		
	// 	if($viewmode = $request->getParam('view_mode')){
	// 		$resultGallery = $this->getAllSizeImagesCustom($product, $baseImage, $viewmode);
	// 	}else{
	// 		$resultGallery = $this->getAllSizeImages($product, $baseImage);
	// 	}

        
    //     $resultGallery['gallery'] = $gallery;

    //     return $resultGallery;
    // }
	
    /**
     * @param ModelProduct $product
     * @param string $imageFile
     * @return array
     */
    // public function getAllSizeImages(\Magento\Catalog\Model\Product $product, $imageFile)
    // {
    //     return [
    //         'large' => $this->imageHelper->init($product, 'product_page_image_large_no_frame')
    //             ->setImageFile($imageFile)
    //             ->getUrl(),
    //         'medium' => $this->imageHelper->init($product, 'product_page_image_medium_no_frame')
    //             ->setImageFile($imageFile)
    //             ->getUrl(),
    //         'small' => $this->imageHelper->init($product, 'product_page_image_small')
    //             ->setImageFile($imageFile)
    //             ->getUrl(),
    //     ];
    // }
	
	/**
     * @param ModelProduct $product
     * @param string $imageFile
     * @return array
     */
    // public function getAllSizeImagesCustom(\Magento\Catalog\Model\Product $product, $imageFile, $viewmode)
    // {
	// 	switch($viewmode){
	// 		case 'grid':
	// 			return [
	// 				'large' => $this->imageHelper->init($product, 'category_page_grid_swatches')
	// 					->setImageFile($imageFile)
	// 					->getUrl(),
	// 				'medium' => $this->imageHelper->init($product, 'category_page_grid_swatches')
	// 					->setImageFile($imageFile)
	// 					->getUrl(),
	// 				'small' => $this->imageHelper->init($product, 'product_page_image_small')
	// 					->setImageFile($imageFile)
	// 					->getUrl(),
	// 			];
	// 			break;
	// 		case 'list':
	// 			return [
	// 				'large' => $this->imageHelper->init($product, 'category_page_list_swatches')
	// 					->setImageFile($imageFile)
	// 					->getUrl(),
	// 				'medium' => $this->imageHelper->init($product, 'category_page_list_swatches')
	// 					->setImageFile($imageFile)
	// 					->getUrl(),
	// 				'small' => $this->imageHelper->init($product, 'product_page_image_small')
	// 					->setImageFile($imageFile)
	// 					->getUrl(),
	// 			];
	// 			break;
	// 		default:
	// 			return [
	// 				'large' => $this->imageHelper->init($product, 'product_page_image_large_no_frame')
	// 					->setImageFile($imageFile)
	// 					->getUrl(),
	// 				'medium' => $this->imageHelper->init($product, 'product_page_image_medium_no_frame')
	// 					->setImageFile($imageFile)
	// 					->getUrl(),
	// 				'small' => $this->imageHelper->init($product, 'product_page_image_small')
	// 					->setImageFile($imageFile)
	// 					->getUrl(),
	// 			];
	// 			break;
    //     }
    // }
}
