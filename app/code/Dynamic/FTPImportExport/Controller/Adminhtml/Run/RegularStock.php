<?php

/**
 * * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dynamic\FTPImportExport\Controller\Adminhtml\Run;

class RegularStock extends \Magento\Backend\App\Action
{
	protected $importStock;
	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Dynamic\FTPImportExport\Helper\ImportStock $importStock
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Dynamic\FTPImportExport\Helper\ImportStock $importStock
	) {
		
		$this->importStock = $importStock;
		return parent::__construct($context);
	}

	/**
	 *
	 * @return \Magento\Framework\Controller\Result\Json
	 */
	public function execute()
	{
		$this->importStock->import($checkisFilename = true);
	}
}
