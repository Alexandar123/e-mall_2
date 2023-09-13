<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\ThemeSettings\Model\Product\Attribute\Source;

class Template extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	
	
	public function getAllOptions()
    {
		$option = [
			['value' => '', 'label' => __('Use Config Setting')]
		];
		
		$templateObj = new \MGS\ThemeSettings\Model\Config\Source\Template;
		$template = $templateObj->toOptionArray();
		
		$option = array_merge($option, $template);
		
        return $option;
    }
}
