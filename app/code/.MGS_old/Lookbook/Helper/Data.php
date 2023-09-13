<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Lookbook\Helper;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	public function getStoreConfig($node){
		return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getImageDimensions($img_path){
		list($width, $height) = getimagesize($img_path);
		$result = array('width'=>$width, 'height'=>$height);
        return $result;
    }
}