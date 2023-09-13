<?php
/**
 * RegisterSubmitSuccess
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Observer;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Model\Order\CustomerManagement;

class RegisterSubmitSuccess implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @type \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @type \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @type \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @type \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @type \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @type CustomerManagement
     */
    protected $customerManagement;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param CustomerManagement $customerManagement
     */
    public function __construct(
        Session                    $checkoutSession,
        AccountManagementInterface $accountManagement,
        Url                        $customerUrl,
        ManagerInterface           $messageManager,
        CustomerSession            $customerSession,
        SubscriberFactory          $subscriberFactory,
        CustomerManagement         $customerManagement
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->accountManagement = $accountManagement;
        $this->_customerUrl = $customerUrl;
        $this->messageManager = $messageManager;
        $this->_customerSession = $customerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerManagement = $customerManagement;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @type \Magento\Quote\Model\Quote $quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        $data = $this->checkoutSession->getData();
        if (isset($data['register']) && $data['register']
            && isset($data['password']) && $data['password']
        ) {
            $customer = $this->customerManagement->create($order->getId());

            /* Set customer Id for address */
            if ($customer->getId()) {
                $quote->getBillingAddress()->setCustomerId($customer->getId());
                if ($shippingAddress = $quote->getShippingAddress()) {
                    $shippingAddress->setCustomerId($customer->getId());
                }
            }

            if ($customer->getId() &&
                $this->accountManagement->getConfirmationStatus($customer->getId())
                === AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED) {
                $url = $this->_customerUrl->getEmailConfirmationUrl($customer->getEmail());
                $this->messageManager->addSuccessMessage(
                // @codingStandardsIgnoreStart
                    __(
                        'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                        $url
                    )
                // @codingStandardsIgnoreEnd
                );
            } else {
                $this->_customerSession->loginById($customer->getId());
            }
        }

        if (isset($data['is_subscribed']) && $data['is_subscribed']) {
            if (!$this->_customerSession->isLoggedIn()) {
                $subscribedEmail = $quote->getBillingAddress()->getEmail();
            } else {
                $customer = $this->_customerSession->getCustomer();
                $subscribedEmail = $customer->getEmail();
            }

            try {
                $this->subscriberFactory->create()
                    ->subscribe($subscribedEmail);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There is an error while subscribing for newsletter.'));
            }
        }

        $this->checkoutSession->unsData();
    }

    /**
     * Retrieve cookie manager
     *
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        return ObjectManager::getInstance()->get(PhpCookieManager::class);
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        return ObjectManager::getInstance()->get(CookieMetadataFactory::class);
    }
}
