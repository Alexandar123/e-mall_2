<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\ExtraGallery\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReplaceTeamplate implements ObserverInterface {

    const XML_CONFIG_TYPE = 'extragallery/general/glr_type';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

	/**
     * @var \Magento\Framework\App\Request\Http
     */
	protected $_request;

	/**
     * @var \Magento\Framework\Registry
     */
    protected $registry;


    /**
     * AdminFailed constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\Registry $registry
    )
    {
        $this->scopeConfig = $scopeConfig;
		$this->_request = $request;
		$this->registry = $registry;
    }

    public function execute(Observer $observer) {

		$product = $this->registry->registry('product');

		if ($product && $product->getId()) {
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			$type = $this->scopeConfig->getValue(self::XML_CONFIG_TYPE, $storeScope);
			if($product->getData('extragallery_glr_type')){
				$type = $product->getData('extragallery_glr_type');
			}

			$action = $this->_request->getFullActionName();

			$layout = $observer->getLayout();
			$blockProductGallery = $layout->getBlock('product.info.media.image');

			if($blockProductGallery){
				$layout->unsetElement('product.info.media.image');
				if($type == 3){
					$layout->unsetElement('breadcrumbs');
					$layout->unsetElement('product.info.overview');
					$layout->unsetElement('product.info.media');
				}else {
					$layout->unsetElement('product.info.extra.media.image.type');
					$layout->unsetElement('product.info.overview.copy');
				}
			}
		}

		return $this;
    }
}
