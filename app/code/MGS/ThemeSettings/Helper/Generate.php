<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\ThemeSettings\Helper;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Contact base helper
 */
class Generate extends \MGS\Fbuilder\Helper\Generate
{
	public function generateCssWidth($pageId){
		return '.cms-index-index.cms-page'.$pageId.' #maincontent > .columns, .cms-page-view.cms-page'.$pageId.' #maincontent > .columns, .cms-index-index.cms-page'.$pageId.' main.page-main, .cms-page-view.cms-page'.$pageId.' main.page-main {max-width:100vw !important;padding-left: 0; padding-right: 0;}.cms-index-index.cms-page'.$pageId.' footer.page-footer {margin-top: 0;}';
	}
	
	public function generateCssCustomWidth($customWidth){
		$customWidthPadding = $customWidth + 30;
		return '.page.messages .messages { max-width: '. $customWidth .'px; } body.custom .page-main { max-width: '. $customWidth .'px;} @media(min-width: '. $customWidthPadding .'px){ body.custom .frame, body.custom .breadcrumbs .items, body.custom .page-main { max-width: '. $customWidth .'px;} }';
	}
}