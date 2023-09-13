<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Controller\Adminhtml\Lookbook;

use Magento\Framework\Controller\ResultFactory;
class Loadproduct extends \MGS\Lookbook\Controller\Adminhtml\Lookbook
{
    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;
	
	/**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
	
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Catalog\Block\Product\Context $catalogContext,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
	)
    {
        parent::__construct($context);
		$this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
		$this->_catalogConfig = $catalogContext->getCatalogConfig();
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		$q=$this->getRequest()->getPost('term');
		$responseData = [];
		$collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		
		$query = $q.'%';
		$collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
			->addAttributeToSelect('sku')
            ->addAttributeToFilter('sku', ['like'=> $query])
            ->addAttributeToSort('created_at', 'desc');
		
		if(count($collection)>0){
			foreach($collection as $_product){
				$responseData[] = ['id'=>$_product->getId(), 'label'=>$_product->getSku(), 'value'=>$_product->getSku()];
			}
		}
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
		return $resultJson;
    }
	
	protected function _addProductAttributesAndPrices(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addUrlRewrite();
    }
}
