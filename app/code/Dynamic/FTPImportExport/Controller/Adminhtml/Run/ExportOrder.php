<?php
/**
 * * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dynamic\FTPImportExport\Controller\Adminhtml\Run;

use Magento\Framework\Controller\Result\JsonFactory;

class ExportOrder extends \Magento\Backend\App\Action
{
	protected $_variableFactory;
	protected $helperData;
	protected $categoryFactory;
	protected $productRepository;
	protected $ftp;
	protected $product;
	protected $_productCollectionFactory;
	protected $customer;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Framework\Filesystem\Io\Ftp $ftp,
		\Magento\Catalog\Model\Product $product,
		\Magento\Variable\Model\VariableFactory $variableFactory,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Customer\Model\Customer $customer
    ) { 
        $this->customer = $customer;
		$this->categoryFactory = $categoryFactory;
		$this->productRepository = $productRepository;
		$this->ftp = $ftp;
		$this->_product = $product;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_variableFactory = $variableFactory;
		
		
		return parent::__construct($context);
    }

    /**
     * Check whether vat is valid
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    { 
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$Filelines = $this->getFTPFileData( $objectManager );
	}
	public function getFTPFileData($objectManager)
    {	
		
	}
}