<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Adminhtml\System;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Install extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    protected $_checkStoreView = false;
    protected $_checkWebsite = false;
    protected $_filesystem;
    protected $_dir;
    
    
    /**
     * @param \Magento\Backend\Block\Context      $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js   $jsHelper
     * @param array                               $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Filesystem $filesystem,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->_filesystem = $filesystem;
        $this->_dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Mpanel/data/themes');
    }
    
    public function getWebsiteId()
    {
        $storeModel = $this->_objectManager->create('Magento\Store\Model\Store');
        $store = $storeModel->load($this->getRequest()->getParam('store'));
        return $store->getWebsiteId();
    }
    
    protected function _getHeaderCommentHtml($element)
    {
        $html = '<table class="form-list" cellspacing="0"><tbody>';

        if (is_dir($this->_dir)) {
            if ($dh = opendir($this->_dir)) {
                
                $dirs = scandir($this->_dir);
                
                foreach ($dirs as $theme) {
                    if (($theme !='') && ($theme!='.') && ($theme!='..')) {
                        $themeName = $this->convertString($theme);
                        
                        if ($storeId = $this->getRequest()->getParam('store')) {
                            $url = $this->getUrl('adminhtml/mpanel/install', ['store'=>$storeId, 'theme'=>$theme]);
                        } elseif ($websiteId = $this->getRequest()->getParam('website')) {
                            $url = $this->getUrl('adminhtml/mpanel/install', ['website'=>$websiteId, 'theme'=>$theme]);
                        } else {
                            $url = $this->getUrl('adminhtml/mpanel/install', ['theme'=>$theme]);
                        }
                        
                        
                        $html .= '<tr><td style="padding:0 30px 10px"><button data-ui-id="widget-button-0" onclick="setLocation(\''.$url.'\')" class="action-default scalable" type="button" title="'.__('Install %1 theme', $themeName).'"><span>'.__('Install %1 theme', $themeName).'</span></button></td></tr>';
                    }
                }

                closedir($dh);
            }
        }

        
        $html .= '</tbody></table>';
        return $html;
    }
    
    public function convertString($theme)
    {
        $themeName = str_replace('_', ' ', $theme);
        return ucwords($themeName);
    }
}
