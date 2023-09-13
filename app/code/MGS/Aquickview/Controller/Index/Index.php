<?php
namespace MGS\Aquickview\Controller\Index;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Catalog\Controller\Product\View
{

    public function execute()
    {
        
        $isAjax = $this->getRequest()->isAjax();
        if ($isAjax) {
            $id = $this->getRequest()->getParam('id');
            if(!$id){
				$manager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
				$store_id =  $manager->getStore()->getId();
				// get connnect pdo
				$_resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
				$conn =  $_resource->getConnection('core_read');
				$_path = $this->getRequest()->getParam('path') ? $this->getRequest()->getParam('path') : strstr($this->_request->getRequestUri(), '/path');
				$_path = str_replace("/path/", '', $_path);
				$_path = (strpos($_path, '?') !== false) ? substr($_path, strpos($_path, '?')) : $_path;
				// escape url path
				$str = $conn->quote($_path);
				$url_rewrite = $_resource->getTableName('url_rewrite');
				$select =  $conn->select()
					->from(['rp' => $url_rewrite], new \Zend_Db_Expr('entity_id'))
					->where('rp.request_path in ('.$str.')')
					->where('rp.store_id = ?', $store_id);
				$productId =  $conn->fetchOne($select);
            } else {
				$productId =$id;
            }
            
            if (!$productId) {
                return false;
            } else {
                 $this->getRequest()->setParam('id', $productId);
                 $product = $this->_initProduct();
				 
				 if($product && ($product->getMgsImageDimention()!='')){
					$dimention = $product->getMgsImageDimention();
				}else{
					$dimention = $this->getStoreConfig("themesettings/product_image_dimention/detail_big");
				}
				if($dimention != '') {
                    $ratio = $this->getRatioProduct($dimention);
                    $padding = $ratio['height'] /  $ratio['width'] * 50;
                } else {
                    $padding = 50;
                }
				
				$layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
                
                switch ($product->getTypeId()) {
                    case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:
                         $layout->getUpdate()->load(['quickview_product_type_bundle']);
                        break;
                    
                    case \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE:
                         $layout->getUpdate()->load(['quickview_product_type_downloadable']);
                        break;
                    
                    case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                         $layout->getUpdate()->load(['quickview_product_type_grouped']);
                        break;
                     
                    case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
                         $layout->getUpdate()->load(['quickview_product_type_simple']);
                        break;
                    
					case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL:
                         $layout->getUpdate()->load(['quickview_product_type_simple']);
						 break;
						 
                    default:
                        $layout->getUpdate()->load(['quickview_product_type_configurable']);
                }
                
                 $product_info=$layout->getOutput();
                 $output=[];
                 $output['sucess']=true;
                 $output['id_product']= $productId;
                 $output['type_product']=$product->getTypeId();
                 $output['title']=$product->getName();
                 $output['product_detail']=$product_info;
                 $output['padding']=$padding;
				 
                return $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($output));
            }
        } else {
            return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }
	
	protected function getStore(){
		$storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		return $storeManager->getStore();
	}
	
	/* Get system store config */
	protected function getStoreConfig($node){
		$scopeConfig = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
		return $scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
	}
	
	protected function getRatioProduct($dimention) {
		$result = [];
		$arrDimention = explode('x',$dimention);
		if(($dimention!='') && (count($arrDimention)>0)){
			$result['width'] = trim($arrDimention[0]);
			if(isset($arrDimention[1])){
				$result['height'] = trim($arrDimention[1]);
			}else{
				$result['height'] = $result['width'];
			}
		}
		return $result;
	}
}
