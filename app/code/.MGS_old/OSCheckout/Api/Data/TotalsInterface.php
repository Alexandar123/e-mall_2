<?php
/**
 * TotalsInterface
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Api\Data;


interface TotalsInterface
{
    const TOTALS = 'totals';
    const IMAGE_DATA = 'imageData';
    const OPTIONS_DATA = 'options';
    const SHIPPING = 'shipping';
    const PAYMENT = 'payment';

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals();

    /**
     * @return string Json encoded data
     */
    public function getImageData();

    /**
     * @return string Json encoded data
     */
    public function getOptionsData();

    /**
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     */
    public function getShipping();

    /**
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[] Array of payment methods.
     */
    public function getPayment();
}
