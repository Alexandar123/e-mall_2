<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\ThemeSettings\Model\Category\Attribute\Source;

class Perrow extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	
	public function getAllOptions()
    {
		$option = [
			['value' => '', 'label' => __('Use Config Setting')]
		];
		
		$perrowObj = new \MGS\ThemeSettings\Model\Config\Source\Perrow;
		$perrow = $perrowObj->toOptionArray();
		
		$option = array_merge($option, $perrow);
		
        return $option;
    }
}
