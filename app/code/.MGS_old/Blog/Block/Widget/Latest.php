<?php

namespace MGS\Blog\Block\Widget;

class Latest extends AbstractWidget
{
    protected $_post;
    protected $_coreRegistry = null;
    protected $_blogHelper;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Post $post,
        array $data = []
    )
    {
        $this->_post = $post;
        $this->_blogHelper = $blogHelper;
        $this->_coreRegistry = $registry;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $blogHelper);
    }

    public function _toHtml()
    {
        if (!$this->_blogHelper->getConfig('general_settings/enabled')) return;
        $template = $this->getConfig('template');
        $this->setTemplate($template);
        return parent::_toHtml();
    }

    public function getPostCollection()
    {
        $post = $this->_post;
        $postCollection = $post->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('created_at', 'DESC');
		if($this->getConfig('post_category')){
			$postCollection =  $postCollection->addCategoryFilter($this->getConfig('post_category'));
		}
		
        $postCollection->getSelect()->limit($this->getConfig('number_of_posts'));
        return $postCollection;
    }
}
