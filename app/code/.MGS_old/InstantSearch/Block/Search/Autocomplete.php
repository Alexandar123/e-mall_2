<?php
namespace MGS\InstantSearch\Block\Search;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MGS\InstantSearch\Helper\Data;
class Autocomplete extends Template
{
    const XML_PATH_PRODUCT_SORT_ORDER = 'instantsearch/additional_product/sort_order';
    const XML_PATH_CATEGORY_SORT_ORDER = 'instantsearch/additional_category/sort_order';
    const XML_PATH_CMS_PAGE_SORT_ORDER = 'instantsearch/additional_cms_page/sort_order';
    const XML_PATH_BLOG_SORT_ORDER = 'instantsearch/additional_blog/sort_order';
    /**
    * @var array|\Magento\Checkout\Block\Checkout\LayoutProcessorInterface []
    */
   protected $layoutProcessors;

   /**
     * @var Data
     */
    protected $_inSearchHelper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data $inSearchHelper
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $inSearchHelper,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->layoutProcessors = $layoutProcessors;
        $this->_inSearchHelper = $inSearchHelper;
    }

    /**
     * Retrieve search action url
     *
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->getUrl("instantsearch/ajax/result");
    }
    /**
     *
     * @return string
     */
    public function getTextNoRsult()
    {
        return 'No Result';
    }

    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        $this->jsLayout['components']['instant_search_form']['config']['textNoResult'] = $this->getTextNoRsult();
        $this->jsLayout['components']['instant_search_form']['children']['steps']['children']
            ['product']['sortOrder'] = $this->_inSearchHelper->getConfig(self::XML_PATH_PRODUCT_SORT_ORDER) ? 
            $this->_inSearchHelper->getConfig(self::XML_PATH_PRODUCT_SORT_ORDER) : 0;
        $this->jsLayout['components']['instant_search_form']['children']['steps']['children']
            ['category']['sortOrder'] = $this->_inSearchHelper->getConfig(self::XML_PATH_CATEGORY_SORT_ORDER) ?
            $this->_inSearchHelper->getConfig(self::XML_PATH_CATEGORY_SORT_ORDER) : 0;
        $this->jsLayout['components']['instant_search_form']['children']['steps']['children']
            ['page']['sortOrder'] = $this->_inSearchHelper->getConfig(self::XML_PATH_CMS_PAGE_SORT_ORDER) ?
            $this->_inSearchHelper->getConfig(self::XML_PATH_CMS_PAGE_SORT_ORDER) : 0;
        $this->jsLayout['components']['instant_search_form']['children']['steps']['children']
        ['blog']['sortOrder'] = $this->_inSearchHelper->getConfig(self::XML_PATH_BLOG_SORT_ORDER) ?
        $this->_inSearchHelper->getConfig(self::XML_PATH_BLOG_SORT_ORDER) : 0;
        $this->jsLayout['components']['autocompleteDataProvider']['config']['url'] = $this->getSearchUrl();
        $this->jsLayout['components']['autocompleteBindEvents']['config']['url'] = $this->getUrl("instantsearch/result");
        return \Zend_Json::encode($this->jsLayout);
    }

    public function getInstantSearchConfig()
    {
        $responseData = [];
        $responseData['result']['product'] = array('data'=>[],'size'=>0, 'url'=>'');
        $responseData['result']['category'] = array('data'=>[],'size'=>0, 'url'=>'');
        $responseData['result']['page'] = array('data'=>[],'size'=>0, 'url'=>'');
        $responseData['result']['blog'] = array('data'=>[],'size'=>0, 'url'=>'');
        return \Zend_Json::encode($responseData);
    }
}
