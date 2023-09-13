<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Controller\Adminhtml\Fbuilder;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \MGS\Fbuilder\Controller\Adminhtml\Fbuilder
{
    protected $_section;
    protected $_block;
    
    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;
    
    protected $fileFactory;
    
    protected $resultRawFactory;
    
    protected $date;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MGS\Fbuilder\Model\ResourceModel\Section\CollectionFactory $sectionFactory,
        \MGS\Fbuilder\Model\ResourceModel\Child\CollectionFactory $blockFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->_section = $sectionFactory;
        $this->_block = $blockFactory;
        $this->_pageFactory = $pageFactory;
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->date = $date;
    }

    /**
     * Edit sitemap
     *
     * @return                                  void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if ($pageId = $this->getRequest()->getParam('page_id')) {
            $page = $this->_pageFactory->create()->load($pageId);
            $sectionCollection = $this->_section->create();
            $sectionCollection->addFieldToFilter('page_id', $pageId);
            $content = '';
            if (count($sectionCollection)>0) {
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
                
                $blockCollection = $this->_block->create();
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
                $contentObject = new \Magento\Framework\DataObject(['label' => $content]);
                $this->_eventManager->dispatch('mgs_fbuilder_export_before_end', ['content' => $contentObject]);
                
                $content .= $contentObject->getContent();
                
                $content .= "</page>";
            }
            
            try {
                if ($content!='') {
                    $fileName = strtotime($this->date->gmtDate()).'_page_'.$pageId.'.xml';
                    return $this->fileFactory->create($fileName, $content, 'var');
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Have no page to export'));
        }
        
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
