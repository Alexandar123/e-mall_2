<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Controller\Adminhtml\Portfolio;

use Magento\Backend\App\Action;

class Index extends \MGS\Portfolio\Controller\Adminhtml\Portfolio
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Portfolio'));
        $this->_view->renderLayout();
    }
}
