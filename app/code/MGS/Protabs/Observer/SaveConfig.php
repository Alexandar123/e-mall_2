<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Protabs\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class SaveConfig implements ObserverInterface
{
	protected $_objectManager;
	protected $_request;
	protected $_attributeCollection;
	protected $_messageManager;
	
	public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
		RequestInterface $request,
		\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection,
		\Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_objectManager = $objectManager;
		$this->_request = $request;
		$this->_attributeCollection = $attributeCollection;
		$this->_messageManager = $messageManager;
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Protabs\Model\Protabs');
	}
	
	public function getAttributeCollection(){
		return $this->_attributeCollection->create()->addVisibleFilter();
	}
	
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->_request->getPost();
		
		if(isset($data['remove_website']) || isset($data['remove_store_view'])){
			if(isset($data['remove_website']) && ($data['remove_website'] == 1)){
				$collectionToDelete = $this->getModel()->getCollection()
					->addFieldToFilter('scope', 'websites')
					->addFieldToFilter('scope_id', $this->_request->getParam('website'));
			}
			
			if(isset($data['remove_store_view']) && ($data['remove_store_view'] == 1)){
				$collectionToDelete = $this->getModel()->getCollection()
					->addFieldToFilter('scope', 'stores')
					->addFieldToFilter('scope_id', $this->_request->getParam('store'));
			}
			
			if(count($collectionToDelete)>0){
				foreach($collectionToDelete as $_itemDelete){
					$_itemDelete->delete();
				}
			}
		}
		
		if(isset($data['title'])){
			$collection = $this->getModel()->getCollection();
			
			if($this->_request->getParam('website') || $this->_request->getParam('store')){
				if($websiteId =  $this->_request->getParam('website')){
					$collection->addFieldToFilter('scope', 'websites')->addFieldToFilter('scope_id', $websiteId);
				}
				if($storeId =  $this->_request->getParam('store')){
					$collection->addFieldToFilter('scope', 'stores')->addFieldToFilter('scope_id', $storeId);
				}
			}else{
				$collection->addFieldToFilter('scope', 'default');
			}
		
			foreach($collection as $item){
				$item->delete();
			}
			
			$scope = 'default';
			$scopeId = 0;
			if($websiteId =  $this->_request->getParam('website')){
				$scope = 'websites';
				$scopeId = $websiteId;
			}
			if($storeId =  $this->_request->getParam('store')){
				$scope = 'stores';
				$scopeId = $storeId;
			}
			
			foreach($data['title'] as $key=>$value){
				if($data['title'][$key] != ''){
					$itemData = array();
					$itemData['title'] = $data['title'][$key];
					$itemData['tab_type'] = $data['tab_type'][$key];
					$itemData['value'] = $data['value'][$key];
					$itemData['position'] = $data['position'][$key];
					$itemData['scope'] = $scope;
					$itemData['scope_id'] = $scopeId;
					
					if($itemData['tab_type']=='attribute'){
						$attributes = $this->getAttributeCollection();
						$attributes->addFieldToFilter('attribute_code', $itemData['value']);
						if(count($attributes)>0){
							$this->getModel()->setData($itemData)->save();
						}
						else{
							$this->_messageManager->addError(__('Attribute with attribute_code is "%1" does not exist.', $itemData['value']));
						}
					}
					else{
						$this->getModel()->setData($itemData)->save();
					}
				}
			}
		}
    }
}
