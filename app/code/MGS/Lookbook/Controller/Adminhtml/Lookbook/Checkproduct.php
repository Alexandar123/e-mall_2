<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Lookbook\Controller\Adminhtml\Lookbook;

use Magento\Framework\Controller\ResultFactory;
class Checkproduct extends \MGS\Lookbook\Controller\Adminhtml\Lookbook
{
    private $productRepository; 
	
	protected $_helper;
	
	protected $_priceHelper;
	
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\MGS\Lookbook\Helper\Data $_helper,
		\Magento\Framework\Pricing\Helper\Data $_priceHelper,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository
	)
    {
        parent::__construct($context);
		$this->_helper = $_helper;
		$this->_priceHelper = $_priceHelper;
		$this->productRepository = $productRepository;
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		$product_id = 0;
		$sku = $this->getRequest()->getPost('text');
		$defaultPinText = $this->_helper->getStoreConfig('lookbook/general/pin_default');
		$labelPost = $this->getRequest()->getPost('label');
		if($sku!=''){
			$product = $this->productRepository->get($sku);
			$product_id = $product->getId();
			$status =  $product->getStatus();
			$result['label'] = 0;

			if($this->_helper->getStoreConfig('lookbook/general/pin_price')){
				//
				$price = strip_tags($this->_priceHelper->currency($product->getFinalPrice()));
				$price = str_replace('.00','',$price);
				$result['label'] = $price;

				if($labelPost != ''){
					if($this->_helper->getStoreConfig('lookbook/general/pin_price') && ($labelPost != $price)){
						$result['label'] = $labelPost;
					}
				}
			}else{
				if($labelPost!=''){
					$result['label'] = $labelPost;
				}else{
					$result['label'] = $defaultPinText;
				}
			}
		}
			
		if ($product_id) {
			if ($status==1) 
			{
			  $result['status'] = 1;
			}
			else
			{
			  $result['status'] = "is disabled";  
			}
			
		}
		else
		{
			$result['status'] = "doesn't exists"; 
			if($labelPost!=''){
				$result['label'] = $labelPost;
			}else{
				$result['label'] = $defaultPinText;
			}
		}
		$result = json_encode($result);
		$this->getResponse()->setBody($result);
    }
}
