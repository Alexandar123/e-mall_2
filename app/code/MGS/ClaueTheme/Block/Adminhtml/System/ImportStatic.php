<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ClaueTheme\Block\Adminhtml\System;

use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Export CSV button for shipping table rates
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class ImportStatic extends \MGS\ThemeSettings\Block\Adminhtml\System\ImportStatic
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
		$html = '';
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/static_blocks');
		$activeUrl = $objectManager->get('Magento\Backend\Model\UrlInterface')->getUrl("adminhtml/system_config/edit/section/active_theme");
		if(is_dir($dir)) {
            if ($dhFile = opendir($dir)) {
				while ($fileStatic[] = readdir($dhFile));
				sort($fileStatic);
				if(count($fileStatic)>0){
					$numberFile = 0;
					foreach ($fileStatic as $file){
						$filePart = pathinfo($dir.'/'.$file);
						if(isset($filePart['extension']) && $filePart['extension']=='xml'){
							$numberFile++;
							$fileName = str_replace('.xml','',$file);

							$activeKey = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('active_theme/activate/'.$fileName);

							if($activeKey!=''){
								$url = $this->_backendUrl->getUrl("adminhtml/themesettings/importstatic", ['theme'=>$fileName]);
								$html .= '<button type="button" class="action-default scalable" onclick="setLocation(\''.$url.'\')" data-ui-id="widget-button-2" style="margin-bottom:10px"><span style="text-transform: capitalize;">'.__("Import %1's Static Blocks", $fileName).'</span></button><br/>';
							}else{
								$html .= '<button type="button" class="action-default scalable" data-ui-id="widget-button-2" style="margin-bottom:10px" disabled="disabled"><span style="text-transform: capitalize;">'.__("Import %1's Static Blocks", $fileName).'</span></button><br/>';

								$html .= '<span style="color:#ff0000; font-size:14px">Please <a href="'.$activeUrl.'">activate</a> for Claue theme.</span>';
							}


						}
					}
					if($numberFile == 0){
						$html .= '<span style="margin-top:5px; display:block">'.__('Have no static block to import').'</span>';
					}
				}else{
					$html .= '<span style="margin-top:5px; display:block">'.__('Have no static block to import').'</span>';
				}
			}
		}

        return $html;
    }
}
