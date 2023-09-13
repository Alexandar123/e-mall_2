<?php

/**
 * * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dynamic\FTPImportExport\Controller\Adminhtml\Run;

class Regularproducts extends \Magento\Backend\App\Action
{
	protected $importProduct;
	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Dynamic\FTPImportExport\Helper\ImportProduct $ImportProduct
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Dynamic\FTPImportExport\Helper\ImportProduct $importProduct
	) {
		
		$this->importProduct = $importProduct;
		return parent::__construct($context);
	}

	/**
	 *
	 * @return \Magento\Framework\Controller\Result\Json
	 */
	public function execute()
	{
		$this->importProduct->import($checkisFilename = true);
	}
}
