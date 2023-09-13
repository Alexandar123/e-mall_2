<?php

namespace MGS\StoreLocator\Controller\Index;

use MGS\StoreLocator\Model\StoreFactory;
use Magento\Framework\Session\SessionManager as SessionManager;
use Magento\Framework\App\Action\Context;

class Searchbydistance extends \Magento\Framework\App\Action\Action {

    protected $_storeFactory;
    protected $_sessionManager;

    public function __construct(Context $context, StoreFactory $storeFactory, SessionManager $sessionManager) {
        parent::__construct($context);
        $this->_storeFactory = $storeFactory;
        $this->_sessionManager = $sessionManager;
    }

    public function execute() {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Store Locator'));
        $this->_view->renderLayout();
    }

}