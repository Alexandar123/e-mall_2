<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sitemap grid link column renderer
 *
 */
namespace MGS\Mmegamenu\Block\Adminhtml\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Prepare link to display in grid
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		$id = $row->getId();
		$arrParam = ['id'=>$id];
		if($row->getStore()!=''){
			$arrParam['store'] = $row->getStore();
		}
		$url=$this->getUrl('*/*/edit', $arrParam);
		return sprintf("<a href='%s'>%s</a>", $url, __('Edit'));
    }
}
