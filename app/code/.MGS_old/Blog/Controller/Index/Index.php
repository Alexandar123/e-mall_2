<?php

namespace MGS\Blog\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $blogHelper;
    protected $resultForwardFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \MGS\Blog\Helper\Data $blogHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    )
    {
        $this->blogHelper = $blogHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->blogHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($this->blogHelper->getConfig('general_settings/template')) {
            $resultPage->getConfig()->setPageLayout($this->blogHelper->getConfig('general_settings/template'));
        }
        return $resultPage;
    }
}
