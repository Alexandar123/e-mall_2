<?php
/**
 * DetailsInterface
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Api\Data;

interface DetailsInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    const REDIRECT_URL = 'redirect_url';
    const PAYMENT_METHODS = 'payment_methods';
    const TOTALS = 'totals';
    const SHIPPING_METHODS = 'shipping_methods';

    /**
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingMethods();

    /**
     * @param \Magento\Quote\Api\Data\PaymentMethodInterface[] $paymentMethods
     * @return $this
     */
    public function setPaymentMethods($paymentMethods);

    /**
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface[] $shippingMethods
     * @return $this
     */
    public function setShippingMethods($shippingMethods);

    /**
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[]
     */
    public function getPaymentMethods();

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals();

    /**
     * @return string
     */
    public function getRedirectUrl();

    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     * @return $this
     */
    public function setTotals($totals);

    /**
     * @param $url
     * @return $this
     */
    public function setRedirectUrl($url);
}
