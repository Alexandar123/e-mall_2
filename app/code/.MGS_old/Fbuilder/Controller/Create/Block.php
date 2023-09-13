<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Fbuilder\Controller\Create;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;
use MGS\Fbuilder\Helper\Generate as BuilderGenerate;

class Block extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        BuilderGenerate $_generateHelper,
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
        $this->_generateHelper = $_generateHelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        if ($this->customerSession->getUseFrontendBuilder() == 1) {
            $this->_view->loadLayout();
            $layout = $this->_view->getLayout();
            if ($this->_generateHelper->getCurrentTheme() == 'Infortis/base') {
                $infortisBlock = $layout->getBlock('base-header-container');
                $infortisBlocks = $layout->getBlock('base-footer-container');
                if ($infortisBlock && $infortisBlocks) {
                    $layout->unsetElement('base-header-container');
                    $layout->unsetElement('base-footer-container');
                }
            }
            if ($this->_generateHelper->getCurrentTheme() == 'Sm/market') {
                $smsBlock = $layout->getBlock('header.content');
                if ($smsBlock) {
                    $layout->unsetElement('header.content');
                }
            }
            $this->_view->renderLayout();
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
