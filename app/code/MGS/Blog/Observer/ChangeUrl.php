<?php

namespace MGS\Blog\Observer;

use Magento\Framework\Event\ObserverInterface;

class ChangeUrl implements ObserverInterface
{
    protected $redirect;
    protected $actionFlag;
    protected $resource;
    protected $request;
    protected $store;
    protected $storeManeger;
    protected $resourcePost;
    protected $dataHelper;
    protected $url;
    protected $resourceCategory;

    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \MGS\Blog\Model\Post $resource,
        \MGS\Blog\Model\Category $resourceCategory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManeger,
        \MGS\Blog\Model\Resource\Post $resourcePost,
        \Magento\Framework\App\RequestInterface $store,
        \Magento\Store\Model\Store $url,
        \MGS\Blog\Helper\Data $dataHelper

    ) {
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->resource = $resource;
        $this->request = $request;
        $this->resourcePost = $resourcePost;
        $this->storeManeger = $storeManeger;
        $this->store= $store;
        $this->dataHelper = $dataHelper;
        $this->url= $url;
        $this->resourceCategory = $resourceCategory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
     {
        $postId = (int)$this->request->getParam('post_id', false);
        $categoryId = (int)$this->request->getParam('category_id', false);
        if ($postId ==0 && $categoryId ==0) {
            return $this;
        }
        $urlBase = $this->url->getBaseUrl();
        $urlCurrent = $this->url->getCurrentUrl();
        $temp= substr($urlCurrent, strlen($urlBase));
        $urll = explode('/', $temp);
        $count = count($urll);
        $urlKey = explode('?', $urll[$count-1]);
        $store = $this->storeManeger->getStore()->getId();

        if ($postId !=0) {

            $post = $this->resource->load($postId);
            $urlStore = $this->getUrlStore($postId, $store);
            $url = $post->getUrlKey();
        }

        if($categoryId != 0 ){
            $category = $this->resourceCategory->load($categoryId);
            $urlStore = $this->getUrlCatgoryStore($categoryId, $store);
            $url = $category->getUrlKey();
        }
        if ($urlStore == null) {
            $urlStore = $url;
        }
        if ($urlKey[0] == $urlStore) {
            return $this;
        }
        $observer->getControllerAction()->getResponse()->setRedirect($urlStore);
    }

    public function getUrlStore($post_id, $store)
    {
        $connection = $this->resourcePost->getConnection();
        $select = $connection->select()->from(
            $this->resourcePost->getTable('mgs_blog_post_update'),
            'value',
        )->where(
            'post_id = ?',
            (int)$post_id
        )->where(
            'scope_id = ?',
            $store
        )->where(
            'field = ?',
            'url_key'
        );
        return $connection->fetchOne($select);
    }
    public function getUrlCatgoryStore($category_id, $store)
    {
        $connection = $this->resourcePost->getConnection();
        $select = $connection->select()->from(
            $this->resourcePost->getTable('mgs_blog_category_update'),
            'value',
        )->where(
            'category_id = ?',
            (int)$category_id
        )->where(
            'scope_id = ?',
            $store
        )->where(
            'field = ?',
            'url_key'
        );
        return $connection->fetchOne($select);
    }
}
