<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Controller\Adminhtml\Fbuilder;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Checkstore extends \MGS\Fbuilder\Controller\Adminhtml\Fbuilder
{
    
    protected $_filesystem;
    protected $_file;
    

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        parent::__construct($context);
        $this->_filesystem = $filesystem;
        $this->_file = $file;
    }

    /**
     * Edit sitemap
     *
     * @return                                  void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $result['result'] = 'error';
        if (($productId = $this->getRequest()->getParam('product_id')) && ($type = $this->getRequest()->getParam('type'))) {
            $dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/'.$type.'/'.$productId);
            $html = '';
            if (is_dir($dir)) {
                $files = [];
                if ($dh = opendir($dir)) {
                    while ($files[] = readdir($dh));
                    sort($files);
                    $filterFile = [];
                    foreach ($files as $file) {
                        $fileParts = pathinfo($dir.'/'.$file);
                        if (isset($fileParts['extension']) && $fileParts['extension']=='xml') {
                            $filterFile[] = $file;
                        }
                    }
                    if (count($filterFile)>0) {
                        if (count($filterFile)==1) {
                            $fileParts = pathinfo($dir.'/'.$filterFile[0]);
                            if (isset($fileParts['extension']) && $fileParts['extension']=='xml') {
                                $html .= '<input type="hidden" value="'.$fileParts['filename'].'" class="input-hidden store_id"/>';
                            }
                        } else {
                            $html .= '<select class="admin__control-select store_id" style="height:32px; vertical-align:middle; margin-right:10px"><option value="">From</option>';
                            foreach ($filterFile as $file) {
                                $fileParts = pathinfo($dir.'/'.$file);
                                if (isset($fileParts['extension']) && $fileParts['extension']=='xml') {
                                    if ($fileParts['filename']=='0') {
                                        $label = 'Default - ID #0';
                                    } else {
                                        $store = $this->_objectManager->create('Magento\Store\Model\Store')->load($fileParts['filename']);
                                        $label = $store->getName(). ' - ID #'.$fileParts['filename'];
                                    }
                                    $html .= '<option value="'.$fileParts['filename'].'">'.$label.'</option>';
                                }
                            }
                            $html .= '</select>';
                        }
                        
                        $result['result'] = 'success';
                        $result['data'] = $html;
                    }
                }
            }
            
        }
        
        return $this->getResponse()->setBody(json_encode($result));
    }
    
    public function isFile($filename)
    {
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR);

        return $mediaDirectory->isFile($filename);
    }
}
