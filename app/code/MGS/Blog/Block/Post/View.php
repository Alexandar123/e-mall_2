<?php

namespace MGS\Blog\Block\Post;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\RequestInterface;

class View extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_blogHelper;
    protected $_post;
    protected $_category;
    protected $httpContext;
    protected $request;
    protected $resource;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Post $post,
        \MGS\Blog\Model\Category $category,
        \Magento\Framework\App\Http\Context $httpContext,
        \MGS\Blog\Model\Resource\Category $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		 array $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->resource = $resource;
        $this->_post = $post;
        $this->_category = $category;
        $this->_coreRegistry = $registry;
        $this->request = $context->getRequest();
        $this->_blogHelper = $blogHelper;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enabled')) return;
        parent::_construct();
    }

    public function getCacheKeyInfo()
    {
        return [
            'BLOG_POST_VIEW',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP),
            'template' => $this->getTemplate()
        ];
    }

    protected function _addBreadcrumbs()
    {
        $category = $this->getParentCategory();
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $pageTitle = $this->_blogHelper->getConfig('general_settings/title');
        $post = $this->getCurrentPost();
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
        if ($category != false) {
            $breadcrumbsBlock->addCrumb(
                'category',
                [
                    'label' => $category->getTitle(),
                    'title' => $category->getTitle(),
                    'link' => $category->getCategoryUrl()
                ]
            );
        }
        $breadcrumbsBlock->addCrumb(
            'post',
            [
                'label' => $post->getTitle(),
                'title' => $post->getTitle(),
                'link' => ''
            ]
        );
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_blogHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    public function getCurrentPost()
    {
        $post = $this->_coreRegistry->registry('current_post');
        if ($post) {
            $id= $post->getPostId();
            $store_id = $this->storeManager->getStore()->getId();
            $update = $this->getPostByStore($store_id, $id);
            foreach($update as $key => $value) {
                $post[$key] = $value['value'];
            }
            $this->setData('current_post', $post);
        }
        return $post;
    }

    public function getPostByStore($store, $post_id) {
        $table = $this->resource->getTable('mgs_blog_post_update');
        $connection = $this->resource->getConnection();
        $sql = "SELECT `field`, `value` 
                 FROM `$table` 
                 WHERE `scope_id`= $store
                 AND `post_id`= $post_id ";
        $category = $connection->fetchAssoc($sql);
        return $category;
    }

    protected function _prepareLayout()
    {
        $post = $this->getCurrentPost();
        $pageTitle = $post->getTitle();
        $metaKeywords = $post->getMetaKeywords();
        $metaDescription = $post->getMetaDescription();
        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('blog-post-view');
        if ($pageTitle) {
            $this->pageConfig->getTitle()->set($pageTitle);
        }
        if ($metaKeywords) {
            $this->pageConfig->setKeywords($metaKeywords);
        }
        if ($metaDescription) {
            $this->pageConfig->setDescription($metaDescription);
        }
        $this->pageConfig->addRemotePageAsset(
            $this->getPostUrl(),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );
        return parent::_prepareLayout();
    }

    public function getParentCategory()
    {
        $urlKey = trim($this->request->getPathInfo(), '/');
        $identifiers = explode('/', $urlKey);
        if (count($identifiers) == 3) {
            $identifier = $identifiers[1];
            $category = $this->_category->getCollection()
                ->addFieldToFilter('status', array('eq' => 1))
                ->addFieldToFilter('url_key', array('eq' => $identifier))
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->getFirstItem();
            if ($category && $category->getId() && (in_array($this->_storeManager->getStore()->getId(), $category->getStoreId()) || in_array(0, $category->getStoreId()))) {
                return $category;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getPostUrl()
    {
        $category = $this->getParentCategory();
        if ($category != false) {
            return $this->getCurrentPost()->getPostUrlWithCategory($category->getId());
        } else {
            return $this->getCurrentPost()->getPostUrlWithNoCategory();
        }
    }
	
	
	public function getAllPost() {
		$post = $this->_post;
        $postCollection = $post->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('created_at', $this->getConfig('general_settings/default_sort'));
			
		return $postCollection;
	}
	
	
	public function getAllPostId(){
        $postCollection = $this->getAllPost();
		$arrResult = [];
		if(count($postCollection)>0){
			foreach($postCollection as $item){
				$arrResult[] = ['id'=>$item->getId(), 'value'=>$item->getUrlKey(), 'name'=>$item->getTitle()];
			}
		}
		return $arrResult;
	}
	
	public function getUrlPostById($id,$value,$name){

		$arrResult = [];
		$route = $this->_blogHelper->getRoute();
		$route = $route . '/' . $value;
		$arrResult = ['id'=>$id,'url'=>$route, 'name'=>$name];
		return $arrResult;
	}
	
	public function getNextPost($id){
		$arrId = $this->getAllPostId();
		if(is_array($arrId)){
			$maxKey = count($arrId) - 1;
			$key = array_search($id, array_column($arrId, 'id'));
			if($key == $maxKey){
				$nextKey = 0;
			}else {
				$nextKey = $key + 1;
			}
			$idNext = $arrId[$nextKey]['id'];
			$valueNext = $arrId[$nextKey]['value'];
			$nameNext = $arrId[$nextKey]['name'];
			
			return $this->getUrlPostById($idNext,$valueNext,$nameNext);
		}
		return;
	}
	
	public function getPrevPost($id){
		$arrId = $this->getAllPostId();
		if(is_array($arrId)){
			$maxKey = count($arrId) - 1;
			$key = array_search($id, array_column($arrId, 'id'));
			if($key == 0){
				$nextKey = $maxKey;
			}else {
				$nextKey = $key - 1;
			}
			$idNext = $arrId[$nextKey]['id'];
			$valueNext = $arrId[$nextKey]['value'];
			$nameNext = $arrId[$nextKey]['name'];
			
			return $this->getUrlPostById($idNext,$valueNext,$nameNext);
		}
		return;
	}
}
