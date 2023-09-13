<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\Brand\Model\Config\Source;

class Perrow implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 2, 'label' => __('2')], 
			['value' => 3, 'label' => __('3')], 
			['value' => 4, 'label' => __('4')], 
			['value' => 5, 'label' => __('5')], 
			['value' => 6, 'label' => __('6')], 
			['value' => 7, 'label' => __('7')],
			['value' => 8, 'label' => __('8')]
		];
    }
}
