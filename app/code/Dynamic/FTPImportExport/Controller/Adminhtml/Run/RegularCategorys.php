<?php

/**
 * * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dynamic\FTPImportExport\Controller\Adminhtml\Run;

class RegularCategorys extends \Magento\Backend\App\Action
{
	protected $importCategory;

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Dynamic\FTPImportExport\Helper\ImportProduct $importProduct
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Dynamic\FTPImportExport\Helper\ImportCategory $importCategory
	) {
		
		$this->importCategory = $importCategory;
		return parent::__construct($context);
	}

	/**
	 *
	 * @return \Magento\Framework\Controller\Result\Json
	 */
	public function execute()
	{
		$this->importCategory->import();
	}
}
