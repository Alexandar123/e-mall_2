<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\ExtraGallery\Model\Product\Attribute\Source;

class GalleryType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	
	
	public function getAllOptions()
    {
		$option = [
			['value' => '', 'label' => __('Use Default Setting')],
			['value' => 1, 'label' => __('Gallery List')], 
			['value' => 2, 'label' => __('Gallery Grid')], 
			['value' => 3, 'label' => __('Top Site Slide')], 
			['value' => 4, 'label' => __('Vertical Thumbnail')], 
			['value' => 5, 'label' => __('Horizontal Thumbnail')], 
			['value' => 6, 'label' => __('No Thumbnail')],  
		];
		
        return $option;
    }
}
