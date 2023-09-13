<?php

namespace MGS\Blog\Controller\Category;

use Magento\Framework\Controller\ResultFactory;

class View extends \Magento\Framework\App\Action\Action
{
    protected $blogHelper;
    protected $resultForwardFactory;
    protected $_categoryModel;
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Category $categoryModel,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    )
    {
        $this->blogHelper = $blogHelper;
        $this->_categoryModel = $categoryModel;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('category_id', false);
        if (!$categoryId) {
            return false;
        }
        try {
            $category = $this->_categoryModel->load($categoryId);
        } catch (\Exception $e) {
            return false;
        }
        $this->_coreRegistry->register('current_blogcategory', $category);
        return $category;
    }

    public function execute()
    {
        if (!$this->blogHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $category = $this->_initCategory();
        if ($category) {
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            if ($this->blogHelper->getConfig('general_settings/template')) {
                $resultPage->getConfig()->setPageLayout($this->blogHelper->getConfig('general_settings/template'));
            }
            return $resultPage;
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}
