<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Testimonial\Controller\Adminhtml\Testimonial;

class NewAction extends \MGS\Testimonial\Controller\Adminhtml\Testimonial
{
    /**
     * Create new customer action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('New Item'));
        return $resultPage;
    }
}
