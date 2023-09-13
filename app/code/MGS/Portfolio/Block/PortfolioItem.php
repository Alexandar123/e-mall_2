<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Portfolio\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class PortfolioItem extends Template
{
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
		Template\Context $context, 
		\Magento\Framework\ObjectManagerInterface $objectManager,
		array $data = []
	)
    {
        parent::__construct($context, $data);
		$this->_objectManager = $objectManager;
    }

	/**
     * Prepare global layout
     *
     * @return $this
     */

	public function getModel(){
		return $this->_objectManager->create('MGS\Portfolio\Model\Portfolio');
	}

	public function getPortfolio(){
		return $this->getModel()->load($this->getRequest()->getParam('id'));
	}

	public function getBaseImage(){
		$filePath = 'mgs_portfolio/image/';
		if($filePath!=''){
			$imageUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $filePath;
			return $imageUrl;
		}
		return 0;
	}
}

