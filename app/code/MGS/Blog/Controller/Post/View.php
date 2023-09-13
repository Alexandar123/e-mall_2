<?php

namespace MGS\Blog\Controller\Post;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class View extends \Magento\Framework\App\Action\Action
{
    protected $blogHelper;
    protected $resultForwardFactory;
    protected $_postModel;
    protected $_coreRegistry = null;
    /**
     * @var DateTime
     */
    protected $date;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Post $postModel,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        DateTime $date
    )
    {
        $this->blogHelper = $blogHelper;
        $this->_postModel = $postModel;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->date = $date;
        parent::__construct($context);
    }

    public function _initPost()
    {
        $postId = (int)$this->getRequest()->getParam('post_id', false);
        if (!$postId) {
            return false;
        }
        try {
            $post = $this->_postModel->load($postId);
        } catch (\Exception $e) {
            return false;
        }
        $this->_coreRegistry->register('current_post', $post);
        return $post;
    }

    public function execute()
    {
        if (!$this->blogHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $post = $this->_initPost();
        if ($post) {
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            if ($this->blogHelper->getConfig('general_settings/template')) {
                $resultPage->getConfig()->setPageLayout($this->blogHelper->getConfig('general_settings/template'));
            }
            return $resultPage;
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $data['publish_date'] = !empty($data['publish_date']) ? $data['publish_date'] : $this->date->date();
    }
}
