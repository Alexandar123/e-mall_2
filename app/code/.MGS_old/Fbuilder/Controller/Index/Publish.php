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
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IOFile;
use Magento\Framework\App\State;
use Magento\Framework\App\Area as AppArea;

class Publish extends \Magento\Framework\App\Action\Action
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
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var File
     */
     protected $ioFile;
    
    /**
     * \Magento\Store\Model\App\Emulation
     */
     protected $emulation;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Element\Context $urlContext,
        CustomerSession $customerSession,
        CacheManager $cacheManager,
        SectionFactory $sectionFactory,
        BlockFactory $blockFactory,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Model\CategoryFactory $_categoryloader,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Catalog\Model\Attribute\ScopeOverriddenValue $scopeOverriddenValue,
        \Magento\Store\Model\App\Emulation $emulation,
        Filesystem $fileSystem,
        IOFile $ioFile,
        State $state
    ) {
        $this->_file = $file;
        $this->_urlBuilder = $urlContext->getUrlBuilder();
        $this->customerSession = $customerSession;
        $this->cacheManager = $cacheManager;
        $this->sectionFactory = $sectionFactory;
        $this->blockFactory = $blockFactory;
        $this->_productloader = $_productloader;
        $this->_categoryloader = $_categoryloader;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->state = $state;
        $this->fileSystem = $fileSystem;
        $this->ioFile = $ioFile;
        $this->emulation = $emulation;
        parent::__construct($context);
    }
    
    public function urlDecode($url)
    {
        $url = base64_decode(strtr($url, '-_,', '+/='));
        return $this->_urlBuilder->sessionUrlVar($url);
    }
    
    public function execute()
    {
        if (($this->customerSession->getUseFrontendBuilder() == 1) 
            && ($referer = $this->getRequest()->getParam('referrer')) 
            && (($productId = $this->getRequest()->getParam('product_id')) || ($categoryId = $this->getRequest()->getParam('category_id'))) 
            && ($storeId = $this->getRequest()->getParam('store_id'))
        ) {
            if ($this->getRequest()->getParam('product_id')) {
                $this->importContent($productId, $storeId, 'product');
            } else {
                $this->importContent($categoryId, $storeId, 'category');
            }
            
            
            $this->cacheManager->clean(['full_page']);
            $url = $this->urlDecode($referer);
        } else {
            $url = $this->_redirect->getRefererUrl();
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
    
    public function importContent($typeId, $storeId, $type)
    {
        $sectionCollection = $this->sectionFactory->create()
            ->addFieldToFilter('page_type', $type)
            ->setOrder('block_position', 'ASC');
            
        if ($type=='category') {
            $category = $this->_categoryloader->create()->load($typeId);
            $isOverriden = $this->scopeOverriddenValue->containsValue(
                \Magento\Catalog\Api\Data\CategoryInterface::class,
                $category,
                'description',
                $storeId
            );
            
            
        } else {
            $product = $this->_productloader->create()->load($typeId);
            $isOverriden = $this->scopeOverriddenValue->containsValue(
                \Magento\Catalog\Api\Data\ProductInterface::class,
                $product,
                'description',
                $storeId
            );
            
            
        }

        $sectionCollection->addFieldToFilter('product_id', $typeId);
        
        
        if ($isOverriden) {
            $sectionCollection->addFieldToFilter('store_id', $storeId);
        } else {
            $storeId = 0;
            $sectionCollection->addFieldToFilter('store_id', 0);
        }
                
        $html = $content = '';
        
        
        if (count($sectionCollection)>0) {
            $content = "<page>\n";
            foreach ($sectionCollection as $_section) {
                
                //foreach($sectionCollection as $section){
                    $content .= "\t<section>\n";
                    $sectionData = $_section->getData();
                    unset($sectionData['block_id'], $sectionData['store_id'], $sectionData['product_id'], $sectionData['page_id']);
                foreach ($sectionData as $sectionColumn => $value) {
                    $content .= "\t\t<".$sectionColumn."><![CDATA[".$value."]]></".$sectionColumn.">\n";
                }
                    $content .= "\t</section>\n";
                //}
                
            }
        
            
            $blocks = $this->blockFactory->create()->addFieldToFilter('page_type', $type);
            if ($type=='category') {
                $blocks->addFieldToFilter('product_id', $category->getId());
            } else {
                $blocks->addFieldToFilter('product_id', $product->getId());
            }
                
            
            if ($isOverriden) {
                $blocks->addFieldToFilter('store_id', $storeId);
            } else {
                $blocks->addFieldToFilter('store_id', 0);
            }
            
            if (count($blocks)>0) {
                foreach ($blocks as $block) {
                    $content .= "\t<block>\n";
                    $blockData = $block->getData();
                    unset($blockData['home_name'], $blockData['static_block_id'], $blockData['store_id'], $blockData['product_id'], $blockData['page_id']);
                    foreach ($blockData as $blockColumn => $blockValue) {
                        $content .= "\t\t<".$blockColumn."><![CDATA[".$blockValue."]]></".$blockColumn.">\n";
                    }
                    $content .= "\t</block>\n";
                }
            }
            $content .= "</page>";
            
        } else {
            // Remove File
            $folderPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/mgs/fbuilder/'.$type.'/'.$typeId.'/');
            $fileName = $storeId . '.xml';
            $filePath = $folderPath . $fileName;
            if ($this->_file->isExists($filePath)) {
                $this->_file->deleteFile($filePath);
            }
        }
        
        if (count($sectionCollection)>0) {
            foreach ($sectionCollection as $_section) {
                $html .= '<div'.$this->getSectionSetting($_section).'>';
                $html .= '<div class="frame no-padding">';
                
                $cols = $this->getBlockCols($_section);
                $class = $_section->getBlockClass();
                if ($class!='') {
                    $class = json_decode($class, true);
                }

                
                if (count($cols)>1) {
                    $html .= '<div class="line">';
                    foreach ($cols as $key => $col) {
                        $blockClass = $this->getBlockClass($_section, $col, $class, $key);
                        $html .= '<div class="'.$blockClass.'">';
                            $html .= '<div class="line">';
                                    
                        if ($type=='category') {
                            $blocks = $this->getBlocks($_section->getName().'-'.$key, $category, $isOverriden, $storeId, $type);
                        } else {
                            $blocks = $this->getBlocks($_section->getName().'-'.$key, $product, $isOverriden, $storeId, $type);
                        }
                                    
                        foreach ($blocks as $_block) {
                            $setting = json_decode($_block->getSetting(), true);
                            $html .= '<div class="panel-block-row '.$this->getChildClass($_block, $setting).'"';
                            if (isset($setting['animation']) && $setting['animation']!='') {
                                $html .= ' data-appear-animation="'.$setting['animation'].'"';
                            }
                            if (isset($setting['animation_delay']) && $setting['animation_delay']!='') {
                                $html .= ' data-appear-animation-delay="'.$setting['animation_delay'].'"';
                            }
                            $html .= '>';
                            $html .= '<div style="'.$this->getInlineSetting($_block).'">';
                            $html .= $_block->getBlockContent();
                            $html .= '</div>';
                            $html .= '</div>';
                        }
                                    
                                $html .= '</div>';
                                $html .= '</div>';
                    }
                    $html .= '</div>';
                } else {
                    $html .= '<div class="line">';
                        $html .= '<div class="col-des-12">';
                            $html .= '<div class="line">';
                                
                    if ($type=='category') {
                        $blocks = $this->getBlocks($_section->getName().'-0', $category, $isOverriden, $storeId, $type);
                    } else {
                        $blocks = $this->getBlocks($_section->getName().'-0', $product, $isOverriden, $storeId, $type);
                    }
                                
                    foreach ($blocks as $_block) {
                        $setting = json_decode($_block->getSetting(), true);
                        $html .= '<div class="panel-block-row '.$this->getChildClass($_block, $setting).'"';
                        if (isset($setting['animation']) && $setting['animation']!='') {
                            $html .= ' data-appear-animation="'.$setting['animation'].'"';
                        }
                        if (isset($setting['animation_delay']) && $setting['animation_delay']!='') {
                            $html .= ' data-appear-animation-delay="'.$setting['animation_delay'].'"';
                        }
                        $html .= '>';
                        $html .= '<div style="'.$this->getInlineSetting($_block).'">';
                        $html .= $_block->getBlockContent();
                        $html .= '</div>';
                        $html .= '</div>';
                    }
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                }

                $html .= '</div></div>';
            }
        }
        
        
        
        try {
            $this->emulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_ADMINHTML);
            if ($type=='product') {
                $product->setDescription($html);
                if ($isOverriden) {
                    $product->setStoreId($storeId);
                } else {
                    $product->setStoreId(0);
                }
                $this->productRepository->save($product);
            } else {
                $category->setDescription($html);
                if ($isOverriden) {
                    $category->setStoreId($storeId);
                } else {
                    $category->setStoreId(0);
                }
                $this->categoryRepository->save($category);
            }
            $this->emulation->stopEnvironmentEmulation();
            
            
            /* Write File */
            if ($content!='') {
                $folderPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/mgs/fbuilder/'.$type.'/'.$typeId.'/');
                $fileName = $storeId . '.xml';
                $filePath = $folderPath . $fileName;

                $io = $this->ioFile;
                $io->setAllowCreateFolders(true);
                $io->open(['path' => $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($folderPath)]);
                $io->write($filePath, $content, 0644);
                $io->streamClose();

            }
            
            $this->messageManager->addSuccess(__('You saved the %1 information.', $type));
            
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the %1.', $type));
        }
    }
    
    public function getSectionSetting($section)
    {
        $html = ' class="section-builder ';
        if ($section->getId()) {
            if ($section->getClass() != '') {
                $html.= $section->getClass();
            }

            if ($section->getParallax() & ($section->getBackgroundImage() != '')) {
                $html.= ' parallax';
            }
            
            if ($section->getNoPadding()) {
                $html.= ' no-padding-col';
            }
            
            if ($section->getFullwidth()) {
                $html.= ' section-builder-full';
            }

            $html.= '" style="';

            if ($section->getBackgroundGradient()) {
                $gradientFrom = $section->getBackgroundGradientFrom();
                $gradientTo = $section->getBackgroundGradientTo();
                if (($gradientFrom!='') || ($gradientTo!='')) {
                    if ($gradientFrom=='') {
                        $gradientFrom = '#ffffff';
                    }
                    if ($gradientTo=='') {
                        $gradientTo = '#ffffff';
                    }
                    
                    switch ($section->getBackgroundGradientOrientation()) {
                    case "vertical":
                        $html.= 'background: '.$gradientFrom.'; background: -moz-linear-gradient(top, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(top, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(to bottom, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=0 );';
                        break;
                    case "diagonal":
                        $html.= 'background: '.$gradientFrom.'; background: -moz-linear-gradient(-45deg, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(-45deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(135deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
                        break;
                    case "diagonal-bottom":
                        $html.= 'background: '.$gradientFrom.'; background: -moz-linear-gradient(45deg, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(45deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(45deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
                        break;
                    case "radial":
                        $html.= 'background: '.$gradientFrom.'; background: -moz-radial-gradient(center, ellipse cover, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-radial-gradient(center, ellipse cover, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: radial-gradient(ellipse at center, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
                        break;
                    default:
                        $html.= 'background: '.$gradientFrom.'; background: -moz-linear-gradient(left, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(left, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(to right, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
                        break;
                    }
                }
            } else {
                if ($section->getBackground() != '') {
                    $html.= 'background-color: ' .$section->getBackground() . ';';
                }
                
                if ($section->getBackgroundImage() != '') {
                    $html.= 'background-image: url(\'' . $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'mgs/fbuilder/backgrounds' . $section->getBackgroundImage() . '\');';

                    if (!$section->getParallax()) {
                        if ($section->getBackgroundRepeat()) {
                            $html.= 'background-repeat:repeat;';
                        } else {
                            $html.= 'background-repeat:no-repeat;';
                        }
                        
                        if ($section->getBackgroundCover()) {
                            $html.= 'background-size:cover;';
                        }
                    }
                }
            }



            if ($section->getPaddingTop() != '') {
                $html.= ' padding-top:' . $section->getPaddingTop() . 'px;';
            }

            if ($section->getPaddingBottom() != '') {
                $html.= ' padding-bottom:' . $section->getPaddingBottom() . 'px;';
            }
            
            $html.= '"';
            
            if ($section->getParallax()) {
                $html.= ' data-stellar-vertical-offset="20" data-stellar-background-ratio="0.6"';
            }

        }
        
        return $html;
    }
    
    public function getBlocks($blockName, $typeObject, $isOverriden, $storeId, $type)
    {
        $blocks = $this->blockFactory->create()
            ->addFieldToFilter('block_name', $blockName)
            ->addFieldToFilter('product_id', $typeObject->getId())
            ->addFieldToFilter('page_type', $type)
            ->setOrder('position', 'ASC');
        
        if ($isOverriden) {
            $blocks->addFieldToFilter('store_id', $storeId);
        } else {
            $blocks->addFieldToFilter('store_id', 0);
        }

        return $blocks;
    }
    
    public function getInlineSetting($block)
    {
        $setting = json_decode($block->getSetting(), true);
        $html = '';
        if (isset($setting['margin_top']) && ($setting['margin_top']!='')) {
            $html .= ' margin-top:'.$setting['margin_top'].'px;';
        }
        if (isset($setting['margin_bottom']) && ($setting['margin_bottom']!='')) {
            $html .= ' margin-bottom:'.$setting['margin_bottom'].'px;';
        }
        if (isset($setting['margin_left']) && ($setting['margin_left']!='')) {
            $html .= ' margin-left:'.$setting['margin_left'].'px;';
        }
        if (isset($setting['margin_right']) && ($setting['margin_right']!='')) {
            $html .= ' margin-right:'.$setting['margin_right'].'px;';
        }
        if (isset($setting['padding_top']) && ($setting['padding_top']!='')) {
            $html .= ' padding-top:'.$setting['padding_top'].'px;';
        }
        if (isset($setting['padding_bottom']) && ($setting['padding_bottom']!='')) {
            $html .= ' padding-bottom:'.$setting['padding_bottom'].'px;';
        }
        if (isset($setting['padding_left']) && ($setting['padding_left']!='')) {
            $html .= ' padding-left:'.$setting['padding_left'].'px;';
        }
        if (isset($setting['padding_right']) && ($setting['padding_right']!='')) {
            $html .= ' padding-right:'.$setting['padding_right'].'px;';
        }
        if (isset($setting['main_block_color']) && ($setting['main_block_color']!='')) {
            $html .= ' color:'.$setting['main_block_color'].';';
        }
        
        if ($block->getBackgroundGradient()) {
            $gradientFrom = $block->getBackgroundGradientFrom();
            $gradientTo = $block->getBackgroundGradientTo();
            if (($gradientFrom!='') || ($gradientTo!='')) {
                if ($gradientFrom=='') {
                    $gradientFrom = '#ffffff';
                }
                if ($gradientTo=='') {
                    $gradientTo = '#ffffff';
                }
                switch ($block->getBackgroundGradientOrientation()) {
                case "vertical":
                    $html.= 'background: '.$gradientFrom.'; background: -moz-linear-gradient(top, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(top, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(to bottom, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=0 );';
                    break;
                case "diagonal":
                    $html.= 'background: '.$gradientFrom.'; background: -moz-linear-gradient(-45deg, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(-45deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(135deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
                    break;
                case "diagonal-bottom":
                    $html.= 'background: '.$gradientFrom.'; background: -moz-linear-gradient(45deg, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(45deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(45deg, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
                    break;
                case "radial":
                    $html.= 'background: '.$gradientFrom.'; background: -moz-radial-gradient(center, ellipse cover, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-radial-gradient(center, ellipse cover, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: radial-gradient(ellipse at center, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
                    break;
                default:
                    $html.= 'background: '.$gradientFrom.'; background: -moz-linear-gradient(left, '.$gradientFrom.' 0%, '.$gradientTo.' 100%); background: -webkit-linear-gradient(left, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); background: linear-gradient(to right, '.$gradientFrom.' 0%,'.$gradientTo.' 100%); filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='.$gradientFrom.', endColorstr='.$gradientTo.',GradientType=1 );';
                    break;
                }
            }
        } else {
            if ($block->getBackground() != '') {
                $html.= 'background-color: ' .$block->getBackground() . ';';
            }
            
            if ($block->getBackgroundImage() != '') {
                $html.= 'background-image: url(\'' . $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'mgs/fbuilder/backgrounds' . $block->getBackgroundImage() . '\');';


                if ($block->getBackgroundRepeat()) {
                    $html.= 'background-repeat:repeat;';
                } else {
                    $html.= 'background-repeat:no-repeat;';
                }
                
                if ($block->getBackgroundCover()) {
                    $html.= 'background-size:cover;';
                }

            }
        }
        
        return $html;
    }
    
    public function getBlockCols($section)
    {
        $cols = $section->getBlockCols();
        $cols = str_replace(' ', '', $cols);
        $arr = explode(',', $cols);
        return $arr;
    }
    
    public function getChildClass($block, $setting)
    {
        $class = ' panel-block col-des-' . $block->getCol().' block'.$block->getId();
        
        if ($block->getColTablet()!='') {
            $class .= ' col-tb-'.$block->getColTablet();
        }
        if ($block->getColMobile()!='') {
            $class .= ' col-mb-'.$block->getColMobile();
        }
        
        if ($block->getClass()!='') {
            $class .= ' '.$block->getClass();
        }
        if (isset($setting['custom_class']) && $setting['custom_class'] != '') {
            $class .= ' ' . $setting['custom_class'];
        }
        if (isset($setting['animation']) && $setting['animation'] != '') {
            $class .= ' animated';
        }
        
        if ($block->getHideDesktop()) {
            $class.= ' hidden-des';
        }
        
        if ($block->getHideTablet()) {
            $class.= ' hidden-tb';
        }
        
        if ($block->getHideMobile()) {
            $class.= ' hidden-mb';
        }

        return $class;
    }
    
    public function getBlockClass($section, $col, $arrClass, $key)
    {
        $class = 'col-des-'.$col;
        
        $colTablets = json_decode($section->getTabletCols(), true);
        if (is_array($colTablets) && isset($colTablets[$key])) {
            $class .= ' col-tb-'.$colTablets[$key];
        }
        $colMobiles = json_decode($section->getMobileCols(), true);
        if (is_array($colMobiles) && isset($colMobiles[$key])) {
            $class .= ' col-mb-'.$colMobiles[$key];
        }
        if (is_array($arrClass) && isset($arrClass[$key])) {
            $class .= ' '.$arrClass[$key];
        }

        return $class;
    }
}
