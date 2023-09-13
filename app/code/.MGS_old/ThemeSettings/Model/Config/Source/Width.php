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

class Width implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'width1024', 'label' => __('1024px')], 
			['value' => 'width1200', 'label' => __('1200px')], 
			['value' => 'width1366', 'label' => __('1366px')], 
			['value' => 'fullwidth', 'label' => __('Full width')],
			['value' => 'custom', 'label' => __('Custom')],
		];
    }
}
