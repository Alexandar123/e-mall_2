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

class More implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 'not-show', 'label' => __('Not Show')], 
			['value' => 'link', 'label' => __('Show as link')], 
			['value' => 'popup', 'label' => __('Show as popup')]
		];
    }
}
