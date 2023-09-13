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

class FontWeightImport implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
			['value' => '100', 'label' => __('Thin 100')], 
			['value' => '100i', 'label' => __('Thin 100 Italic')], 
			['value' => '200', 'label' => __('Extra Light 200')], 
			['value' => '200i', 'label' => __('Extra Light 200 Italic')], 
			['value' => '300', 'label' => __('Light 300')],
			['value' => '300i', 'label' => __('Light 300 Italic')],
			['value' => '400', 'label' => __('Regular 400')],
			['value' => '400i', 'label' => __('Regular 400 Italic')],
			['value' => '500', 'label' => __('Medium 500')],
			['value' => '500i', 'label' => __('Medium 500 Italic')],
			['value' => '600', 'label' => __('Semi Bold 600')],
			['value' => '600i', 'label' => __('Semi Bold 600 Italic')],
			['value' => '700', 'label' => __('Bold 700')],
			['value' => '700i', 'label' => __('Bold 700 Italic')],
			['value' => '800', 'label' => __('Extra Bold 800')],
			['value' => '800i', 'label' => __('Extra Bold 800 Italic')],
			['value' => '900', 'label' => __('Black 900')],
			['value' => '900i', 'label' => __('Black 900 Italic')]
		];
    }
}
