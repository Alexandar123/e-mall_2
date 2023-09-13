<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Fbuilder\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Cache\Manager as CacheManager;

class Copyblock extends \Magento\Framework\App\Action\Action
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CustomerSession $customerSession,
        CacheManager $cacheManager
    ) {
        $this->customerSession = $customerSession;
        $this->cacheManager = $cacheManager;

        parent::__construct($context);
    }
    
    public function execute()
    {
        if ($this->getRequest()->isAjax() && ($this->customerSession->getUseFrontendBuilder() == 1)) {
            if ($blockId = $this->getRequest()->getParam('block_id')) {
                $this->customerSession->setBlockCopied($blockId);
                $this->cacheManager->clean(['full_page']);
                return $this->getResponse()->setBody($blockId);
            }
            return;
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
