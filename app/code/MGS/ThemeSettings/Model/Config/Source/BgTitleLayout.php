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

class BgTitleLayout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => 1, 'label' => __('Breadcrumbs display inside the page title')], 
			['value' => 2, 'label' => __('Breadcrumbs display outside the page title')]
		];
    }
}
