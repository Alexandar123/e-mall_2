<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 */
namespace MGS\Fbuilder\Model\Config\Source;

class ImageResolution implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'low_resolution', 'label' => __('Low Resolution')],
            ['value' => 'thumbnail', 'label' => __('Thumbnail')],
            ['value' => 'standard_resolution', 'label' => __('Standard Resolution')]
        ];
    }
}
