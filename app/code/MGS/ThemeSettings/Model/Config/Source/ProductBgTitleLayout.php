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

class ProductBgTitleLayout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        // Get all options in class BgTitleLayout.
        $BgTitleLayout = new BgTitleLayout();
        $options = $BgTitleLayout->toOptionArray();

        if (empty($options)) {
            $options = [];
        }

        // Add default option.
        array_unshift($options, ['value' => 0, 'label' => __('Default')]);

        return $options;
    }
}
