<?php

namespace MGS\Blog\Block\Category;

use Magento\Customer\Model\Context as CustomerContext;
use MGS\Blog\Model\Resource\Post as PostResource;

class Posts extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_blogHelper;
    protected $_post;
    protected $httpContext;
    protected $resource;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Post $post,
        \Magento\Framework\App\Http\Context $httpContext, 
        PostResource $resource,
		array $data = []
    )
    {
        $this->resource= $resource;
        $this->_post = $post;
        $this->_coreRegistry = $registry;
        $this->_blogHelper = $blogHelper;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enabled')) return;
        parent::_construct();
        $category = $this->getCurrentCategory();
        $post = $this->_post;
        $postCollection = $post->getCollection()
            ->addFieldToFilter('status', 1)
            ->addCategoryFilter($category->getCategoryId())
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('created_at', $this->getConfig('general_settings/default_sort'));
        $this->setCollection($postCollection);
    }

    public function getCacheKeyInfo()
    {
        return [
            'BLOG_POST_LIST',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP),
            'template' => $this->getTemplate()
        ];
    }

    protected function _addBreadcrumbs()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $pageTitle = $this->_blogHelper->getConfig('general_settings/title');
        $category = $this->getCurrentCategory();
        $breadcrumbsBlock->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        $breadcrumbsBlock->addCrumb(
            'blog',
            [
                'label' => $pageTitle,
                'title' => $pageTitle,
                'link' => $this->_blogHelper->getRoute()
            ]
        );
        $breadcrumbsBlock->addCrumb(
            'category',
            [
                'label' => $category->getTitle(),
                'title' => $category->getTitle(),
                'link' => ''
            ]
        );
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_collection;
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_blogHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    public function getCurrentCategory()
    {
        $category = $this->_coreRegistry->registry('current_blogcategory');
        if ($category) {
            $this->setData('current_blogcategory', $category);
        }
        return $category;
    }

    public function getPostByStore($store, $post_id) {
        $table = $this->resource->getTable('mgs_blog_post_update');
        $connection = $this->resource->getConnection();
        $sql = "SELECT `field`, `value` 
                 FROM `$table` 
                 WHERE `scope_id`= $store
                 AND `post_id`= $post_id ";
        $post = $connection->fetchAssoc($sql);
        return $post;
    }

    protected function _prepareLayout()
    {
        $category = $this->getCurrentCategory();
        $pageTitle = $category->getTitle();
        $metaKeywords = $category->getMetaKeywords();
        $metaDescription = $category->getMetaDescription();
        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('blog-post-list');
        if ($pageTitle) {
            $this->pageConfig->getTitle()->set($pageTitle);
        }
        if ($metaKeywords) {
            $this->pageConfig->setKeywords($metaKeywords);
        }
        if ($metaDescription) {
            $this->pageConfig->setDescription($metaDescription);
        }
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'blog.post.list.pager'
            );
            $pager->setLimit($this->getConfig('general_settings/posts_per_page'))->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
        }
        $this->pageConfig->addRemotePageAsset(
            $category->getUrlKey(),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );
        return parent::_prepareLayout();
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
