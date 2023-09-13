<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Protabs\Controller\Adminhtml\Protabs;

use Magento\Backend\App\Action;

class Index extends \MGS\Protabs\Controller\Adminhtml\Protabs
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Protabs'));
        $this->_view->renderLayout();
    }
}
