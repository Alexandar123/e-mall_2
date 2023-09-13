<?php

namespace MGS\Fbuilder\Controller\Adminhtml\Fbuilder;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;

class Delete extends \MGS\Fbuilder\Controller\Adminhtml\Fbuilder
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    protected $ioFile;

    public function __construct(
        Action\Context $context,
        Filesystem $fileSystem,
        File $ioFile
    ) {
        $this->fileSystem = $fileSystem;
        $this->ioFile = $ioFile;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            if ($this->getRequest()->getParam('version_id')) {
                $fileName_s = $this->getRequest()->getParam('version_id');
                $fileName = explode(',', $fileName_s);
                foreach ($fileName as $item) {
                    if ($this->isFile('mgs/fbuilder/restore/'.$item)) {
                        $dir = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/restore/');
                        $deleteFile = $dir . $item;
                        $this->deleteFile($deleteFile);
                    }
                }
                try {
                    $amount = count($fileName);
                    if ($amount == 1) {
                        $this->messageManager->addSuccess(__('Successfully deleted the file.'));
                    }
                    if ($amount > 1) {
                        $this->messageManager->addSuccess(__('Successfully deleted ') . $amount . __(' files'));
                    }

                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while deleting the file.'));
                }
            }
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }

    public function isFile($filename)
    {
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);

        return $mediaDirectory->isFile($filename);
    }

    public function deleteFile($fileName)
    {
        $folderPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/mgs/fbuilder/restore/');

        $io = $this->ioFile;
        $io->setAllowCreateFolders(true);
        $io->open(['path' => $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($folderPath)]);
        try {
            $io->rm($fileName);
        } catch (\Exception $e) {
        }
    }
}
