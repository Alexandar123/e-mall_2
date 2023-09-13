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

class Code extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
		if($id>1){
			$type = 'MGS\Mmegamenu\Block\Horizontal';
			$template = 'horizontal.phtml';
			if($row->getMenuType()==2){
				$type = 'MGS\Mmegamenu\Block\Vertical';
				$template = 'vertical.phtml';
			}
			
			$html = '{{block class="'.$type.'" menu_id="'.$id.'" template="MGS_Mmegamenu::'.$template.'"}}';
			return $html;
		}
		else{
			return '';
		}
    }
}
