<?php
/**
 * GuestCheckoutManagementInterface
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Api;

/**
 * Interface for update item information
 * @api
 */
interface GuestCheckoutManagementInterface
{
    /**
     * @param string $cartId
     * @param int $itemId
     * @param int $itemQty
     * @return \MGS\OSCheckout\Api\Data\DetailsInterface
     */
    public function updateItemQty($cartId, $itemId, $itemQty);

    /**
     * @param string $cartId
     * @return \MGS\OSCheckout\Api\Data\DetailsInterface
     */
    public function getPaymentTotalInformation($cartId);

    /**
     * @param string $cartId
     * @param int $itemId
     * @return \MGS\OSCheckout\Api\Data\DetailsInterface
     */
    public function removeItemById($cartId, $itemId);

    /**
     * @param string $cartId
     * @param bool $isUseGiftWrap
     * @return \MGS\OSCheckout\Api\Data\DetailsInterface
     */
    public function updateGiftWrap($cartId, $isUseGiftWrap);

    /**
     * @param string $cartId
     * @param string $email
     * @return bool
     */
    public function saveEmailToQuote($cartId, $email);

    /**
     * @param string $cartId
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
     * Check if given email is associated with a customer account in given website.
     *
     * @param string $cartId
     * @param string $customerEmail
     * @param int $websiteId If not set, will use the current websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isEmailAvailable($cartId, $customerEmail, $websiteId = null);
}
