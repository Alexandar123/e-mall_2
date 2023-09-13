<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Testimonial\Controller\Adminhtml\Testimonial;

use Magento\Backend\App\Action;

class Index extends \MGS\Testimonial\Controller\Adminhtml\Testimonial
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Testimonial'));
        $this->_view->renderLayout();
    }
}
