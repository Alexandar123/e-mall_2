<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Fbuilder\Block\Cms;

/**
 * Main contact form block
 */
class Page extends \Magento\Cms\Block\Page
{
    
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;
    
    protected $_panelHelper;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context    $context
     * @param \Magento\Cms\Model\Page                    $page
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\PageFactory             $pageFactory
     * @param \Magento\Framework\View\Page\Config        $pageConfig
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Cms\Model\Page $page,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Framework\View\Page\Config $pageConfig,
        \MGS\Fbuilder\Helper\Builder $panelHelper,
        array $data = []
    ) {
        parent::__construct($context, $page, $filterProvider, $storeManager, $pageFactory, $pageConfig, $data);
        $this->_panelHelper = $panelHelper;
    }
    
    protected function _prepareLayout()
    {
        $page = $this->getPage();
        $this->_addBreadcrumbs($page);
        $this->pageConfig->addBodyClass('cms-' . $page->getIdentifier());
        $this->pageConfig->addBodyClass('cms-page ' . $page->getId());
        
        $canUsePanel = $this->_panelHelper->acceptToUsePanel();
        if ($canUsePanel) {
            $this->pageConfig->addBodyClass('active-builder');
        }
        
        $metaTitle = $page->getMetaTitle();
        $this->pageConfig->getTitle()->set($metaTitle ? $metaTitle : $page->getTitle());
        $this->pageConfig->setKeywords($page->getMetaKeywords());
        $this->pageConfig->setDescription($page->getMetaDescription());

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            // Setting empty page title if content heading is absent
            $cmsTitle = $page->getContentHeading() ?: ' ';
            $pageMainTitle->setPageTitle($this->escapeHtml($cmsTitle));
        }
        return parent::_prepareLayout();
    }
    
    /**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        $canUsePanel = $this->_panelHelper->acceptToUsePanel();
        if ($canUsePanel) {
            return $this->getLayout()->createBlock('MGS\Fbuilder\Block\Panel\HomeContent')->setCanUsePanel($canUsePanel)->setTemplate('panel/homecontent.phtml')->toHtml();
        } else {
            return parent::_toHtml();
        }
    }
}
