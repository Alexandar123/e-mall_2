<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sitemap grid link column renderer
 *
 */
namespace MGS\Lookbook\Block\Adminhtml\Grid\Renderer;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Prepare link to display in grid
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		return '<img src="'.$this->getImageUrl($row).'" style="max-width:300px; max-height:150px">';
    }
	
	public function getImageUrl($lookbook){
		$bannerUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).$lookbook->getImage();
		return $bannerUrl;
	}
}
