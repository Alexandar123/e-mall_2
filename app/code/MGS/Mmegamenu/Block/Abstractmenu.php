<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Block;

use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Context;
/**
 * Main contact form block
 */
abstract class Abstractmenu extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var Category
     */
    protected $_categoryInstance;

    /**
     * Current category key
     *
     * @var string
     */
    protected $_currentCategoryKey;

    /**
     * Array of level position counters
     *
     * @var array
     */
    protected $_itemLevelPositions = [];

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $flatState;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_urlinterface;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogLayer = $layerResolver->get();
        $this->httpContext = $httpContext;
        $this->_catalogCategory = $catalogCategory;
        $this->_registry = $registry;
        $this->flatState = $flatState;
        $this->_categoryInstance = $categoryFactory->create();
        $this->_objectManager = $objectManager;
        $this->_urlinterface = $context->getUrlBuilder();
        $this->_filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            [
                'cache_lifetime' => false,
                'cache_tags' => [Category::CACHE_TAG, \Magento\Store\Model\Group::CACHE_TAG],
            ]
        );
    }

    /**
     * Get current category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $shortCacheId = [
            'CATALOG_NAVIGATION',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getCurrentCategoryKey(),
        ];
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['category_path'] = $this->getCurrentCategoryKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * Get current category key
     *
     * @return string
     */
    public function getCurrentCategoryKey()
    {
        if (!$this->_currentCategoryKey) {
            $category = $this->_registry->registry('current_category');
            if ($category) {
                $this->_currentCategoryKey = $category->getPath();
            } else {
                $this->_currentCategoryKey = $this->_storeManager->getStore()->getRootCategoryId().'/'.time();
            }
        }

        return $this->_currentCategoryKey;
    }

    /**
     * Retrieve child categories of current category
     *
     * @return \Magento\Framework\Data\Tree\Node\Collection
     */
    public function getCurrentChildCategories()
    {
        $categories = $this->_catalogLayer->getCurrentCategory()->getChildrenCategories();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->_productCollectionFactory->create();
        $this->_catalogLayer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($categories);
        return $categories;
    }

    /**
     * Checkin activity of category
     *
     * @param   \Magento\Framework\DataObject $category
     * @return  bool
     */
    public function isCategoryActive($category)
    {
        if ($this->getCurrentCategory()) {
            if (is_array($this->getCurrentCategory()->getPathIds()) && in_array($category->getId(), $this->getCurrentCategory()->getPathIds())) {
                return in_array($category->getId(), $this->getCurrentCategory()->getPathIds());
            }
        }
        return false;
    }

    /**
     * Get url for category data
     *
     * @param Category $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        if ($category instanceof Category) {
            $url = $category->getUrl();
        } else {
            $url = $this->_categoryInstance->setData($category->getData())->getUrl();
        }

        return $url;
    }

    /**
     * Enter description here...
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->_catalogLayer->getCurrentCategory();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Catalog\Model\Category::CACHE_TAG, \Magento\Store\Model\Group::CACHE_TAG];
    }

    /* Megamenu Begin */
    public function getModel($model){
        return $this->_objectManager->create($model);
    }

    public function getResourceModel($model) {
        return $this->_objectManager->create($model);
    }
    public function getStore(){
        return $this->_storeManager->getStore();
    }

    public function getClass($item) {
        $type = $item->getMenuType();
        $class = $item->getSpecialClass();
        $class.=' ' . $item->getAlignMenu();
        if ($item->getColumns() > 1) {
            $class.= 'mega-menu-item mega-menu-fullwidth menu-'. $item->getColumns() .'columns level0';
        }
        if ($type == 2) {
            $class.= " static-menu level0";
            $currentUrl = $this->_urlinterface->getCurrentUrl();
            if ($currentUrl == $item->getUrl()) {
                $class.= " active";
            }

            if ($item->getStaticContent() != '') {
                $class.= ' dropdown';
            }
        } else {

            $categoryId = $item->getCategoryId();
            $category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
            $subCatAccepp = $this->getSubCategoryAccepp($categoryId, $item);

            $class.= " category-menu level0";

            if (count($subCatAccepp) > 0) {
                $class.= ' dropdown';
            }
            if ($this->isCategoryActive($category)) {
                $store = $this->getStore();
                if ($store->getRootCategoryId() != $category->getId()) {
                    $class.= " active";
                }
            }
        }
        return $class;
    }

    public function getSubCategoryAccepp($categoryId, $item) {
        $subCatExist = explode(',', $item->getSubCategoryIds() ?? '');

        $category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);

        $children = $category->getChildrenCategories()
			//->addAttributeToFilter('include_in_menu', 1)
			;
        $childrenCount = count($children);

        $subCatId = array();
        if ($childrenCount > 0) {
            foreach ($children as $child) {
                if (in_array($child->getId(), $subCatExist)) {
                    $subCatId[] = $child->getId();
                }
            }
        }
        return $subCatId;
    }

    public function getMenuHtml($item) {
        $type = $item->getMenuType();
        if ($type == 2) {
            return $this->getStaticMenu($item);
        } else {
            return $this->getCategoryMenu($item);
        }
    }

    public function getCategoryMenu($item) {

        $html = '<a';
        $categoryId = $item->getCategoryId();
        $subCatAccepp = $this->getSubCategoryAccepp($categoryId, $item);
        if ($categoryId) {
            $category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
            $html.=' href="';
            if ($item->getUrl() != '') {
                if (filter_var($item->getUrl(), FILTER_VALIDATE_URL)) {
                    $html = $item->getUrl() . '"';
                }else{
                    $html.= $this->getUrl($item->getUrl()) . '"';
                }

                $html.= $this->getUrl($item->getUrl()) . '"';
            } else {
                if ($this->getStore()->getRootCategoryId() == $category->getId()) {
                    $html.='#" onclick="return false"';
                } else {
                    $html.= $this->getCategoryUrl($category) . '"';
                }
            }
        }
        $html.=' class="level0';

        if (count($subCatAccepp) > 0) {
            $html.= ' dropdown-toggle';
        }

        $html.='">';
        if ($item->getHtmlLabel() != '') {
            $html.=$item->getHtmlLabel();
        }
        $html.='<span data-hover="'.$item->getTitle().'">'.$item->getTitle().'</span>';
        if (count($subCatAccepp) > 0) {
            $html.= ' <span class="icon-next"><i class="fa fa-angle-down"></i></span>';
        }
        $html.= '</a>';

        if (count($subCatAccepp) > 0 || $item->getTopContent() != '' || $item->getBottomContent() != '') {
            $html.='<span class="toggle-menu"><a onclick="toggleEl(this,\'mobile-menu-' . $item->getId() . '-'. $item->getParentId() . '\')" href="javascript:void(0)" class=""><span class="fa fa-chevron-right"></span></a></span>';
            $html.='<ul class="dropdown-menu" id="mobile-menu-' . $item->getId() . '-' . $item->getParentId() . '"><li class="hidden-des"><a onclick="toggleEl(this,\'mobile-menu-' . $item->getId() . '-' . $item->getParentId() . '\')" href="javascript:void(0)" class=""><span class="nav-back"><i class="fa fa-chevron-left"></i> Back</span></a></li><li>';
            $columnAccepp = count($subCatAccepp);
            $arrColumn = [];
            if ($columnAccepp > 0) {
                $columns = $item->getColumns();
                if($columns > 1 && $item->getLeftContent()!='' && $item->getLeftCol()!=0){
                    $columns = $columns - $item->getLeftCol();
                }

                if($columns > 1 && $item->getRightContent()!='' && $item->getRightCol()!=0){
                    $columns = $columns - $item->getRightCol();
                }

                $arrOneElement = array_chunk($subCatAccepp, 1);
                $countCat = count($subCatAccepp);
                $count = 0;
                while ($countCat > 0) {
                    for ($i = 0; $i < $columns; $i++) {
                        if (isset($subCatAccepp[$count])) {
                            $arrColumn[$i][] = $subCatAccepp[$count];
                            $count++;
                        }
                    }
                    $countCat--;
                }

                $newArrColumn = [];
                $newCount = 0;

                for ($i = 0; $i < count($arrColumn); $i++) {
                    $newColumn = count($arrColumn[$i]);
                    for ($j = 0; $j < $newColumn; $j++) {
                        $newArrColumn[$i][$j] = $subCatAccepp[$newCount];
                        $newCount++;
                    }
                }

                $arrColumn = $newArrColumn;



                if ($columns > 1) {
                    $html.= '<div class="mega-menu-content"><div class="line">';

                    if ($item->getTopContent() != '') {
                        $html.='<div class="top_content static-content col-12">';
                        $html.= $this->_filterProvider->getBlockFilter()->filter($item->getTopContent());
                        $html.='</div>';
                    }

                    if($item->getLeftContent()!='' && $item->getLeftCol()!=0){
                        $html.='<div class="left_content static-content col-'.$this->getColumnByCol($item->getColumns()) * $item->getLeftCol().'">';
                        $html.= $this->_filterProvider->getBlockFilter()->filter($item->getLeftContent());
                        $html.='</div>';
                    }
                } else {
                    $html.= '<ul>';
                }
                foreach ($arrColumn as $_arrColumn) {
                    $html.= $this->drawListSub($item, $_arrColumn);
                }

                if ($columns > 1) {
                    if($item->getRightContent()!='' && $item->getRightCol()!=0){
                        $html.='<div class="right_content static-content col-'.$this->getColumnByCol($item->getColumns()) * $item->getRightCol().'">';
                        $html.= $this->_filterProvider->getBlockFilter()->filter($item->getRightContent());
                        $html.='</div>';
                    }

                    if ($item->getBottomContent() != '') {
                        $html.='<div class="bottom_content static-content col-12">';
                        $html.= $this->_filterProvider->getBlockFilter()->filter($item->getBottomContent());
                        $html.='</div>';
                    }

                    $html.= '</div></div>';
                } else {
                    $html.= '</ul>';
                }
            }


            $html.='</li></ul>';
        }

        return $html;
    }

    public function drawListSub($item, $catIds) {
        $html = '';

        if ($item->getColumns() > 1) {
            $html.='<div class="col-12 col-des-' . $this->getColumnByCol($item->getColumns()) . '"><ul class="sub-menu">';
        }

        if (count($catIds) > 0) {
            foreach ($catIds as $categoryId) {
                $category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
                $html.= $this->drawList($category, $item);
            }
        }

        if ($item->getColumns() > 1) {
            $html.='</ul></div>';
        }

        return $html;
    }

    public function drawList($category, $item, $level = 1) {
        /* $maxLevel = $item->getMaxLevel();
        if ($maxLevel == '' || $maxLevel == NULL) {
            $maxLevel = Mage::getStoreConfig('megamenu/general/max_level');
        }

        if ($maxLevel == 0 || $maxLevel == '' || $maxLevel == NULL) {
            $maxLevel = 100;
        } */
        $maxLevel = 10;

        $children = $this->getSubCategoryAccepp($category->getId(), $item);
        $childrenCount = count($children);
        $includeInMenu = $category->getData('include_in_menu');

        $htmlLi = '<li';

        if ($includeInMenu != 0) {

            $htmlLi .= ' class="level'.$level.'';
            if ($childrenCount > 0 && $item->getColumns() == 1) {
                $htmlLi .= ' category-submenu';
            }

            $htmlLi .= '">';

            $html[] = $htmlLi;
            $html[] = '<a href="' . $this->getCategoryUrl($category) . '">';
            if ($item->getColumns() > 1 && $level == 1) {
                $html[] = '<span class="mega-menu-sub-title">';
            }

            $html[] = $category->getName();

            if($category->getMgsMegamenuItemLabel()){
                $backgroundLabel = "";
                if($category->getMgsMegamenuItemBackground()){
                    $backgroundLabel = $category->getMgsMegamenuItemBackground();
                }
                if($backgroundLabel != ""){
                    $html[] = '<span class="label-menu" style="background-color: '.$backgroundLabel.'; border-color: '.$backgroundLabel.';">';
                }else {
                    $html[] = '<span class="label-menu">';
                }
                $html[] = $category->getMgsMegamenuItemLabel();
                $html[] = '</span>';
            }

            if ($item->getColumns() > 1 && $level == 1) {
                $html[] = '</span>';
            }

            if ($childrenCount > 0 && $item->getColumns() == 1) {
                $html[] = '<span class="icon-next"><i class="fa fa-angle-right">&nbsp;</i></span>';
            }

            $html[] = '</a>';

            if ($level < $maxLevel) {


                $maxSub = 50;

                $htmlChildren = '';
                if ($childrenCount > 0) {
                    $i = 0;
                    foreach ($children as $child) {
                        $i++;
                        if ($i <= $maxSub) {
                            $_child = $this->getModel('Magento\Catalog\Model\Category')->load($child);
                            $htmlChildren .= $this->drawList($_child, $item, ($level + 1));
                        }
                    }
                }
                if (!empty($htmlChildren)) {
                    $html[] = '<span class="toggle-menu"><a onclick="toggleEl(this,\'mobile-menu-cat-' . $category->getId() . '-' . $item->getParentId() . '\')" href="javascript:void(0)" class=""><span class="fa fa-chevron-right"></span></a></span>';
                    //$html[] = '</div>';
                    $html[] = '<ul id="mobile-menu-cat-' . $category->getId() . '-' . $item->getParentId() . '"';
                    if ($item->getColumns() > 1) {
                        $html[] = ' class="sub-menu"';
                    } else {
                        $html[] = ' class="dropdown-submenu"';
                    }
                    $html[] = '>';
                    $html[] = '<li class="hidden-des"><a onclick="toggleEl(this,\'mobile-menu-cat-' . $category->getId() . '-' . $item->getParentId() . '\')" href="javascript:void(0)" class=""><span class="nav-back"><i class="fa fa-chevron-left"></i> Back</span></a></li>';
                    $html[] = $htmlChildren;
                    $html[] = '</ul>';
                }
            }
            $html[] = '</li>';
            $html = implode("\n", $html);
            return $html;
        }
        return null;
    }

    public function getStaticMenu($item) {

        if (filter_var($item->getUrl(), FILTER_VALIDATE_URL)) {
            $html = '<a href="' . $item->getUrl() . '" class="level0';
        }else{
            $html = '<a href="' . $this->getUrl($item->getUrl()) . '" class="level0';
        }

        if ($item->getStaticContent() != '') {
            $html.= ' dropdown-toggle';
        }
        $html.= '">';
        $html.='<span>'.$item->getTitle().'</span>';
        if ($item->getHtmlLabel() != '') {
            $html.=$item->getHtmlLabel();
        }
        if ($item->getStaticContent() != '') {
            $html.= ' <span class="icon-next"><i class="fa fa-angle-down"></i></span>';
        }
        $html.='</a>';
        if ($item->getStaticContent() != '') {
            $html.='<span class="toggle-menu"><a onclick="toggleEl(this,\'mobile-menu-' . $item->getId() . '-' . $item->getParentId() . '\')" href="javascript:void(0)" class=""><span class="fa fa-chevron-right"></span></a></span>';
            $col = $item->getColumns();
            $html.='<ul class="dropdown-menu" id="mobile-menu-' . $item->getId() . '-' . $item->getParentId() . '"><li class="hidden-des"><a onclick="toggleEl(this,\'mobile-menu-' . $item->getId() . '-' . $item->getParentId() . '\')" href="javascript:void(0)" class=""><span class="nav-back"><i class="fa fa-chevron-left"></i> Back</span></a></li><li>';


            $staticContent = $this->_filterProvider->getBlockFilter()->filter($item->getStaticContent());

            $html.= $staticContent;

            $html.='</li></ul>';
        }
        return $html;
    }

    public function getColumnByCol($col) {
        return 12/$col;
    }

    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }
}

