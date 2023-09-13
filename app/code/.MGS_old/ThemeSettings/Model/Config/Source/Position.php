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

class Position implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'default', 'label' => __('Above Main Content')],
			['value' => 'top', 'label' => __('Above Product List')],
			['value' => 'bottom', 'label' => __('Below Product List')],
			['value' => 'page', 'label' => __('Below Main Content')],
		];
    }
}
