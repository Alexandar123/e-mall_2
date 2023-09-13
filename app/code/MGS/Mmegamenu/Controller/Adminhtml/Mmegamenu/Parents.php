<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu;

class Parents extends \MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu
{
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Megamenu'));
        $this->_view->renderLayout();
    }
}
