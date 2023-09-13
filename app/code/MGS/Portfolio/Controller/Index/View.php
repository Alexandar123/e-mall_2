<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Portfolio\Controller\Index;

class View extends \Magento\Framework\App\Action\Action
{	
    public function execute()
    {
		if($id = $this->getRequest()->getParam('id')){
			$this->_view->loadLayout();
			$this->_view->renderLayout();
		}else{
			return $this->_redirect('');
		}
    }
}
