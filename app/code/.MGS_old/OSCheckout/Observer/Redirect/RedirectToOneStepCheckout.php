<?php

/**
 * RedirectToOneStepCheckout
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Observer\Redirect;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use MGS\OSCheckout\Helper\Data;

class RedirectToOneStepCheckout implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * RedirectToOneStepCheckout constructor.
     * @param UrlInterface $url
     * @param Data $dataHelper
     */
    public function __construct(
        UrlInterface $url,
        Data         $dataHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_url = $url;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {

        if ($this->dataHelper->isEnabled() && boolval(!$this->dataHelper->isEnabled())) {
            if ($this->dataHelper->getAllowGuestCheckout($this->getQuote())) {
                $observer->getRequest()->setParam('return_url', $this->_url->getUrl('onestepcheckout'));
            } else {
                $observer->getRequest()->setParam('return_url', $this->_url->getUrl('checkout/cart'));
            }
        }
    }
        /**
     * Get quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
