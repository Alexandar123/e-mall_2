<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\ThemeSettings\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Rotate extends \Magento\Framework\App\Action\Action
{
	protected $_filesystem;
	
	protected $_storeManager;
	
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
		$this->_filesystem = $filesystem;
		$this->_storeManager = $storeManager;
    }
	
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		$productId = $this->getRequest()->getParam('product');
		$images = $this->getRotateImages($productId);
		
		$html = '<div class="rotate-image-container" style="position:relative;"><span class="close-rotate" onclick="closeRotateContainer()"><span class="pe-7s-close"></span></span><div class="rotate-j360" id="mgs_j360" style="overflow:hidden;">';
		if(count($images)>0){
			foreach($images as $image){
				$html .= '<img src="'.$image.'"/>';
			}
		}
		$html .= '</div></div>';
		$result['html'] = $html;
		
		return $this->getResponse()->setBody(json_encode($result));
    }
	
	public function getRotateImages($productId){
		$dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('wysiwyg/360/'.$productId);
		
		$result = [];
		$files = [];
		if(is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ($files[] = readdir($dh));
				sort($files);
				foreach ($files as $file){
					$file_parts = pathinfo($dir . $file);
					if (isset($file_parts['extension']) && (($file_parts['extension'] == 'jpg') || ($file_parts['extension'] == 'png'))) {
                        $result[] = $this->getMediaUrl().'wysiwyg/360/'.$productId.'/'.$file;
                    }
				}
                closedir($dh);
            }
        }
		return $result;
	}
	
	public function getMediaUrl(){
		return $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
	}
}
