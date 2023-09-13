<?php
/**
 * Details
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MGS\OSCheckout\Api\Data\DetailsInterface;

class Details extends AbstractExtensibleModel implements DetailsInterface
{

    public function getShippingMethods()
    {
        return $this->getData(self::SHIPPING_METHODS);
    }


    public function setShippingMethods($shippingMethods)
    {
        return $this->setData(self::SHIPPING_METHODS, $shippingMethods);
    }


    public function getPaymentMethods()
    {
        return $this->getData(self::PAYMENT_METHODS);
    }


    public function setPaymentMethods($paymentMethods)
    {
        return $this->setData(self::PAYMENT_METHODS, $paymentMethods);
    }

    public function getTotals()
    {
        return $this->getData(self::TOTALS);
    }


    public function setTotals($totals)
    {
        return $this->setData(self::TOTALS, $totals);
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getData(self::REDIRECT_URL);
    }

    /**
     * @param $url
     * @return $this
     */
    public function setRedirectUrl($url)
    {
        return $this->setData(self::REDIRECT_URL, $url);
    }
}
