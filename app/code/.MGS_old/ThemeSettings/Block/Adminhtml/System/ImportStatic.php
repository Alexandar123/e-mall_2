<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ThemeSettings\Block\Adminhtml\System;

use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Export CSV button for shipping table rates
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class ImportStatic extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;
	
	protected $collectionFactory;
	
	protected $_request;
	
    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Filesystem $filesystem
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\Filesystem $filesystem,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_backendUrl = $backendUrl;
		$this->_request = $request;
		$this->_filesystem = $filesystem;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
		$html = '';
		
		$dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/static_blocks');
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
							$url = $this->_backendUrl->getUrl("adminhtml/themesettings/importstatic", ['theme'=>$fileName]);
							$html .= '<button type="button" class="action-default scalable" onclick="setLocation(\''.$url.'\')" data-ui-id="widget-button-2" style="margin-bottom:10px"><span style="text-transform: capitalize;">'.__("Import %1's Static Blocks", $fileName).'</span></button><br/>';
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
