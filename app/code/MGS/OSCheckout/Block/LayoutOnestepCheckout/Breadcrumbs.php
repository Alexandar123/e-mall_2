<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Simple product data view
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace MGS\OSCheckout\Block\LayoutOnestepCheckout;

use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;

class Breadcrumbs extends \Magento\Framework\View\Element\Template
{
	/**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;

    /**
     * @param Context $context
     * @param Data $catalogData
     * @param array $data
     */
    public function __construct(Context $context, Data $catalogData, array $data = [])
    {
        $this->_catalogData = $catalogData;
        parent::__construct($context, $data);
    }
	
    public function getCrumbs()
	{
		$crumbs = [
			[
				'label' => __('Home'),
				'title' => __('Go to Home Page'),
				'link' => $this->_storeManager->getStore()->getBaseUrl()
			],
			[
				'label' => __('Checkout'),
				'title' => __('Checkout Page'),
				'link' => ''
			]
		];
		return $crumbs;
	}
}
