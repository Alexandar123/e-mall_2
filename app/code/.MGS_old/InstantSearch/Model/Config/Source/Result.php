<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\InstantSearch\Model\Config\Source;

class Result implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 0, 'label' => __('MGS Instant Search Results')], 
			['value' => 1, 'label' => __('Magento Catalog Search Results')], 
		];
    }
}
