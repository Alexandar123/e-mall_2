<?php

namespace MGS\Blog\Block\Post\View;

use Magento\Customer\Model\Context as CustomerContext;

class Commentlist extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_blogHelper;
    protected $_post;
    protected $_category;
    protected $httpContext;
    protected $request;
    protected $_comment;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Post $post,
        \MGS\Blog\Model\Category $category,
        \MGS\Blog\Model\Comment $comment,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    )
    {
        $this->_post = $post;
        $this->_comment = $comment;
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
        $comment = $this->_comment;
        $commentCollection = $comment->getCollection()
            ->addFieldToFilter('post_id', $this->getCurrentPost()->getId())
            ->addFieldToFilter('status', 1)
            ->setOrder('created_at', 'DESC');
        $this->setCollection($commentCollection);
    }

    public function getCacheKeyInfo()
    {
        return [
            'BLOG_POST_VIEW_COMMENT_LIST',
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

    public function getCurrentPost()
    {
        $post = $this->_coreRegistry->registry('current_post');
        if ($post) {
            $this->setData('current_post', $post);
        }
        return $post;
    }

    protected function _prepareLayout()
    {
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'blog.post.comment.list.pager'
            );
            $pager->setLimit($this->getConfig('comment_settings/comments_per_page'))->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
        }
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

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
