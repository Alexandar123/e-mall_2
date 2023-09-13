<?php
/**
 * GuestCheckoutManagement
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use MGS\OSCheckout\Api\CheckoutManagementInterface;
use MGS\OSCheckout\Api\GuestCheckoutManagementInterface;


class GuestCheckoutManagement implements GuestCheckoutManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @type CheckoutManagementInterface
     */
    protected $checkoutManagement;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * GuestCheckoutManagement constructor.
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CheckoutManagementInterface $checkoutManagement
     * @param CartRepositoryInterface $cartRepository
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        QuoteIdMaskFactory          $quoteIdMaskFactory,
        CheckoutManagementInterface $checkoutManagement,
        CartRepositoryInterface     $cartRepository,
        AccountManagementInterface  $accountManagement
    )
    {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->checkoutManagement = $checkoutManagement;
        $this->cartRepository = $cartRepository;
        $this->accountManagement = $accountManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function updateItemQty($cartId, $itemId, $itemQty)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->updateItemQty($quoteIdMask->getQuoteId(), $itemId, $itemQty);
    }

    /**
     * {@inheritDoc}
     */
    public function removeItemById($cartId, $itemId)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->removeItemById($quoteIdMask->getQuoteId(), $itemId);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentTotalInformation($cartId)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->getPaymentTotalInformation($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritDoc}
     */
    public function updateGiftWrap($cartId, $isUseGiftWrap)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->updateGiftWrap($quoteIdMask->getQuoteId(), $isUseGiftWrap);
    }

    /**
     * {@inheritDoc}
     */
    public function saveCheckoutInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation,
        $customerAttributes = [],
        $additionInformation = []
    )
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->checkoutManagement->saveCheckoutInformation(
            $quoteIdMask->getQuoteId(),
            $addressInformation,
            $customerAttributes,
            $additionInformation
        );
    }

    /**
     * {@inheritDoc}
     */
    public function saveEmailToQuote($cartId, $email)
    {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
        $quote->setCustomerEmail($email);

        try {
            $this->cartRepository->save($quote);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmailAvailable($cartId, $customerEmail, $websiteId = null)
    {
        $this->saveEmailToQuote($cartId, $customerEmail);

        return $this->accountManagement->isEmailAvailable($customerEmail, $websiteId = null);
    }
}
