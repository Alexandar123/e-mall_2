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

class Repeat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'no-repeat', 'label' => __('No Repeat')],
			['value' => 'repeat', 'label' => __('Repeat')], 
			['value' => 'repeat-x', 'label' => __('Repeat X')], 
			['value' => 'repeat-y', 'label' => __('Repeat Y')]
		];
    }
}
