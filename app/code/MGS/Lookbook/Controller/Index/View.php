<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mmegamenu\Controller\Index;

class View extends \Magento\Framework\App\Action\Action
{
    
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
