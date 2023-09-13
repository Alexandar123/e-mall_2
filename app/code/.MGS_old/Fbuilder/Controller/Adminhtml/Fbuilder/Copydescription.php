<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Controller\Adminhtml\Fbuilder;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Copydescription extends \MGS\Fbuilder\Controller\Adminhtml\Fbuilder
{
    protected $_sectionCollection;
    protected $_blockCollection;

    protected $_filesystem;
    protected $_fileUploaderFactory;
    protected $_file;
    
    /**
     * @var \Magento\Framework\Xml\Parser
     */
    private $_parser;
    
    protected $_xmlArray;
    protected $_generateHelper;

    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MGS\Fbuilder\Model\ResourceModel\Section\CollectionFactory $sectionCollectionFactory,
        \MGS\Fbuilder\Model\ResourceModel\Child\CollectionFactory $blockCollectionFactory,
        \Magento\Framework\Xml\Parser $parser,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \MGS\Fbuilder\Helper\Generate $generateHelper,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Catalog\Model\Product $product
    ) {
        parent::__construct($context);
        $this->product = $product;
        $this->_sectionCollection = $sectionCollectionFactory;
        $this->_blockCollection = $blockCollectionFactory;
        $this->_filesystem = $filesystem;
        $this->_file = $file;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_parser = $parser;
        $this->_generateHelper = $generateHelper;
    }

    /**
     * Edit sitemap
     *
     * @return                                  void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        try {
            $storeId = $this->getRequest()->getParam('store_id');

            $parentProductId = (int)$this->getRequest()->getParam('mgs_file_field');
            $data['description'] = $this->product->getResource()->getAttributeRawValue($parentProductId, 'description', $storeId);
            $selectedIds = $this->getRequest()->getParam('selected');
            
            // Update product description for selected products
            $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class)
                ->updateAttributes($selectedIds, $data, 0);
            
            $dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/product/'.$parentProductId);
            $xmlFile = $dir.'/'.$storeId.'.xml';
            
            $xmlArray = $this->_parser->load($xmlFile)->xmlToArray();
                                    
            foreach ($selectedIds as $_productId) {
                // Remove old sections
                $sections = $this->_sectionCollection->create()
                    ->addFieldToFilter('product_id', $_productId)
                    ->addFieldToFilter('page_type', 'product')
                    ->addFieldToFilter('store_id', '0');

                if (count($sections) > 0) {
                    foreach ($sections as $_section) {
                        $_section->delete();
                    }
                }
                
                // Remove old blocks
                $childs = $this->_blockCollection->create()
                    ->addFieldToFilter('product_id', $_productId)
                    ->addFieldToFilter('page_type', 'product')
                    ->addFieldToFilter('store_id', '0');

                if (count($childs) > 0) {
                    foreach ($childs as $_child) {
                        $_child->delete();
                    }
                }
                
                $html = '';
                
                // Import new sections
                $sectionArray = $xmlArray['page']['section'];
                if (isset($sectionArray)) {
                    if (isset($sectionArray[0]['name'])) {
                        foreach ($sectionArray as $section) {
                            $section['store_id'] = 0;
                            $section['product_id'] = $_productId;
                            $this->_objectManager->create('MGS\Fbuilder\Model\Section')->setData($section)->save();
                        }
                    } else {
                        $sectionArray['store_id'] = 0;
                        $sectionArray['product_id'] = $_productId;
                        $this->_objectManager->create('MGS\Fbuilder\Model\Section')->setData($sectionArray)->save();
                    }
                }
                
                // Import new blocks
                $blockArray = $xmlArray['page']['block'];
                if (isset($blockArray)) {
                    if (isset($blockArray[0]['block_name'])) {
                        foreach ($blockArray as $block) {
                            $block['store_id'] = 0;
                            $block['product_id'] = $_productId;
                            $oldId = $block['child_id'];
                            unset($block['child_id']);
                            $child = $this->_objectManager->create('MGS\Fbuilder\Model\Child')->setData($block)->save();
                            $customStyle = $child->getCustomStyle();
                            $customStyle = str_replace('.block'.$oldId, '.block'.$child->getId(), $customStyle);
                            $child->setCustomStyle($customStyle)->save();
                        }
                    } else {
                        $blockArray['store_id'] = 0;
                        $blockArray['product_id'] = $_productId;
                        $oldId = $blockArray['child_id'];
                        unset($blockArray['child_id']);
                        $child = $this->_objectManager->create('MGS\Fbuilder\Model\Child')->setData($blockArray)->save();
                        $customStyle = $child->getCustomStyle();
                        $customStyle = str_replace('.block'.$oldId, '.block'.$child->getId(), $customStyle);
                        $child->setCustomStyle($customStyle)->save();
                    }
                }
            }
            
            $this->generateBlockCss();
            $this->messageManager->addSuccess(
                __('Successfully updated product description')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
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
                $this->_generateHelper->generateFile($customStyle, 'blocks.css', $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/css/'));
            } catch (\Exception $e) {
                
            }
        }
    }
    
    public function isFile($filename)
    {
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR);

        return $mediaDirectory->isFile($filename);
    }
}
