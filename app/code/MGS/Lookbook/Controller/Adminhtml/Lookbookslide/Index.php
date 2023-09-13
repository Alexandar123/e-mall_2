<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Controller\Adminhtml\Lookbookslide;

use Magento\Backend\App\Action;

class Index extends \MGS\Lookbook\Controller\Adminhtml\Lookbookslide
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Lookbook Slider'));
        $this->_view->renderLayout();
    }
}
