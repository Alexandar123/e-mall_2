<?php

namespace MGS\Blog\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url;
use MGS\Blog\Model\Post;
use MGS\Blog\Model\Category;
use MGS\Blog\Helper\Data;

class Router implements RouterInterface
{
    protected $actionFactory;
    protected $eventManager;
    protected $response;
    protected $dispatched;
    protected $postCollection;
    protected $categoryCollection;
    protected $blogHelper;
    protected $storeManager;
    protected $resource;


    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        ManagerInterface $eventManager,
        Category $categoryCollection,
        Post $postCollection,
        Data $blogHelper,
        StoreManagerInterface $storeManager,
        \MGS\Blog\Model\Resource\Category $resource
       
    )
    {
      
        $this->resource = $resource;
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->response = $response;
        $this->blogHelper = $blogHelper;
        $this->categoryCollection = $categoryCollection;
        $this->postCollection = $postCollection;
        $this->storeManager = $storeManager;
    }

    public function match(RequestInterface $request)
    {
        $blogHelper = $this->blogHelper;
        if (!$this->dispatched) {
            $route = $blogHelper->getConfig('general_settings/route');
            $urlKey = trim($request->getPathInfo(), '/');

            $origUrlKey = $urlKey;
            $condition = new DataObject(['url_key' => $urlKey, 'continue' => true]);
            $this->eventManager->dispatch(
                'mgs_blog_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );
            $urlKey = $condition->getUrlKey();
            
           
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
          
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                );
            }
            if (!$condition->getContinue()) {
                return null;
            }
            if ($urlKey == $route) {
                $request->setModuleName('blog')
                    ->setControllerName('index')
                    ->setActionName('index');
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                );
            }
            $identifiers = explode('/', $urlKey);
            if (count($identifiers) == 2) {
                $identifier = $identifiers[1];
                $tempId = '';
                $temp = $this->searchUrl($identifier);
                if($temp != NULL ) {
                    $category = $this->categoryCollection->getCollection();
                    foreach($category as $items ) {
                        $update = $this->getchUrl($temp,$this->storeManager->getStore()->getId());
                        foreach ($update as $key =>$value) {
                            $items[$key] = $value['value'];
                        }
                    }
                    $category  = $category->getFirstItem();
                    $tempId = $temp;
                }
         
                else {
                    $category = $this->categoryCollection->getCollection()
                                ->addFieldToFilter('status', array('eq' => 1))
                                ->addFieldToFilter('url_key', array('eq' => $identifier))
                                ->addStoreFilter($this->storeManager->getStore()->getId())
                                ->getFirstItem();
                    $tempId = $category->getCategoryId();
                }
                
                if ($category && $category->getCategoryId()) {
                    $request->setModuleName('blog')
                        ->setControllerName('category')
                        ->setActionName('view')
                        ->setParam('category_id',  $tempId);
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                    );
                }
                $idpost = '';
                $isEnable = false;
                $temp = $this->searchUrlPost($identifier,$this->storeManager->getStore()->getId());
                if($temp != NULL ) {
                    $post = $this->postCollection->getCollection()
                                   ->addFieldToFilter('status', array('eq' => 1))
                                   ->addStoreFilter($this->storeManager->getStore()->getId());
                                  
                    foreach($post as $items) {
                        
                         if($items->getPostId() == $temp) {
                            $idpost = $temp;
                            $isEnable = true;
                            break;
                        }
                    }
                }

                else {
                    $post = $this->postCollection->getCollection()
                        ->addFieldToFilter('status', array('eq' => 1))
                        ->addFieldToFilter('url_key', array('eq' => $identifier))
                        ->addStoreFilter($this->storeManager->getStore()->getId())
                        ->getFirstItem();
                    $idpost = $post->getId();
                    if (($post && $post->getId())) {
                        $isEnable = true;
                    } 
                }
                if ($isEnable == true) {
                    $request->setModuleName('blog')
                        ->setControllerName('post')
                        ->setActionName('view')
                        ->setParam('post_id', $idpost);
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                    );
                }
            }

            if (count($identifiers) == 3) {
                $identifier = $identifiers[2];
                $post = $this->postCollection->getCollection()
                    ->addFieldToFilter('status', array('eq' => 1))
                    ->addFieldToFilter('url_key', array('eq' => $identifier))
                    ->addStoreFilter($this->storeManager->getStore()->getId())
                    ->getFirstItem();
                if ($post && $post->getId()) {
                    $request->setModuleName('blog')
                        ->setControllerName('post')
                        ->setActionName('view')
                        ->setParam('post_id', $post->getId());
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                    );
                }
            }

            $identifier = substr_replace($request->getPathInfo(), '', 0, strlen('/' . $route . '/'));
            if (substr($identifier, 0, strlen('tag/')) == 'tag/') {
                $identifier = substr_replace($identifier, '', 0, 4);
                if ($identifier != null || $identifier != '') {
                    $request->setModuleName('blog')
                        ->setControllerName('tag')
                        ->setActionName('view')
                        ->setParam('tag', urldecode($identifier));
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                    );
                }
            }
        }
    }

    public function searchUrl($url) {
        $table = $this->resource->getTable('mgs_blog_category_update');
        $connection = $this->resource->getConnection();
        $sql = "SELECT `category_id` 
                 FROM `$table` 
                 WHERE `field`= 'url_key'
                 AND `value` = '$url'" ;
        $post = $connection->fetchOne($sql);
        return $post;
    } 
   
    public function getchUrl($store, $category_id) {
        $table = $this->resource->getTable('mgs_blog_category_update');
        $connection = $this->resource->getConnection();
        $sql = "SELECT `field`, `value` 
                 FROM `$table` 
                 WHERE `scope_id`= $store
                 AND `category_id`= $category_id ";
        $post = $connection->fetchAssoc($sql);
        return $post;
    }
    public function searchUrlPost($url,$store_id) {
        $table = $this->resource->getTable('mgs_blog_post_update');
        $connection = $this->resource->getConnection();
        $sql = "SELECT `post_id` 
                 FROM `$table` 
                 WHERE `field`= 'url_key'
                 AND `value` = '$url'" ;
        $post = $connection->fetchOne($sql);
        return $post;
    } 

    public function getchUrlPost($store, $post_id) {
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
