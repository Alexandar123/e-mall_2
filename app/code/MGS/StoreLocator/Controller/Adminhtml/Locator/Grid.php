<?php

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\StoreLocator\Controller\Adminhtml\Locator;

class Grid extends \Magento\Backend\App\Action {

    /**
     * Managing store locator grid
     *
     * @return void
     */
    public function execute() {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

}
