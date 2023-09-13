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

class GalleryEffect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		return [
			['value' => 1, 'label' => __('Hover Zoom')], 
			['value' => 2, 'label' => __('Light Box')]
		];
    }
}
