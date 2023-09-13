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

class PageLayout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => '', 'label' => __('No Layout Update')], 
			['value' => 'themesetting_onecolumn_custom', 'label' => __('1 column')], 
			['value' => 'themesetting_2columns_left_custom', 'label' => __('2 columns with left bar')], 
			['value' => 'themesetting_2columns_right_custom', 'label' => __('2 columns with right bar')], 
			['value' => 'themesetting_3columns_custom', 'label' => __('3 columns')]
		];
    }
}
