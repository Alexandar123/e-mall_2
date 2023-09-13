<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace MGS\Fbuilder\Model;

class Category extends \Magento\Catalog\Model\Category
{
   
    /**
     * Initialize resource model
     *
     * @return void
     */
	protected function _construct()
    {
        // If Flat Index enabled then use it but only on frontend
        if ($this->flatState->isAvailable() && !$this->getDisableFlat()) {
            $this->_init(\Magento\Catalog\Model\ResourceModel\Category\Flat::class);
            $this->_useFlatResource = true;
        } else {
            $this->_init(\Magento\Catalog\Model\ResourceModel\Category::class);
        }
    }
	
	public function getDisableFlat(){
		
		$urlString = str_replace($this->getUrlInstance()->getUrl(), '', $this->getUrlInstance()->getCurrentUrl());
		$arrUrl = explode('/',$urlString);
		if($arrUrl[0]=='fbuilder' && $arrUrl[1]=='index' && $arrUrl[2]=='publish'){
			return true;
		}
		return false;
	}
}
