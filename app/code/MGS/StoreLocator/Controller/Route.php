<?php
namespace MGS\StoreLocator\Controller;

use Magento\Framework\Url;
class Route implements \Magento\Framework\App\RouterInterface
{
    protected $actionFactory;
    protected $_response;
    protected $_storeFactory;
    public function __construct(
       \Magento\Framework\App\ActionFactory $actionFactory,
       \Magento\Framework\App\ResponseInterface $response,
       \MGS\StoreLocator\Model\StoreFactory $storeFactory
   ) {
       $this->actionFactory = $actionFactory;
       $this->_response = $response;
       $this->_storeFactory = $storeFactory;
   }
   public function match(\Magento\Framework\App\RequestInterface $request)
   {
        $urlKey = trim($request->getPathInfo(), '/storelocator/');
        if(preg_match('#storelocator#', $request->getPathInfo())) {
            $id =  trim($request->getPathInfo(), '/storelocator/index/view/id/');
            if(isset($id) && is_numeric($id)){
                $model = $this->_storeFactory->create();
                $data = $model->load($id)->getData();
                $route =  '';
            if(isset($data['route'])) {
                $route = $data['route'];
            }
            $request->setModuleName('storelocator')->setControllerName('index')->setActionName('view')->setParam('id', $data['id']);
            $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, 'storelocator/' . $route);
            $request->setDispatched(true);
            return $this->actionFactory->create(
                 'Magento\Framework\App\Action\Forward',
                 ['request' => $request]
            );
           }else{
                $getPathInfo = $request->getPathInfo();
                $getPathInfo = rtrim($getPathInfo,'/');
                $param = str_replace("/storelocator/", "", $getPathInfo);
                $model = $this->_storeFactory->create();
                $data = $model->getCollection()->addFieldToFilter('route',$param)->getData();

                if(isset($data) && is_array($data) && !empty($data)){
                    $request->setModuleName('storelocator')->setControllerName('index')->setActionName('view')->setParam('id', $data[0]['id']);
                    $request->setDispatched(true);
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                    );
                } 
           }
       }
   }
}