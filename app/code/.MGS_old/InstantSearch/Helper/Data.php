<?php
namespace MGS\InstantSearch\Helper;
use Magento\Search\Model\QueryFactory;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLE = 'instantsearch/general/enabled';
    const XML_PATH_RESULT = 'instantsearch/general/result';
    const XML_PATH_NUMBER_RESULT = 'instantsearch/general/number_results';
    const XML_PATH_VIEW_MORE_LABEL = 'instantsearch/general/view_more_label';

    const XML_PATH_PRODUCT_ACTIVE = 'instantsearch/additional_product/actived';
    const XML_PATH_PRODUCT_SHOW_SHORT_DESCRIPTION = 'instantsearch/additional_product/show_short_description';
    const XML_PATH_PRODUCT_SHOW_REVIEW = 'instantsearch/additional_product/show_review';
    const XML_PATH_PRODUCT_SORT_ORDER = 'instantsearch/additional_product/sort_order';

    const XML_PATH_CATEGORY_ACTIVE = 'instantsearch/additional_category/actived';
    const XML_PATH_CATEGORY_FIELDS = 'instantsearch/additional_category/category_fields';
    const XML_PATH_CATEGORY_SORT_ORDER = 'instantsearch/additional_category/sort_order';

    const XML_PATH_CMS_PAGE_ACTIVE = 'instantsearch/additional_cms_page/actived';
    const XML_PATH_CMS_PAGE_FIELDS = 'instantsearch/additional_cms_page/cms_page_fields';
    const XML_PATH_CMS_PAGE_SORT_ORDER = 'instantsearch/additional_cms_page/sort_order';

    const XML_PATH_BLOG_ACTIVE = 'instantsearch/additional_blog/actived';
    const XML_PATH_BLOG_SORT_ORDER = 'instantsearch/additional_blog/sort_order';

    const VIEW_TYPE_CATEGORY = 'category';
    const VIEW_TYPE_CMS_PAGE = 'cms_page';
    const VIEW_TYPE_BLOG = 'blog';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    private $limit = 5;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Default limits per page
     *
     * @var array
     */
    protected $_defaultAvailableLimit  = [10 => 10,20 => 20,50 => 50];

    /**
     * [__construct description]
     * @param \Magento\Framework\App\Helper\Context $context[description]
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ){
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_moduleManager = $context->getModuleManager();
        $this->_assetRepo = $assetRepo;
    }

    /**
     * Whether a module is enabled in the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->_moduleManager->isEnabled($moduleName);
    }

    /**
     * Whether a module output is permitted by the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    public function isOutputEnabled($moduleName)
    {
        return $this->_moduleManager->isOutputEnabled($moduleName);
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /*
     * return enable / disable module with magento path
     * @return string
     */
    public function isEnableFrontend()
    {
        return $this->getConfig(self::XML_PATH_ENABLE);

    }

    /*
     * return number result
     * @return string
     */
    public function getNumberResult()
    {
        $limit = $this->getConfig(self::XML_PATH_NUMBER_RESULT) ? $this->getConfig(self::XML_PATH_NUMBER_RESULT) : $this->limit;
        return $limit;

    }

    /*
     * return number result
     * @return string
     */
    public function getSearchResultsPage()
    {
        return $this->getConfig(self::XML_PATH_RESULT);
    }

    /**
     * Retrieve suggest url
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        return $this->_getUrl(
            'instantsearch/ajax/result',
            ['_secure' => $this->_getRequest()->isSecure()]
        );
    }

    /**
     * Retrieve result page url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @param   string $query
     * @return  string
     */
    public function getResultUrlForm($query = null)
    {
        return $this->_getUrl(
            'instantsearch/result',
            ['_query' => [QueryFactory::QUERY_VAR_NAME => $query], '_secure' => $this->_request->isSecure()]
        );
    }

    /*
     * return number result
     * @return string
     */
    public function getViewMoreLabel()
    {
        return $this->getConfig(self::XML_PATH_VIEW_MORE_LABEL);
    }

    /*
     *
     * show product search result
     * @return string
     */
    public function isProductSearch()
    {
        return $this->getConfig(self::XML_PATH_PRODUCT_ACTIVE);

    }

    /*
     *
     * show short description product search result
     * @return string
     */
    public function showShortDescriptionProductSearch()
    {
        return $this->getConfig(self::XML_PATH_PRODUCT_SHOW_SHORT_DESCRIPTION);

    }

    /*
     *
     * show review product search result
     * @return string
     */
    public function showReviewProductSearch()
    {
        return $this->getConfig(self::XML_PATH_PRODUCT_SHOW_REVIEW);
    }

    /*
     *
     * show category search result
     * @return string
     */
    public function isCategorySearch()
    {
        return $this->getConfig(self::XML_PATH_CATEGORY_ACTIVE);

    }

    /*
     *
     * category search result by fields
     * @return string
     */
    public function categorySearchByFields()
    {
        return $this->getConfig(self::XML_PATH_CATEGORY_FIELDS);

    }
    /*
     *
     * show cms page search result
     * @return string
     */
    public function isCmsPageSearch()
    {
        return $this->getConfig(self::XML_PATH_CMS_PAGE_ACTIVE);

    }

    /*
     *
     * category search result by fields
     * @return string
     */
    public function cmsPageSearchByFields()
    {
        return $this->getConfig(self::XML_PATH_CMS_PAGE_FIELDS);

    }

    /*
     *
     * show blog search result
     * @return string
     */
    public function isBlogSearch()
    {
        return $this->getConfig(self::XML_PATH_BLOG_ACTIVE);

    }

    /*
     *
     * @return array
     */
    public function getSearchType()
    {
        $pos = array();

        $order = $this->getConfig(self::XML_PATH_BLOG_SORT_ORDER) ? $this->getConfig(self::XML_PATH_BLOG_SORT_ORDER) : 0;
        $pos[] = array('type'=>'blog', 'sort_order'=> $order);

        $order = $this->getConfig(self::XML_PATH_CMS_PAGE_SORT_ORDER) ? $this->getConfig(self::XML_PATH_CMS_PAGE_SORT_ORDER) : 0;
        $pos[] = array('type'=>'page', 'sort_order'=> $order);

        $order = $this->getConfig(self::XML_PATH_CATEGORY_SORT_ORDER) ? $this->getConfig(self::XML_PATH_CATEGORY_SORT_ORDER) : 0;
        $pos[] = array('type'=>'category', 'sort_order'=> $order);

        $order = $this->getConfig(self::XML_PATH_PRODUCT_SORT_ORDER) ? $this->getConfig(self::XML_PATH_PRODUCT_SORT_ORDER) : 0;
        $pos[] = array('type'=>'product', 'sort_order'=> $order);

        usort($pos, function($a, $b) {
            return $a['sort_order']-$b['sort_order'];
        });
        return $pos;

    }

    /**
     * Retrieve available limits for specified view mode
     *
     * @param string $type
     * @return array
     */
    public function getAvailableLimit($type)
    {
        if (!in_array($type, [self::VIEW_TYPE_CATEGORY, self::VIEW_TYPE_CMS_PAGE, self::VIEW_TYPE_BLOG])) {
            return $this->_defaultAvailableLimit;
        }
        $perPageConfigKey = 'instantsearch/additional_' . $type . '/per_page_values';
        $perPageValues = (string)$this->getConfig($perPageConfigKey);
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if ($this->getConfig('instantsearch/additional_' . $type . '/list_allow_all')) {
            return ($perPageValues + ['all' => __('All')]);
        } else {
            return $perPageValues;
        }
    }

    /**
     * Retrieve default per page values
     *
     * @param string $viewType
     * @return string (comma separated)
     */
    public function getDefaultLimitPerPageValue($viewType)
    {
        if ($viewType) {
            return $this->getConfig('instantsearch/additional_'. $viewType .'/per_page');
        }
        return 0;
    }

    /*
     * return message with magento path
     * @return string
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
    }

    /*
     * @return string
     */
    public function getUrl($path)
    {
        return $this->_storeManager->getStore()
           ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK) . $path;
    }

    /**
     * Retrieve result page url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @param   string $query
     * @return  string
     */
    public function getResultUrl($url, $query = null)
    {
        return $this->_getUrl(
            $url,
            ['_query' => [QueryFactory::QUERY_VAR_NAME => $query], '_secure' => $this->_request->isSecure()]
        );
    }

	/**
     * @param \MGS\Blog\Model\Post $_post
     * @return string
     */
    public function getImageThumbnailPost($_post, $isList = false){
        $mediaUrl = $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ).'mgs_blog/';
        if($_post->getThumbnail()){
            $imageUrl = $mediaUrl . $_post->getThumbnail();
            return $imageUrl;
        }
        if($isList){
            return $this->_assetRepo->getUrl("MGS_InstantSearch::images/posts/placeholder/image.jpg");
        }
        return $this->_assetRepo->getUrl("MGS_InstantSearch::images/posts/placeholder/small_image.jpg");
    }
}
