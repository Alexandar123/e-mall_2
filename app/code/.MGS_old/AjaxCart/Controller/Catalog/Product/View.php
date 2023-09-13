<?php

namespace MGS\AjaxCart\Controller\Catalog\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Catalog\Controller\Product\View
{
    public function execute()
    {
		$resultPage = $this->resultPageFactory->create();
		$id = $this->getRequest()->getParam('id');
		$product = $this->_initProduct();
		
		$layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
                
		switch ($product->getTypeId()) {
			case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:
				 $layout->getUpdate()->load(['ajax_product_type_bundle']);
				break;
			
			case \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE:
				 $layout->getUpdate()->load(['ajax_product_type_downloadable']);
				break;
			
			case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
				 $layout->getUpdate()->load(['ajax_product_type_grouped']);
				break;
			 
			case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
				 $layout->getUpdate()->load(['ajax_product_type_simple']);
				break;
			
			default:
				$layout->getUpdate()->load(['ajax_product_type_configurable']);
		}
		
		 $product_info = $layout->getOutput();
		 $output=[];
		 $output['sucess']=true;
		 $output['id_product']= $id;
		 $output['type_product']=$product->getTypeId();
		 $output['title']=$product->getName();
		 $output['product_detail']=$product_info;
		 
		return $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($output));
	}
}
