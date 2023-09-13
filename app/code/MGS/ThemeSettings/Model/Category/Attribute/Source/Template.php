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

class Template extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	
	
	public function getAllOptions()
    {
		$option = [
			['value' => 'grid', 'label' => __('Grid')],
			['value' => 'masonry', 'label' => __('Masonry')], 
			['value' => 'parallax', 'label' => __('Parallax')]
		];
        
        return $option;
    }
}
