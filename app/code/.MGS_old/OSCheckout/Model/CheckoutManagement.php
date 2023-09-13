<?php
/**
 * CheckoutManagement
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model;

use Exception;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\GiftMessage\Model\GiftMessageManager;
use Magento\GiftMessage\Model\Message;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use MGS\OSCheckout\Api\CheckoutManagementInterface;
use MGS\OSCheckout\Api\Data\OSCheckoutDetailsInterface;
use MGS\OSCheckout\Helper\Item as OSCheckoutHelper;
use Psr\Log\LoggerInterface;


class CheckoutManagement implements CheckoutManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var OSCheckoutDetailsFactory
     */
    protected $OSCheckoutDetailsFactory;

    /**
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Checkout session
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * @var OSCheckoutHelper
     */
    protected $OSCheckoutHelper;

    /**
     * @var Message
     */
    protected $giftMessage;

    /**
     * @var GiftMessageManager
     */
    protected $giftMessageManagement;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var TotalsCollector
     */
    protected $_totalsCollector;

    /**
     * @var AddressInterface
     */
    protected $_addressInterface;

    /**
     * @var ShippingMethodConverter
     */
    protected $_shippingMethodConverter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CheckoutManagement constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param OSCheckoutDetailsFactory $OSCheckoutDetailsFactory
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartTotalRepositoryInterface $cartTotalsRepository
     * @param UrlInterface $urlBuilder
     * @param Session $checkoutSession
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param OSCheckoutHelper $OSCheckoutHelper
     * @param Message $giftMessage
     * @param GiftMessageManager $giftMessageManager
     * @param CustomerSession $customerSession
     * @param TotalsCollector $totalsCollector
     * @param AddressInterface $addressInterface
     * @param ShippingMethodConverter $shippingMethodConverter
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        DetailsFactory $OSCheckoutDetailsFactory,
        ShippingMethodManagementInterface $shippingMethodManagement,
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartTotalRepositoryInterface $cartTotalsRepository,
        UrlInterface $urlBuilder,
        Session $checkoutSession,
        ShippingInformationManagementInterface $shippingInformationManagement,
        OSCheckoutHelper $OSCheckoutHelper,
        Message $giftMessage,
        GiftMessageManager $giftMessageManager,
        customerSession $customerSession,
        TotalsCollector $totalsCollector,
        AddressInterface $addressInterface,
        ShippingMethodConverter $shippingMethodConverter,
        LoggerInterface $logger
    ) {
        $this->cartRepository = $cartRepository;
        $this->OSCheckoutDetailsFactory = $OSCheckoutDetailsFactory;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->_urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->OSCheckoutHelper = $OSCheckoutHelper;
        $this->giftMessage = $giftMessage;
        $this->giftMessageManagement = $giftMessageManager;
        $this->_customerSession = $customerSession;
        $this->_totalsCollector = $totalsCollector;
        $this->_addressInterface = $addressInterface;
        $this->_shippingMethodConverter = $shippingMethodConverter;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function updateItemQty($cartId, $itemId, $itemQty)
    {
        if ($itemQty === 0) {
            return $this->removeItemById($cartId, $itemId);
        }

        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain item  %2', $cartId, $itemId));
        }

        try {
            $quoteItem->setQty($itemQty)->save();
            $this->cartRepository->save($quote);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new CouldNotSaveException(__('Could not update item from quote'));
        }

        return $this->getResponseData($quote);
    }

    /**
     * {@inheritDoc}
     */
    public function removeItemById($cartId, $itemId)
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain item  %2', $cartId, $itemId));
        }
        try {
            $quote->removeItem($itemId);
            $this->cartRepository->save($quote);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new CouldNotSaveException(__('Could not remove item from quote'));
        }

        return $this->getResponseData($quote);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentTotalInformation($cartId)
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);

        return $this->getResponseData($quote);
    }

    /**
     * {@inheritDoc}
     */
    public function updateGiftWrap($cartId, $isUseGiftWrap)
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $quote->getShippingAddress()->setUsedGiftWrap($isUseGiftWrap);

        try {
            $this->cartRepository->save($quote);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new CouldNotSaveException(__('Could not add gift wrap for this quote'));
        }

        return $this->getResponseData($quote);
    }

    /**
     * Response data to update OSCheckout block
     *
     * @param Quote $quote
     *
     * @return OSCheckoutDetailsInterface
     * @throws NoSuchEntityException
     */
    public function getResponseData(Quote $quote)
    {
        /** @var OSCheckoutDetailsInterface $OSCheckoutDetails */
        $OSCheckoutDetails = $this->OSCheckoutDetailsFactory->create();

        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return $OSCheckoutDetails->setRedirectUrl($this->_urlBuilder->getUrl('checkout/cart'));
        }

        if ($quote->getShippingAddress()->getCountryId()) {
            $OSCheckoutDetails->setShippingMethods($this->getShippingMethods($quote));
        }

        $OSCheckoutDetails->setPaymentMethods($this->paymentMethodManagement->getList($quote->getId()));
        $OSCheckoutDetails->setTotals($this->cartTotalsRepository->get($quote->getId()));

        $imageData = [];
        $optionsData = [];
        $requestPath = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            $optionsData[$item->getId()] = $this->OSCheckoutHelper->getItemOptionsConfig($quote, $item);
            $imageData[$item->getId()] = $this->OSCheckoutHelper->getItemImages($item);
            $requestPath[$item->getId()] = $product->getUrlModel()->getUrl($product);
        }

        $OSCheckoutDetails
            ->setImageData($this->OSCheckoutHelper->jsonEncodeData($imageData))
            ->setOptions($this->OSCheckoutHelper->jsonEncodeData($optionsData))
            ->setRequestPath($this->OSCheckoutHelper->jsonEncodeData($requestPath));

        return $OSCheckoutDetails;
    }

    /**
     * {@inheritDoc}
     */
    public function saveCheckoutInformation(
        $cartId,
        ShippingInformationInterface $addressInformation,
        $customerAttributes = [],
        $additionInformation = []
    ) {
        try {
            $additionInformation['customerAttributes'] = $customerAttributes;
            $this->checkoutSession->setOSCheckoutData($additionInformation);
            $this->addGiftMessage($cartId, $additionInformation);

            if ($addressInformation->getShippingAddress()) {
                if (!empty($additionInformation['billing-same-shipping'])
                    && $this->_customerSession->isLoggedIn()) {
                    $addressInformation->getShippingAddress()->setSaveInAddressBook(0);
                }
                $this->shippingInformationManagement->saveAddressInformation($cartId, $addressInformation);
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new InputException(__('Unable to save order information. Please check input data.'));
        }

        return true;
    }

    /**
     * @param Quote $quote
     *
     * @return array
     */
    public function getShippingMethods(Quote $quote)
    {
        $result = [];
        $shippingAddress = $quote->getShippingAddress();
        $addressData = $this->_addressInterface->getData();
        $shippingAddress->addData($addressData);
        $shippingAddress->setCollectShippingRates(true);
        $this->_totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            if (!is_array($carrierRates)) {
                continue;
            }
            foreach ($carrierRates as $rate) {
                $result[] = $this->_shippingMethodConverter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }

        return $result;
    }

    /**
     * @param $cartId
     * @param $additionInformation
     *
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function addGiftMessage($cartId, $additionInformation)
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);

        if (isset($additionInformation['giftMessage']) && !$this->OSCheckoutHelper->isDisabledGiftMessage()) {
            $giftMessage = $this->OSCheckoutHelper->jsonDecodeData($additionInformation['giftMessage']);
            $this->giftMessage->setSender(isset($giftMessage['sender']) ? $giftMessage['sender'] : '');
            $this->giftMessage->setRecipient(isset($giftMessage['recipient']) ? $giftMessage['recipient'] : '');
            $this->giftMessage->setMessage(isset($giftMessage['message']) ? $giftMessage['message'] : '');
            $this->giftMessageManagement->setMessage($quote, 'quote', $this->giftMessage);
        }
    }
}
