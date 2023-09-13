<?php 

namespace MGS\StoreLocator\Controller;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;


class Router implements \Magento\Framework\App\RouterInterface
{
	protected $actionFactory;
	protected $eventManager;
	protected $objectManager;
	protected $storeManager;
	
	public function __construct (
		\Magento\Framework\App\ActionFactory $actionFactory, 
		 ManagerInterface $eventManager,
		\Magento\Framework\ObjectManagerInterface $objectManager, 
		 StoreManagerInterface $storeManager
		
    ) 
	{
        $this->actionFactory = $actionFactory;
		 $this->eventManager = $eventManager;
		$this->objectManager = $objectManager;
		$this->storeManager = $storeManager;
		
    }
	
	public function getModel(){
		return $this->objectManager -> create ('MGS\StoreLocator\Model\Store');
		
	}
	
	public function  match(\Magento\Framework\App\RequestInterface $request) {
		$urlKey = trim($request->getPathInfo(), '/');
            $origUrlKey = $urlKey;
            $condition = new DataObject(['store_url' => $urlKey, 'continue' => true]);
            $this->eventManager->dispatch(
                'mgs_storelocator_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );
            $urlKey = $condition->getStoreUrl();
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                );
            }
            if (!$condition->getContinue()) {
                return null;
            }
            $identifiers = explode('/', $urlKey);
            if (count($identifiers) == 2 || count($identifiers) == 1) {
                if (count($identifiers) == 2) {
                    $Url = $identifiers[1];
                }
                if (count($identifiers) == 1) {
                    $Url = $identifiers[0];
                }
                $store = $this->getModel()->getCollection()
                    ->addFieldToFilter('status', array('eq' => 1))
                    ->addFieldToFilter('store_url', array('eq' => $Url))
                    ->addStoreFilter($this->storeManager->getStore()->getId())
                    ->getFirstItem();
                if ($store && $store->getId()) {
                    $request->setModuleName('storelocator')
                        ->setControllerName('index')
                        ->setActionName('view')
                        ->setParam('id', $store->getId());
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                    );
                }
            }
		
	}
	
	
	
}
