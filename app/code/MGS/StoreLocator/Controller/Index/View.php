<?php

namespace MGS\StoreLocator\Controller\Index;

use MGS\StoreLocator\Model\StoreFactory;
use Magento\Framework\App\Action\Context;

class View extends \Magento\Framework\App\Action\Action {

    protected $_storeFactory;
    protected $_coreRegistry;
    
    public function __construct(Context $context, StoreFactory $storeFactory, \Magento\Framework\Registry $coreRegistry) {
        parent::__construct($context);
        $this->_storeFactory = $storeFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    public function execute() {
        $id = $this->getRequest()->getParam('id', false);
        if(!$id) {
            return $this->_forward('noRoute');
        }
        $model = $this->_storeFactory->create()->load($id);
        $this->_coreRegistry->register('store_view', $model);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $data = $model->getData();
        $name = $data['name'];
        $this->_view->getPage()->getConfig()->getTitle()->set(__($name));
        $this->_view->renderLayout();
    }

}
