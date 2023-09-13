<?php

namespace MGS\Fbuilder\Controller\Adminhtml\Fbuilder;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use MGS\Fbuilder\Model\ResourceModel\Section\CollectionFactory as sectionCollectionFactory;
use MGS\Fbuilder\Model\ResourceModel\Child\CollectionFactory as blockCollectionFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Filesystem;
use MGS\Fbuilder\Helper\Generate;

class Restore extends \MGS\Fbuilder\Controller\Adminhtml\Fbuilder
{
    protected $xmlArray;

    /**
     * @var sectionCollectionFactory
     */
    protected $sectionCollectionFactory;

    /**
     * @var blockCollectionFactory
     */
    protected $blockCollectionFactory;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Generate
     */
    protected $generateHelper;

    public function __construct(
        Action\Context $context,
        sectionCollectionFactory $sectionCollectionFactory,
        blockCollectionFactory $blockCollectionFactory,
        pageFactory $pageFactory,
        Parser $parser,
        Filesystem $fileSystem,
        Generate $generateHelper
    ) {
        parent::__construct($context);
        $this->sectionCollectionFactory = $sectionCollectionFactory;
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->pageFactory = $pageFactory;
        $this->parser = $parser;
        $this->fileSystem = $fileSystem;
        $this->generateHelper = $generateHelper;
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
            if ($this->getRequest()->getParam('page_id') && $this->getRequest()->getParam('version_id')) {
                $pageId = $this->getRequest()->getParam('page_id');
                $fileName = $this->getRequest()->getParam('version_id');
                if ($this->isFile('mgs/fbuilder/restore/'.$fileName)) {
                    $dir = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/restore/');
                    $importFile = $dir.$fileName;

                    if (is_readable($importFile)) {
                        try {
                            $this->xmlArray = $this->parser->load($importFile)->xmlToArray();
                            // Remove old sections
                            $sections = $this->sectionCollectionFactory->create()
                                ->addFieldToFilter('page_id', $pageId);

                            if (count($sections) > 0) {
                                foreach ($sections as $_section) {
                                    $_section->delete();
                                }
                            }

                            // Remove old blocks
                            $childs = $this->blockCollectionFactory->create()
                                ->addFieldToFilter('page_id', $pageId);

                            if (count($childs) > 0) {
                                foreach ($childs as $_child) {
                                    $_child->delete();
                                }
                            }

                            $html = '';

                            // Import new sections
                            $sectionArray = $this->xmlArray['page']['section'];
                            if (isset($sectionArray)) {
                                if (isset($sectionArray[0]['name'])) {
                                    foreach ($sectionArray as $section) {
                                        $section['store_id'] = 0;
                                        $section['page_id'] = $pageId;
                                        $this->_objectManager->create('MGS\Fbuilder\Model\Section')->setData($section)->save();
                                    }
                                } else {
                                    $sectionArray['store_id'] = 0;
                                    $sectionArray['page_id'] = $pageId;
                                    $this->_objectManager->create('MGS\Fbuilder\Model\Section')->setData($sectionArray)->save();
                                }
                            }

                            // Import new blocks
                            $blockArray = $this->xmlArray['page']['block'];
                            if (isset($blockArray)) {
                                if (isset($blockArray[0]['block_name'])) {
                                    foreach ($blockArray as $block) {
                                        $block['store_id'] = 0;
                                        $block['page_id'] = $pageId;
                                        $oldId = $block['child_id'];
                                        unset($block['child_id']);
                                        $child = $this->_objectManager->create('MGS\Fbuilder\Model\Child')->setData($block)->save();
                                        $customStyle = $child->getCustomStyle();
                                        $customStyle = str_replace('.block'.$oldId, '.block'.$child->getId(), $customStyle);
                                        $child->setCustomStyle($customStyle)->save();
                                    }
                                } else {
                                    $blockArray['store_id'] = 0;
                                    $blockArray['page_id'] = $pageId;
                                    $oldId = $blockArray['child_id'];
                                    unset($blockArray['child_id']);
                                    $child = $this->_objectManager->create('MGS\Fbuilder\Model\Child')->setData($blockArray)->save();
                                    $customStyle = $child->getCustomStyle();
                                    $customStyle = str_replace('.block'.$oldId, '.block'.$child->getId(), $customStyle);
                                    $child->setCustomStyle($customStyle)->save();
                                }
                            }

                            $this->generateHelper->importContent((int)$pageId);

                            $this->generateBlockCss();

                            //$this->_eventManager->dispatch('mgs_fbuilder_import_before_end', ['content' => $this->_xmlArray]);

                            $result['result'] = 'success';
                        } catch (\Exception $e) {
                            $result['result'] = $e->getMessage();
                        }
                    } else {
                        $result['result'] = __('Cannot import page');
                    }
                    $result['data'] = $fileName;
                }


            } else {
                $result['result'] = __('Have no page to import');
            }

            return $this->getResponse()->setBody(json_encode($result));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    public function generateBlockCss()
    {
        $model = $this->_objectManager->create('MGS\Fbuilder\Model\Child');
        $collection = $model->getCollection();
        $customStyle = '';
        foreach ($collection as $child) {
            if ($child->getCustomStyle() != '') {
                $customStyle .= $child->getCustomStyle();
            }
        }
        if ($customStyle!='') {
            try {
                $this->generateHelper->generateFile($customStyle, 'blocks.css', $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/css/'));
            } catch (\Exception $e) {

            }
        }
    }

    public function isFile($filename)
    {
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);

        return $mediaDirectory->isFile($filename);
    }
}
