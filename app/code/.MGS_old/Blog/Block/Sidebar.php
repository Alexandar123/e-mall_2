<?php

namespace MGS\Blog\Block;

use Magento\Customer\Model\Context as CustomerContext;

class Sidebar extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_blogHelper;
    protected $_post;
    protected $_category;
    protected $httpContext;
    protected $resource;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Post $post,
        \MGS\Blog\Model\Category $category,
        \Magento\Framework\App\Http\Context $httpContext,
        \MGS\Blog\Model\Resource\Category $resource,
		array $data = []
    )
    {
        $this->resource = $resource;
        $this->_category = $category;
        $this->_post = $post;
        $this->_coreRegistry = $registry;
        $this->_blogHelper = $blogHelper;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enabled')) return;
        if (!$this->getConfig('sidebar_settings/enabled')) return;
        parent::_construct();
        $post = $this->_post;
        $postCollection = $post->getCollection()
            ->addFieldToFilter('status', 1);
        $postCollection->getSelect()->limit($this->getConfig('sidebar_settings/number_of_recent_posts'));
        $postCollection->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('created_at', 'DESC');
        $this->setCollection($postCollection);
    }

    public function getCacheKeyInfo()
    {
        return [
            'BLOG_POSTS_SIDEBAR',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP),
            'template' => $this->getTemplate()
        ];
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection()
    {
        $post= $this->_collection ;
        $post->addFieldToFilter('status', 1);
        foreach ($post  as $items) {
            $temp = $items->getPostId();
            $update = $this->getPostByStore($this->_storeManager->getStore()->getId(), $temp);
            foreach ($update as $key =>$value) {
                $items[$key] = $value['value'];
            }
        }
        return $post;
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_blogHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCategories()
    {
        $category = $this->_category;
        $categoryCollection = $category->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('sort_order', 'ASC');
        foreach($categoryCollection  as $items) {
            $temp = $items->getCategoryId();
            $update = $this->getCategoryByStore($this->_storeManager->getStore()->getId(),$temp);
            foreach($update as $key =>$value) {
             $items[$key] = $value['value'];
            }
        }
        return $categoryCollection;
    }

    public function getTags()
    {
        $postCollection = $this->_post->getCollection()
            ->addFieldToFilter('status', 1);
        $postCollection->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('created_at', 'DESC');
        $tags = [];
        foreach ($postCollection as $post) {
            $postTags = explode(',', $post->getTags());
            foreach ($postTags as $tag) {
                if ($tag == null || $tag == '') continue;
                $tags[] = trim($tag);
            }
        }
        return array_count_values($tags);
    }

    public function getCategoryByStore($store, $category_id) {
        $table = $this->resource->getTable('mgs_blog_category_update');
        $connection = $this->resource->getConnection();
        $sql = "SELECT `field`, `value` 
                 FROM `$table` 
                 WHERE `scope_id`= $store
                 AND `category_id`= $category_id ";
        $category = $connection->fetchAssoc($sql);
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
    
}
