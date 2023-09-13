<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Fbuilder\Controller\Post;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Cache\Manager as CacheManager;

class Staticblock extends \Magento\Framework\App\Action\Action
{
    protected $_storeManager;
    
    protected $_filesystem;
    
    protected $_file;
    
    protected $cacheManager;
    
    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        CacheManager $cacheManager
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
        
        $this->_storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->_file = $file;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->cacheManager = $cacheManager;
    }
    
    public function execute()
    {
        if ($this->customerSession->getUseFrontendBuilder() == 1) {
            $dataPost = $this->getRequest()->getPostValue();
            $this->removePanelImages('panel', $dataPost);
            if (isset($dataPost['block_id']) && isset($dataPost['content']) && ($dataPost['content']!='')) {
                $model = $this->_objectManager->create('Magento\Cms\Model\Block')->load($dataPost['block_id']);
                $data = $model->getData();
                $data['content'] = $dataPost['content'];
                $model->setData($data);
            
                try {
                    // save the data
                    $model->save();
                    $this->cacheManager->clean(['full_page','block_html']);
                    // display success message
                    return $this->getMessageHtml('success', __('You saved the Block. Please wait to reload page.'), true);
                    
                } catch (\Exception $e) {
                    return $this->getMessageHtml('danger', $e->getMessage(), false);
                }
            } else {
                if (isset($dataPost['block_id'])) {
                    $message = __('Please add content.');
                } else {
                    $message = __('Can not find block id.');
                }
                $this->messageManager->addError($message);
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
            
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
    
    public function removePanelImages($type, $data)
    {
        if (isset($data['remove']) && (count($data['remove'])>0)) {
            foreach ($data['remove'] as $filename) {
                $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('wysiwyg/'.$type.'/') . $filename;
                if ($this->_file->isExists($filePath)) {
                    $this->_file->deleteFile($filePath);
                }
            }
        }
    }
    
    public function getMessageHtml($type, $message, $reload)
    {
        $html = '<style type="text/css">
			.container {
				padding: 0px 15px;
				margin-top:60px;
			}
			.page.messages .message {
				padding: 15px;
				font-family: "Lato",arial,tahoma;
				font-size: 14px;
			}
			.page.messages .message-success {
				background-color: #dff0d8;
			}
			.page.messages .message-danger {
				background-color: #f2dede;
			}
		</style>';
        $html .= '<main class="page-main container">
			<div class="page messages"><div data-placeholder="messages"></div><div>
				<div class="messages">
					<div class="message-'.$type.' '.$type.' message" data-ui-id="message-'.$type.'">
						<div>'.$message.'</div>
					</div>
				</div>
			</div>
		</div></main>';
        
        if ($reload) {
            $html .= '<script type="text/javascript">window.parent.location.reload();</script>';
        }
        
        return $this->getResponse()->setBody($html);
    }
}
