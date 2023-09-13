<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu;

class NewAction extends \MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu
{
    public function execute()
    {
      $resultPage= $this->resultPageFactory->create();
      $resultPage->getConfig()->getTitle()->prepend(__('New Item'));
      return $resultPage;
    }
}
