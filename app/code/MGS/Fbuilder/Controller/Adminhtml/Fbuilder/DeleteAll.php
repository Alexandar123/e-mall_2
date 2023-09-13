<?php

namespace MGS\Fbuilder\Controller\Adminhtml\Fbuilder;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;

class DeleteAll extends \MGS\Fbuilder\Controller\Adminhtml\Fbuilder
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
            $this->deleteAllFile();
            try {
                $this->messageManager->addSuccess(__('Successfully deleted all the files.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while deleting the files.'));
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

    public function deleteAllFile()
    {
        $folderPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/mgs/fbuilder/restore/');

        $io = $this->ioFile;
        $io->setAllowCreateFolders(true);
        $io->open(['path' => $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($folderPath)]);
        try {
            foreach ($io->ls() as $file) {
                $fileName = 'mgs/fbuilder/restore/'.$file['text'];
                $fullName = $folderPath . $file['text'];
                if ($this->isFile($fileName)) {
                    $io->rm($fullName);
                }
            }
        } catch (\Exception $e) {
        }
    }
}
