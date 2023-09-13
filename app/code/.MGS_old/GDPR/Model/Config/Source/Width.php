<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\GDPR\Model\Config\Source;

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
			['value' => 'full-width', 'label' => __('Full width')], 
			['value' => 'custom', 'label' => __('Custom')]
		];
    }
}
