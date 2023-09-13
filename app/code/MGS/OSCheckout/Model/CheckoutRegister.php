<?php
/**
 * CheckoutRegister
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\Quote;
use MGS\OSCheckout\Helper\Data;


class CheckoutRegister
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @type Copy
     */
    protected $_objectCopyService;

    /**
     * @type DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @type AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var bool
     */
    protected $_isCheckedRegister = false;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * CheckoutRegister constructor.
     * @param Session $checkoutSession
     * @param Copy $objectCopyService
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Quote\Model\CustomerManagement $customerManagement
     * @param Data $dataHelper
     */
    public function __construct(
        Session                    $checkoutSession,
        Copy                       $objectCopyService,
        DataObjectHelper           $dataObjectHelper,
        AccountManagementInterface $accountManagement,
        CustomerManagement         $customerManagement,
        Data                       $dataHelper
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->_objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountManagement = $accountManagement;
        $this->customerManagement = $customerManagement;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return $this
     */
    public function checkRegisterNewCustomer()
    {
        if ($this->isCheckedRegister()) {
            return $this;
        }

        $this->setIsCheckedRegister(true);

        /** @type \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        /** Validate address */
        $this->validateAddressBeforeSubmit($quote);

        /** One step check out additional data */
        $data = $this->checkoutSession->getData();

        /** Create account when checkout */
        if (isset($data['register']) && $data['register']
            && isset($data['password']) && $data['password']
        ) {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER)
                ->setCustomerIsGuest(false)
                ->setCustomerGroupId(null)
                ->setPasswordHash($this->accountManagement->getPasswordHash($data['password']));

            $this->_prepareNewCustomerQuote($quote, $data);
        }

        return $this;
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     */
    protected function _prepareNewCustomerQuote(Quote $quote, $data)
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $quote->getCustomer();
        $dataArray = $billing->getData();
        if (isset($data['customerAttributes']) && $data['customerAttributes']) {
            $dataArray = array_merge($dataArray, $data['customerAttributes']);
        }
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $dataArray,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );

        $quote->setCustomer($customer);

        /** Create customer */
        $this->customerManagement->populateCustomerInfo($quote);

        $this->dataHelper->setFlagMethodRegister(true);

        /** Init customer address */
        $customerBillingData = $billing->exportCustomerAddress();
        $customerBillingData->setIsDefaultBilling(true)
            ->setData('should_ignore_validation', true);

        if ($shipping) {
            if (isset($data['same_as_shipping']) && $data['same_as_shipping']) {
                $shipping->setCustomerAddressData($customerBillingData);
                $customerBillingData->setIsDefaultShipping(true);
            } else {
                $customerShippingData = $shipping->exportCustomerAddress();
                $customerShippingData->setIsDefaultShipping(true)
                    ->setData('should_ignore_validation', true);
                $shipping->setCustomerAddressData($customerShippingData);
                // Add shipping address to quote since customer Data Object does not hold address information
                $quote->addCustomerAddress($customerShippingData);
            }
        } else {
            $customerBillingData->setIsDefaultShipping(true);
        }
        $billing->setCustomerAddressData($customerBillingData);
        // Add billing address to quote since customer Data Object does not hold address information
        $quote->addCustomerAddress($customerBillingData);

        // If customer is created, set customerId for address to avoid create more address when checkout
        if ($customerId = $quote->getCustomerId()) {
            $quote->getBillingAddress()->setCustomerId($customerId);
            if (!$quote->isVirtual()) {
                $quote->getShippingAddress()->setCustomerId($customerId);
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function validateAddressBeforeSubmit(\Magento\Quote\Model\Quote $quote)
    {
        /** Remove address validation */
        if (!$quote->isVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
        $quote->getBillingAddress()->setShouldIgnoreValidation(true);
        return $this;
    }

    /**
     * @return bool
     */
    public function isCheckedRegister()
    {
        return $this->_isCheckedRegister;
    }

    /**
     * @param bool $isCheckedRegister
     */
    public function setIsCheckedRegister($isCheckedRegister)
    {
        $this->_isCheckedRegister = $isCheckedRegister;
    }
}
