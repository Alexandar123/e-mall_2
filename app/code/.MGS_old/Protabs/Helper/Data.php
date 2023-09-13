<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Protabs\Helper;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_storeManager;

	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

	protected $_attributeCollection;
    protected $_scopeConfig;
    protected $request;

	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
		\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection
	){
        $this->_storeManager = $storeManager;
		$this->_objectManager = $objectManager;
		$this->_attributeCollection = $attributeCollection;
        $this->_scopeConfig = $scopeConfig;
        $this->request = $request;
    }

	public function getStore(){
        return $this->_storeManager->getStore();
    }

    public function getModel($model){
        return $this->_objectManager->create($model);
    }

    public function getStoreConfig($node){
        return $this->_scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	public function getAttributeCollection(){
		return $this->_attributeCollection->create()->addVisibleFilter();
	}

	public function getTabsCollection(){
		$storeId = $this->getStore()->getId();
		$websiteId = $this->getStore()->getWebsiteId();
		$collection = $this->getModel('MGS\Protabs\Model\Protabs')->getCollection()->addFieldToFilter('scope', 'stores')->addFieldToFilter('scope_id', $storeId)->setOrder('position', 'ASC');

		if($collection->getSize()==0){
			$collection  = $this->getModel('MGS\Protabs\Model\Protabs')->getCollection()->addFieldToFilter('scope', 'websites')->addFieldToFilter('scope_id', $websiteId)->setOrder('position', 'ASC');

			if($collection->getSize()==0){
				$collection  = $this->getModel('MGS\Protabs\Model\Protabs')->getCollection()->addFieldToFilter('scope', 'default')->setOrder('position', 'ASC');
			}
		}
		return $collection;
	}

	public function convertAttributeToCallName($attributeCode){
		$arrText = explode("_", $attributeCode);
		$result = 'get';
		if(count($arrText)>1){
			foreach($arrText as $_text){
				$result.=ucfirst($_text);
			}
		}else{
			$result.=ucfirst($arrText[0]);
		}
		return $result;
	}

	public function getAttributeType($attributeCode){
		$attribute = $this->getAttributeCollection()->addFieldToFilter('attribute_code', $attributeCode)->getFirstItem();
		if($attribute->getFrontendInput() == 'select'){
			return 'text';
		}
		if($attribute->getFrontendInput() == 'multiselect'){
			return 'list';
		}
		return 'none';
	}

	public function getCurrentProduct() {
	    return $this->_objectManager->get('Magento\Framework\Registry')->registry('current_product');
    }

    public function getReviewCount($id) {
        $reviewFactory = $this->_objectManager->create('Magento\Review\Model\Review');
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
        $storeManager  = $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        $reviewFactory->getEntitySummary($product, $storeId);
        $reviewCount = $product->getRatingSummary()->getReviewsCount();
        return $reviewCount;
    }

    public function getRequest()
    {
        return $this->request->isSecure();
    }
}
