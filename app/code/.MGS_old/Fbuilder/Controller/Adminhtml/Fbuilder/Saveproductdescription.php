<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Controller\Adminhtml\Fbuilder;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Saveproductdescription extends \MGS\Fbuilder\Controller\Adminhtml\Fbuilder
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
     * \Magento\Store\Model\App\Emulation
     */
     protected $emulation;
     
    /**
     * @param \Magento\Backend\App\Action\Context                         $context
     * @param \Magento\Framework\Registry                                 $coreRegistry
     * @param \MGS\Fbuilder\Model\ResourceModel\Section\CollectionFactory $sectionCollectionFactory
     * @param \MGS\Fbuilder\Model\ResourceModel\Child\CollectionFactory   $blockCollectionFactory
     * @param \Magento\Framework\Xml\Parser                               $parser
     * @param \Magento\Framework\Filesystem                               $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory            $fileUploaderFactory
     * @param \MGS\Fbuilder\Helper\Generate                               $generateHelper
     * @param \Magento\Framework\Filesystem\Driver\File                   $file
     * @param \Magento\Catalog\Model\Product                              $product
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
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
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
        $this->emulation = $emulation;
        $this->productRepository = $productRepository;
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
            $storeId = $this->getRequest()->getParam('storeId');
            $currentStore = 0;
            if ($this->getRequest()->getParam('store')) {
                $currentStore = $this->getRequest()->getParam('store');
            }

            $parentProductId = (int)$this->getRequest()->getParam('parent_product_id');
            $data['description'] = $this->product->getResource()->getAttributeRawValue($parentProductId, 'description', $storeId);
            $selectedIds[] = $_productId = $this->getRequest()->getParam('product_id');
            
            // Update product description for selected products
            $this->emulation->startEnvironmentEmulation($currentStore, \Magento\Framework\App\Area::AREA_ADMINHTML);
            /* $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class)
                    ->updateAttributes($selectedIds, $data, $currentStore); */
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_productId);
            $product->setDescription($data['description']);
            $product->setStoreId($currentStore);
            $this->productRepository->save($product);
            $this->emulation->stopEnvironmentEmulation();
            $dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/product/'.$parentProductId);
            $xmlFile = $dir.'/'.$storeId.'.xml';
            
            $xmlArray = $this->_parser->load($xmlFile)->xmlToArray();

            // Remove old sections
            $sections = $this->_sectionCollection->create()
                ->addFieldToFilter('product_id', $_productId)
                ->addFieldToFilter('page_type', 'product')
                ->addFieldToFilter('store_id', $currentStore);

            if (count($sections) > 0) {
                foreach ($sections as $_section) {
                    $_section->delete();
                }
            }
            
            // Remove old blocks
            $childs = $this->_blockCollection->create()
                ->addFieldToFilter('product_id', $_productId)
                ->addFieldToFilter('page_type', 'product')
                ->addFieldToFilter('store_id', $currentStore);

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
                        $section['store_id'] = $currentStore;
                        $section['product_id'] = $_productId;
                        $this->_objectManager->create('MGS\Fbuilder\Model\Section')->setData($section)->save();
                    }
                } else {
                    $sectionArray['store_id'] = $currentStore;
                    $sectionArray['product_id'] = $_productId;
                    $this->_objectManager->create('MGS\Fbuilder\Model\Section')->setData($sectionArray)->save();
                }
            }
            
            // Import new blocks
            $blockArray = $xmlArray['page']['block'];
            if (isset($blockArray)) {
                if (isset($blockArray[0]['block_name'])) {
                    foreach ($blockArray as $block) {
                        $block['store_id'] = $currentStore;
                        $block['product_id'] = $_productId;
                        $oldId = $block['child_id'];
                        unset($block['child_id']);
                        $child = $this->_objectManager->create('MGS\Fbuilder\Model\Child')->setData($block)->save();
                        $customStyle = $child->getCustomStyle();
                        $customStyle = str_replace('.block'.$oldId, '.block'.$child->getId(), $customStyle);
                        $child->setCustomStyle($customStyle)->save();
                    }
                } else {
                    $blockArray['store_id'] = $currentStore;
                    $blockArray['product_id'] = $_productId;
                    $oldId = $blockArray['child_id'];
                    unset($blockArray['child_id']);
                    $child = $this->_objectManager->create('MGS\Fbuilder\Model\Child')->setData($blockArray)->save();
                    $customStyle = $child->getCustomStyle();
                    $customStyle = str_replace('.block'.$oldId, '.block'.$child->getId(), $customStyle);
                    $child->setCustomStyle($customStyle)->save();
                }
            }
            $this->generateBlockCss();
            $this->messageManager->addSuccess(
                __('Successfully updated product description')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
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
