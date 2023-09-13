<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Fbuilder\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Cache\Manager as CacheManager;
use MGS\Fbuilder\Model\ResourceModel\Section\CollectionFactory as SectionFactory;
use MGS\Fbuilder\Model\ResourceModel\Child\CollectionFactory as BlockFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Cms\Model\PageFactory;

class Confirm extends \Magento\Framework\App\Action\Action
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    protected $_sectionFactory;

    protected $_childsFactory;

    protected $_confirmCollectionFactory;
    
    /**
     * @var \MGS\Fbuilder\Helper\Generate
     */
    protected $_generateHelper;
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var SectionFactory
     */
    protected $sectionFactory;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var File
     */
    protected $ioFile;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Element\Context $urlContext,
        CustomerSession $customerSession,
        \MGS\Fbuilder\Helper\Generate $generateHelper,
        CacheManager $cacheManager,
        SectionFactory $sectionFactory,
        BlockFactory $blockFactory,
        DateTime $dateTime,
        Filesystem $fileSystem,
        File $ioFile,
        FileFactory $fileFactory,
        PageFactory $pageFactory
    ) {
        $this->_urlBuilder = $urlContext->getUrlBuilder();
        $this->customerSession = $customerSession;
        $this->cacheManager = $cacheManager;
        $this->sectionFactory = $sectionFactory;
        $this->blockFactory = $blockFactory;
        $this->dateTime = $dateTime;
        $this->fileSystem = $fileSystem;
        $this->ioFile = $ioFile;
        $this->fileFactory = $fileFactory;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
        
        $this->_generateHelper = $generateHelper;
    }
    
    public function urlDecode($url)
    {
        $url = base64_decode(strtr($url, '-_,', '+/='));
        return $this->_urlBuilder->sessionUrlVar($url);
    }
    
    public function execute()
    {
        if (($this->customerSession->getUseFrontendBuilder() == 1) && ($referer = $this->getRequest()->getParam('referrer')) && ($pageId = $this->getRequest()->getParam('page_id')) && ($storeId = $this->getRequest()->getParam('store_id')) && ($page = $this->getRequest()->getParam('page'))) {
            $this->_generateHelper->importContent($pageId);
            $this->cacheManager->clean(['full_page']);
            $url = $this->urlDecode($referer);

            $sectionCollection = $this->sectionFactory->create();
            $sectionCollection->addFieldToFilter('page_id', $pageId);
            $content = '';

            if ($sectionCollection->getSize()) {
                $content = "<page>\n";
                foreach ($sectionCollection as $section) {
                    $content .= "\t<section>\n";
                    $sectionData = $section->getData();
                    unset($sectionData['block_id'], $sectionData['store_id'], $sectionData['page_id']);
                    foreach ($sectionData as $sectionColumn => $value) {
                        $content .= "\t\t<".$sectionColumn."><![CDATA[".$value."]]></".$sectionColumn.">\n";
                    }
                    $content .= "\t</section>\n";
                }

                $blockCollection = $this->blockFactory->create();
                $blockCollection->addFieldToFilter('page_id', $pageId);
                if (count($blockCollection)>0) {
                    foreach ($blockCollection as $block) {
                        $content .= "\t<block>\n";
                        $blockData = $block->getData();
                        unset($blockData['home_name'], $blockData['static_block_id'], $blockData['store_id'], $blockData['page_id']);
                        foreach ($blockData as $blockColumn => $blockValue) {
                            $content .= "\t\t<".$blockColumn."><![CDATA[".$blockValue."]]></".$blockColumn.">\n";
                        }
                        $content .= "\t</block>\n";
                    }
                }

                $content .= "</page>";
            }

            try {
                if ($content!='') {
                    $currentDate = $this->dateTime->gmtDate('Y-m-d_H:i:s');
                    $pageIdentifier = $this->getPageById($pageId);
                    $folderPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/mgs/fbuilder/restore/');
                    $fileName = $pageIdentifier . '_' . $currentDate . '.xml';
                    $filePath = $folderPath . $fileName;

                    $io = $this->ioFile;
                    $io->setAllowCreateFolders(true);
                    $io->open(['path' => $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($folderPath)]);
                    $io->write($filePath, $content, 0644);
                    $io->streamClose();

                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }

        } else {
            $url = $this->_redirect->getRefererUrl();
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }

    public function getPageById($pageId)
    {
        $collection = $this->pageFactory->create()->getCollection()->addFieldToSelect('*')->addFieldToFilter('page_id', $pageId);
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                return $item->getIdentifier();
            }
        }
        return null;
    }
}
