<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\GDPR\Controller\Customer;

use MGS\GDPR\Controller\Customer as CustomerController;
use Magento\Framework\Controller\ResultFactory;

class Index extends CustomerController
{
    /**
     * Render my product reviews
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('gdpr/customer');
        }
        $resultPage->getConfig()->getTitle()->set(__('GDPR'));
        return $resultPage;
    }
}
