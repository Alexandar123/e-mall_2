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

class Htmllayout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'wide', 'label' => __('Wide')], 
			['value' => 'boxed', 'label' => __('Boxed')], 
			['value' => 'wide border', 'label' => __('Wide with border')], 
		];
    }
}
