<?php

namespace MGS\Brand\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_brandHelper;
    protected $resultForwardFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \MGS\Brand\Helper\Data $brandHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    )
    {
        $this->_brandHelper = $brandHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_brandHelper->getConfig('general_settings/enabled')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($this->_brandHelper->getConfig('list_page_settings/template')) {
            $resultPage->getConfig()->setPageLayout($this->_brandHelper->getConfig('list_page_settings/template'));
        }
        return $resultPage;
    }
}