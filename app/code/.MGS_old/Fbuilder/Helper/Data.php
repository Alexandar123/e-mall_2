<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Fbuilder\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Contact base helper
 */
class Data extends \MGS\Fbuilder\Helper\Builder
{
    protected $_date;
    
    protected $_filesystem;
    
    /**
     * @var \Magento\Framework\Xml\Parser
     */
    private $_parser;
    
    /**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;
    
    protected $filterManager;
    
    /**
     * Block factory
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;
    
    protected $_file;
    
    protected $_currentCategory;
    
    protected $_currentProduct;
    
    
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $url,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Xml\Parser $parser,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
    ) {
        parent::__construct($storeManager, $url, $request, $context, $objectManager, $pageFactory, $customerSession, $filterProvider);
        $this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->filterManager = $context->getFilterManager();
        $this->_assetRepo = $context->getAssetRepository();
        $this->_blockFactory = $blockFactory;
        $this->_file = $file;
        $this->_parser = $parser;
        $this->_escaper = $context->getEscaper();
    }

    public function getCurrentDateTime()
    {
        $now = $this->_date->gmtDate();
        return $now;
    }
    
    public function getUrlBuilder()
    {
        return $this->_url;
    }
    
    public function getPanelCssUrl()
    {
        return $this->_url->getUrl('fbuilder/index/panelstyle');
    }
    
    /* Get css content of panel */
    public function getPanelStyle()
    {
        $dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Mpanel/view/frontend/web/css/panel.css');
        $content = file_get_contents($dir);
        return $content;
    }
    
    
    
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->_request->isSecure()], $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return $this->_getNotFoundUrl();
        }
    }
    
    public function getColorAccept($type, $color = null)
    {
        $dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Fbuilder/view/frontend/web/images/panel/colour/');
        $html = '';
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                $html .= '<ul>';

                while ($files[] = readdir($dh));
                sort($files);
                foreach ($files as $file) {
                    $file_parts = pathinfo($dir . $file);
                    if (isset($file_parts['extension']) && $file_parts['extension'] == 'png') {
                        $colour = str_replace('.png', '', $file);
                        $wrapper = str_replace('_', '-', $type);
                        $_color = explode('.', $colour);
                        $colour = $wrapper . '-' . strtolower(end($_color));
                        $html .= '<li>';
                        $html .= '<a href="#" onclick="changeInputColor(\'' . $colour . '\', \'' . $type . '\', this, \'' . $wrapper . '-content\'); return false"';
                        if ($color != null && $color == $colour) {
                            $html .= ' class="active"';
                        }
                        $html .= '>';
                         $html .= '<img src="' . $this->getViewFileUrl('MGS_Fbuilder::images/panel/colour/'.$file) . '" alt=""/>';
                        $html .= '</a>';
                        $html .= '</li>';
                    }
                }
                $html .= '</ul>';
            }
        }
        return $html;
    }
    
    public function getRootCategory()
    {
        $store = $this->getStore();
        $categoryId = $store->getRootCategoryId();
        $category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
        return $category;
    }
    
    public function getTreeCategory($category, $parent, $ids = [], $checkedCat)
    {
        $rootCategoryId = $this->getRootCategory()->getId();
        $children = $category->getChildrenCategories();
        $childrenCount = count($children);
        //$checkedCat = explode(',',$checkedIds);
        $htmlLi = '<li lang="'.$category->getId().'">';
        $html[] = $htmlLi;
        //if($this->isCategoryActive($category)){
        $ids[] = $category->getId();
        //$this->_ids = implode(",", $ids);
        //}
        
        $html[] = '<a id="node'.$category->getId().'">';

        if ($category->getId() != $rootCategoryId) {
            $html[] = '<input lang="'.$category->getId().'" type="checkbox" id="radio'.$category->getId().'" name="setting[category_id][]" value="'.$category->getId().'" class="checkbox'.$parent.'"';
            if (in_array($category->getId(), $checkedCat)) {
                $html[] = ' checked="checked"';
            }
            $html[] = '/>';
        }
        

        $html[] = '<label for="radio'.$category->getId().'">' . $category->getName() . '</label>';

        $html[] = '</a>';
        
        $htmlChildren = '';
        if ($childrenCount>0) {
            foreach ($children as $child) {
                $_child = $this->getModel('Magento\Catalog\Model\Category')->load($child->getId());
                $htmlChildren .= $this->getTreeCategory($_child, $category->getId(), $ids, $checkedCat);
            }
        }
        if (!empty($htmlChildren)) {
            $html[] = '<ul id="container'.$category->getId().'">';
            $html[] = $htmlChildren;
            $html[] = '</ul>';
        }

        $html[] = '</li>';
        $html = implode("\n", $html);
        return $html;
    }
    
    
    
    /* Get all images from pub/media/wysiwyg/$type folder */
    public function getPanelUploadImages($type)
    {
        $path = 'wysiwyg/'.$type.'/';
        $dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($path);
        $result = [];
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ($files[] = readdir($dh));
                sort($files);
                foreach ($files as $file) {
                    $file_parts = pathinfo($dir . $file);
                    if (isset($file_parts['extension']) && in_array(strtolower($file_parts['extension']), ['jpg', 'jpeg', 'png', 'gif'])) {
                        $result[] = $file;
                    }
                }
            }
        }
        return $result;
    }
    
    public function getCmsBlockByIdentifier($identifier)
    {
        $block = $this->_blockFactory->create();
        $block->setStoreId($this->getStore()->getId())->load($identifier);
        return $block;
    }
    
    public function getPageById($id)
    {
        $page = $this->_pageFactory->create();
        $page->setStoreId($this->getStore()->getId())->load($id, 'identifier');
        return $page;
    }
    
    public function isFile($path, $type, $fileName)
    {
        $path = str_replace('Mgs/', '', $path);
        $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/'.$path.'/'.$type.'s/') . $fileName.'.png';
        if ($this->_file->isExists($filePath)) {
            return $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'mgs/'.$path.'/'.$type.'s/' . $fileName.'.png';
        }
        return false;
    }
    
    public function isFileExist($type, $fileName)
    {
        $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('mgs/fbuilder/'.$type) . $fileName;
        if ($this->_file->isExists($filePath)) {
            return true;
        }
        return false;
    }
    
    public function isPopup()
    {
        if ($this->_fullActionName == 'fbuilder_edit_section' 
            || $this->_fullActionName == 'fbuilder_create_block' 
            || $this->_fullActionName == 'fbuilder_create_element' 
            || $this->_fullActionName == 'fbuilder_edit_footer' 
            || $this->_fullActionName == 'fbuilder_edit_header' 
            || $this->_fullActionName == 'fbuilder_edit_staticblock'
        ) {
            return true;
        }
        return false;
    }
    
    public function convertContent($layoutContent, $builderContent = null)
    {
        return $layoutContent;
    }
    
    public function truncateString($string, $length)
    {
        return $this->filterManager->truncate($string, ['length' => $length+3]);
    }
    
    public function truncate($content, $length)
    {
        return $this->filterManager->truncate($content, ['length' => $length, 'etc' => '']);
    }
}
