<?php
/**
 * CheckoutManagementInterface
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Api;

interface CheckoutManagementInterface
{
    /**
     * @param int $cartId
     * @param int $itemId
     * @param int $itemQty
     * @return \MGS\OSCheckout\Api\Data\DetailsInterface
     */
    public function updateItemQty($cartId, $itemId, $itemQty);


    /**
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @param string[] $customerAttributes
     * @param string[] $additionInformation
     * @return bool
     */
    public function saveCheckoutInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation,
        $customerAttributes = [],
        $additionInformation = []
    );

    /**
     * @param int $cartId
     * @return \MGS\OSCheckout\Api\Data\DetailsInterface
     */
    public function getPaymentTotalInformation($cartId);

    /**
     * @param int $cartId
     * @param int $itemId
     * @return \MGS\OSCheckout\Api\Data\DetailsInterface
     */

    public function removeItemById($cartId, $itemId);

    /**
     * @param int $cartId
     * @param bool $isUseGiftWrap
     * @return \MGS\OSCheckout\Api\Data\DetailsInterface
     */
    public function updateGiftWrap($cartId, $isUseGiftWrap);
}
