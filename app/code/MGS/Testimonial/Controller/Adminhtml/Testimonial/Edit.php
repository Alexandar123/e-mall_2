<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Testimonial\Controller\Adminhtml\Testimonial;

class Edit extends \MGS\Testimonial\Controller\Adminhtml\Testimonial
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Item'));
        return $resultPage;
    }
}
