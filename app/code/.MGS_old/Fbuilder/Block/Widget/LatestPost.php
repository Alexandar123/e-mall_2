<?php

namespace MGS\Fbuilder\Block\Widget;

use Magento\Framework\View\Element\Template;

class LatestPost extends Template
{
    protected $_post;
    protected $_storeManager;

    public function __construct(
        Template\Context $context,
        \MGS\Blog\Model\Post $post,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_post = $post;
        $this->_storeManager = $context->getStoreManager();
    }

    public function _toHtml()
    {
        $template = $this->getConfig('template');
        $this->setTemplate($template);
        return parent::_toHtml();
    }

    public function getPostCollection()
    {
        $post = $this->_post;
        $postCollection = $post->getCollection()
            ->distinct(true)
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('created_at', 'DESC');
        if ($this->getConfig('post_category')) {
            $postCollection =  $postCollection->addCategoryFilter($this->getConfig('post_category'));
        }

        $postCollection->getSelect()->limit($this->getConfig('limit'));
        return $postCollection;
    }

    public function getConfig($key, $default = '')
    {
        if ($this->hasData($key)) {
            return $this->getData($key);
        }
        return $default;
    }
}
