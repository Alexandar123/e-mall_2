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

class Pasteblock extends \Magento\Framework\App\Action\Action
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    protected $builderHelper;
    protected $_filesystem;
    
    protected $_productloader;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CustomerSession $customerSession,
        \Magento\Framework\View\Element\Context $urlContext,
        \Magento\Framework\Filesystem $filesystem,
        CacheManager $cacheManager,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Model\CategoryFactory $_categoryloader,
        \Magento\Catalog\Model\Attribute\ScopeOverriddenValue $scopeOverriddenValue,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MGS\Fbuilder\Helper\Generate $builderHelper
    ) {
        $this->customerSession = $customerSession;
        $this->_urlBuilder = $urlContext->getUrlBuilder();
        $this->builderHelper = $builderHelper;
        $this->cacheManager = $cacheManager;
        $this->_filesystem = $filesystem;
        $this->_productloader = $_productloader;
        $this->_categoryloader = $_categoryloader;
        
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
    
    public function getModel($model)
    {
        return $this->_objectManager->create($model);
    }
    
    public function execute()
    {
        if (($this->customerSession->getUseFrontendBuilder() == 1)
            && ($blockId = $this->customerSession->getBlockCopied())
            && ($pageId = $this->getRequest()->getParam('page_id'))
            && ($blockName = $this->getRequest()->getParam('block_name'))
        ) {
            $copyBlock = $this->getModel('MGS\Fbuilder\Model\Child')->load($blockId);
            $copyData = $copyBlock->getData();
            
            unset($copyData['child_id'], $copyData['store_id']);
            
            $copyData['block_name'] = $blockName;
            
            if ($copyData['type']=='modal_popup') {
                $settings = json_decode($copyData['setting'], true);
                $generateBlockId = $settings['generate_block_id'];
                $newGenerateBlockId = rand() . time();
                
                $copyData['setting'] = str_replace($generateBlockId, $newGenerateBlockId, $copyData['setting']);
                $copyData['block_content'] = str_replace($generateBlockId, $newGenerateBlockId, $copyData['block_content']);
                $copyData['custom_style'] = str_replace($generateBlockId, $newGenerateBlockId, $copyData['custom_style']);
            }
            if ($this->getRequest()->getParam('page_type')=='cms') {
                $copyData['product_id'] = null;
                $copyData['page_type'] = 'cms';
                $copyData['page_id'] = $pageId;
            } else {
                $copyData['page_id'] = null;
                $copyData['product_id'] = $pageId;
                if ($this->getRequest()->getParam('page_type')=='category') {
                    $copyData['page_type'] = 'category';
                    $category = $this->_categoryloader->create()->load($pageId);
                    $isOverriden = $this->scopeOverriddenValue->containsValue(
                        \Magento\Catalog\Api\Data\CategoryInterface::class,
                        $category,
                        'description',
                        $this->_storeManager->getStore()->getId()
                    );

                } else {
                    $copyData['page_type'] = 'product';
                    $product = $this->_productloader->create()->load($pageId);
                    $isOverriden = $this->scopeOverriddenValue->containsValue(
                        \Magento\Catalog\Api\Data\ProductInterface::class,
                        $product,
                        'description',
                        $this->_storeManager->getStore()->getId()
                    );
                    
                }
                if ($isOverriden) {
                    $copyData['store_id'] = $this->_storeManager->getStore()->getId();
                } else {
                    $copyData['store_id'] = 0;
                }
            }
            $newBlock = $this->getModel('MGS\Fbuilder\Model\Child')->setData($copyData)->save();
            $customStyle = $newBlock->getCustomStyle();
            $customStyle = str_replace('.block'.$blockId, '.block'.$newBlock->getId(), $customStyle);
            $newBlock->setCustomStyle($customStyle)->save();
            
            $this->generateBlockCss();
            
            $this->cacheManager->clean(['full_page']);
            $this->messageManager->addSuccess(__('You duplicated the block.'));
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
                $this->builderHelper->generateFile($customStyle, 'blocks.min.css', $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/css/'));
            } catch (\Exception $e) {
                
            }
        }
    }
}
