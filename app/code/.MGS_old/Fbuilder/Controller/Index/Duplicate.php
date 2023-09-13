<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Fbuilder\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Filesystem\DirectoryList;

class Duplicate extends \Magento\Framework\App\Action\Action
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    protected $builderHelper;
    protected $_filesystem;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CustomerSession $customerSession,
        \Magento\Framework\View\Element\Context $urlContext,
        \Magento\Framework\Filesystem $filesystem,
        CacheManager $cacheManager,
        \MGS\Fbuilder\Helper\Generate $builderHelper
    ) {
        $this->customerSession = $customerSession;
        $this->_urlBuilder = $urlContext->getUrlBuilder();
        $this->builderHelper = $builderHelper;
        $this->cacheManager = $cacheManager;
        $this->_filesystem = $filesystem;
        parent::__construct($context);
    }
    
    public function getModel($model)
    {
        return $this->_objectManager->create($model);
    }
    
    public function execute()
    {
        if (($this->customerSession->getUseFrontendBuilder() == 1)
            && ($sectionId = $this->getRequest()->getParam('id'))
        ) {
            $copySection = $this->getModel('MGS\Fbuilder\Model\Section')->load($sectionId);
            $copyData = $copySection->getData();
            $oldName = $copySection->getName();

            unset($copyData['block_id']);
            $newSection = $this->getModel('MGS\Fbuilder\Model\Section')->setData($copyData)->save();
            $newName = 'block'.$newSection->getId();
            $newSection->setName($newName)->save();
            
            if ($newSection->getPageType()=='cms') {
                $pageId = $newSection->getPageId();
                $blocks = $this->getModel('MGS\Fbuilder\Model\Child')
                    ->getCollection()
                    ->addFieldToFilter('page_id', $pageId)
                    ->addFieldToFilter('page_type', 'cms')
                    ->addFieldToFilter('block_name', ['like'=>$oldName.'-%']);
            } else {
                $productId = $newSection->getProductId();
                $blocks = $this->getModel('MGS\Fbuilder\Model\Child')
                    ->getCollection()
                    ->addFieldToFilter('product_id', $productId)
                    ->addFieldToFilter('page_type', 'product')
                    ->addFieldToFilter('block_name', ['like'=>$oldName.'-%']);
            }
            
            if (count($blocks)>0) {
                foreach ($blocks as $copyBlock) {
                    $copyData = $copyBlock->getData();
                    unset($copyData['child_id']);
                    $copyData['block_name'] = str_replace($oldName.'-', $newName.'-', $copyData['block_name']);
                    
                    $newBlock = $this->getModel('MGS\Fbuilder\Model\Child')->setData($copyData)->save();
                    $customStyle = $newBlock->getCustomStyle();
                    $customStyle = str_replace('.block'.$copyBlock->getId(), '.block'.$newBlock->getId(), $customStyle);
                    $newBlock->setCustomStyle($customStyle)->save();
                }
            }
            
            $this->generateBlockCss();
            
            $this->cacheManager->clean(['full_page']);
            $this->messageManager->addSuccess(__('You duplicated the section.'));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
    
    public function generateBlockCss()
    {
        $model = $this->getModel('MGS\Fbuilder\Model\Child');
        $collection = $model->getCollection();
        $customStyle = '';
        foreach ($collection as $child) {
            if ($child->getCustomStyle() != '') {
                $customStyle .= $child->getCustomStyle();
            }
        }
        if ($customStyle!='') {
            try {
                $this->builderHelper->generateFile($customStyle, 'blocks.css', $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/css/'));
            } catch (\Exception $e) {
                
            }
        }
    }
}
