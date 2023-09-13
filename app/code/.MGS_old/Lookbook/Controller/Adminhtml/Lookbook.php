<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Controller\Adminhtml;
abstract class Lookbook extends \Magento\Backend\App\Action
{
	/**
     * Init actions
     *
     * @return $this
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'MGS_Lookbook::manage_lookbook'
        )->_addBreadcrumb(
            __('Lookbook'),
            __('Lookbook')
        );
        return $this;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Lookbook::lookbook');
    }
}
