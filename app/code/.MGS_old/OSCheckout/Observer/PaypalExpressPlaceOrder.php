<?php
/**
 * PaypalExpressPlaceOrder
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MGS\OSCheckout\Model\CheckoutRegister;

class PaypalExpressPlaceOrder implements ObserverInterface
{
    /**
     * @var \MGS\OSCheckout\Model\CheckoutRegister
     */
    protected $checkoutRegister;

    /**
     * PaypalExpressPlaceOrder constructor.
     * @param \MGS\OSCheckout\Model\CheckoutRegister $checkoutRegister
     */
    public function __construct(CheckoutRegister $checkoutRegister)
    {
        $this->checkoutRegister = $checkoutRegister;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $this->checkoutRegister->checkRegisterNewCustomer();
    }
}
