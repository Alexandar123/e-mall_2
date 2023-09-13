<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Controller\Adminhtml\Portfolio;

use Magento\Framework\App\Filesystem\DirectoryList;

class Deletefile extends \MGS\Portfolio\Controller\Adminhtml\Portfolio
{

	protected $_storeManager;

	protected $_filesystem;

	protected $_file;

	/**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\Magento\Framework\Filesystem\Driver\File $file,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	)
	{
		parent::__construct($context);

		$this->_storeManager = $storeManager;
		$this->_filesystem = $filesystem;
		$this->_file = $file;
        $this->_fileUploaderFactory = $fileUploaderFactory;
	}

    public function execute()
    {
		if($this->getRequest()->isAjax()){
			$result = ['result'=>'error', 'data'=>__('Can not delete file.')];
			$fileName = $this->getRequest()->getParam('filedelete');
			$filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/portfolio/image') . $fileName;

			if ($this->_file->isExists($filePath)) {
				$this->_file->deleteFile($filePath);
				$result['data'] = 'Deleted file';
			}else {
				$result['data'] = 'Can not find file';
			}
			$result['result'] = 'success';
			echo json_encode($result);
		}else{
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
			return $resultRedirect;
		}
    }
}
