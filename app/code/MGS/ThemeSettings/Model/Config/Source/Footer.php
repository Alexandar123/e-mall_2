<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\ThemeSettings\Model\Config\Source;

class Footer implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'footer1', 'label' => __('Footer 1')], 
			['value' => 'footer2', 'label' => __('Footer 2')], 
			['value' => 'footer3', 'label' => __('Footer 3')],
			['value' => 'footer4', 'label' => __('Footer 4')],
			['value' => 'footer5', 'label' => __('Footer 5')],
			['value' => 'footer6', 'label' => __('Footer 6')]
		];
    }
}
