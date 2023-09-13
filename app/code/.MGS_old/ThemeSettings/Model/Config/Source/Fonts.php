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

class Fonts implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\ThemeSettings\Helper\Config');
		$fontArray = $helper->getFonts();

		$result = [];
		$result[] = [
			'value' => '',
			'label' => __('Choose font family'),
		];

		foreach($fontArray as $_font){
			$result[] = [
				'value' => $_font['css-name'],
				'label' => $_font['font-name'],
			];
		}

		
		return $result;
    }
}
