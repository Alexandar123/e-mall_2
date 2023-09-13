<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Captcha image model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace MGS\ThemeSettings\Model\Config\Source;

class Elements implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
			['value' => 'store', 'label' => __('Store view switcher')], 
			['value' => 'curency', 'label' => __('Curency switcher')],
			['value' => 'account', 'label' => __('Account links')],
			['value' => 'wishlist', 'label' => __('Wishlist')],
			['value' => 'minicart', 'label' => __('Mini Cart')],
			['value' => 'search', 'label' => __('Search')]
		];
    }
}
