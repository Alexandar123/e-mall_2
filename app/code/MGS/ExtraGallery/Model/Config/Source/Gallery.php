<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\ExtraGallery\Model\Config\Source;

class Gallery implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		return [
			['value' => 1, 'label' => __('Gallery List')], 
			['value' => 2, 'label' => __('Gallery Grid')], 
			['value' => 3, 'label' => __('Top Site Slide')], 
			['value' => 4, 'label' => __('Vertical Thumbnail')], 
			['value' => 5, 'label' => __('Horizontal Thumbnail')], 
			['value' => 6, 'label' => __('No Thumbnail')],
		];
    }
}
