<?php
/**
 * Index
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Controller\Index;

use Magento\Checkout\Controller\Onepage;

class Index extends Onepage
{
    /**
     * @type \MGS\OSCheckout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_checkoutHelper = $this->_objectManager->get(\MGS\OSCheckout\Helper\Data::class);
        if ($this->_checkoutHelper->isEnabled() == false) {
            $this->messageManager->addError(__('One step checkout is turned off.'));

            return $this->resultRedirectFactory->create()->setPath('checkout');
        }

        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getErrors() || !$quote->validateMinimumAmount()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $this->_customerSession->regenerateId();
        $this->_objectManager->get('Magento\Checkout\Model\Session')->setCartWasUpdated(false);
        $this->getOnepage()->initCheckout();

        $this->initDefaultMethods($quote);

        $resultPage = $this->resultPageFactory->create();
        $checkoutTitle = $this->_checkoutHelper->getCheckoutTitle();
        $resultPage->getConfig()->getTitle()->set($checkoutTitle);
        $resultPage->getConfig()->setPageLayout($this->_checkoutHelper->isShowHeaderFooter() ? '1column' : 'checkout');

        return $resultPage;
    }

    /**
     * @param $quote
     * @return bool
     */
    public function initDefaultMethods($quote)
    {
        $shippingAddress = $quote->getShippingAddress();

        $defaultCountryId = $this->getDefaultCountryFromLocale();
        if (!$shippingAddress->getCountryId()) {
            $geoIpData = $this->_checkoutHelper->getAddressHelper()->getGeoIpData();
            if (!empty($geoIpData)) {
                $defaultCountryId = $geoIpData['country_id'];
            } elseif (!empty($this->_checkoutHelper->getDefaultCountryId())) {
                $defaultCountryId = $this->_checkoutHelper->getDefaultCountryId();
            }
            $shippingAddress->setCountryId($defaultCountryId)->setCollectShippingRates(true);
        }
        $method = null;

        try {
            $availableMethods = $this->_objectManager->get(\Magento\Quote\Api\ShippingMethodManagementInterface::class)
                ->getList($quote->getId());
            if (sizeof($availableMethods) == 1) {
                $method = array_shift($availableMethods);
            } elseif (!$shippingAddress->getShippingMethod() && sizeof($availableMethods)) {
                $defaultMethod = array_filter($availableMethods, [$this, 'filterMethod']);
                if (sizeof($defaultMethod)) {
                    $method = array_shift($defaultMethod);
                }
            }

            if ($method) {
                $methodCode = $method->getCarrierCode() . '_' . $method->getMethodCode();
                $this->getOnepage()->saveShippingMethod($methodCode);
            }

            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $method
     * @return bool
     */
    public function filterMethod($method)
    {
        $defaultShippingMethod = $this->_checkoutHelper->getDefaultShippingMethod();
        $methodCode = $method->getCarrierCode() . '_' . $method->getMethodCode();
        if ($methodCode == $defaultShippingMethod) {
            return true;
        }

        return false;
    }

    public function getDefaultCountryFromLocale()
    {
        $locale = $this->_objectManager->get(\Magento\Framework\Locale\Resolver::class)
            ->getLocale();

        return substr($locale, strrpos($locale, "_") + 1);
    }
}
