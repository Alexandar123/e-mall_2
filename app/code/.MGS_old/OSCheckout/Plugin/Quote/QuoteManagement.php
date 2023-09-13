<?php
/**
 * QuoteManagement
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Plugin\Quote;

use Magento\Quote\Model\Quote as QuoteEntity;

class QuoteManagement
{
    /**
     * @var \MGS\OSCheckout\Model\CheckoutRegister
     */
    protected $checkoutRegister;

    /**
     * QuoteManagement constructor.
     * @param \MGS\OSCheckout\Model\CheckoutRegister $checkoutRegister
     */
    public function __construct(\MGS\OSCheckout\Model\CheckoutRegister $checkoutRegister)
    {
        $this->checkoutRegister = $checkoutRegister;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param QuoteEntity $quote
     * @param array $orderData
     * @return array
     */
    public function beforeSubmit(\Magento\Quote\Model\QuoteManagement $subject, QuoteEntity $quote, $orderData = [])
    {
        $this->checkoutRegister->checkRegisterNewCustomer();

        return [$quote, $orderData];
    }
}
