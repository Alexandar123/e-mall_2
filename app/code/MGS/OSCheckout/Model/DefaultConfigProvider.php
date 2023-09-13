<?php
/**
 * DefaultConfigProvider
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Model;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Cms\Block\Block;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\UrlInterface;
use Magento\GiftMessage\Model\CompositeConfigProvider;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use MGS\OSCheckout\Helper\Data;

class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @type \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $giftMessageConfigProvider;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var QuoteItemRepository
     */
    protected $quoteItemRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var Block
     */
    protected $cmsBlock;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * DefaultConfigProvider constructor.
     * @param CheckoutSession $checkoutSession
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param CompositeConfigProvider $configProvider
     * @param QuoteItemRepository $quoteItemRepository
     * @param StockRegistryInterface $stockRegistry
     * @param ModuleManager $moduleManager
     * @param Data $dataHelper
     * @param Block $cmsBlock
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CheckoutSession                   $checkoutSession,
        PaymentMethodManagementInterface  $paymentMethodManagement,
        ShippingMethodManagementInterface $shippingMethodManagement,
        CompositeConfigProvider           $configProvider,
        QuoteItemRepository               $quoteItemRepository,
        StockRegistryInterface            $stockRegistry,
        ModuleManager                     $moduleManager,
        Data                              $dataHelper,
        Block                             $cmsBlock,
        StoreManagerInterface             $storeManager
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->giftMessageConfigProvider = $configProvider;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->stockRegistry = $stockRegistry;
        $this->moduleManager = $moduleManager;
        $this->dataHelper = $dataHelper;
        $this->cmsBlock = $cmsBlock;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        if (!$this->dataHelper->isOnePage()) {
            return [];
        }

        $output = [
            'shippingMethods' => $this->getShippingMethods(),
            'selectedShippingRate' => !empty($existShippingMethod = $this->checkoutSession->getQuote()->getShippingAddress()->getShippingMethod())
                ? $existShippingMethod : $this->dataHelper->getDefaultShippingMethod(),
            'paymentMethods' => $this->getPaymentMethods(),
            'selectedPaymentMethod' => $this->dataHelper->getDefaultPaymentMethod(),
            'mageConfig' => $this->getMageConfig()
        ];

        return $output;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getMageConfig()
    {
        return [
            'addressFields' => $this->dataHelper->getAddressHelper()->getAddressFields(),
            'register' => [
                'dataPasswordMinLength' => $this->dataHelper->getConfigValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH),
                'dataPasswordMinCharacterSets' => $this->dataHelper->getConfigValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER)
            ],
            'autocomplete' => [
                'type' => $this->dataHelper->getAutoDetectedAddress(),
                'google_default_country' => $this->dataHelper->getGoogleSpecificCountry(),
            ],
            'showBillingAddress' => $this->dataHelper->getShowBillingAddress(),
            'allowGuestCheckout' => $this->dataHelper->getAllowGuestCheckout($this->checkoutSession->getQuote()),
            'deliveryTimeOptions' => [
                'deliveryTimeFormat' => $this->dataHelper->getDeliveryTimeFormat(),
                'deliveryTimeOff' => $this->dataHelper->getDeliveryTimeOff(),
            ],
            'giftMessageOptions' => array_merge_recursive($this->giftMessageConfigProvider->getConfig(), [
                'isEnableGiftMessageItems' => $this->dataHelper->isEnableGiftMessageItems()
            ]),
            'isUsedMaterialDesign' => $this->dataHelper->isUsedMaterialDesign(),
            'geoIpOptions' => [
                'isEnableGeoIp' => $this->dataHelper->isEnableGeoIP(),
                'geoIpData' => $this->dataHelper->getAddressHelper()->getGeoIpData()
            ],
            'isShowItemListToggle' => $this->dataHelper->isShowItemListToggle(),
            'show_toc' => $this->dataHelper->getShowTOC(),
            'qtyIncrements' => $this->getItemQtyIncrement(),
            'experian_api_key' => $this->dataHelper->getExperianKey(),
            'newsletterDefault' => $this->dataHelper->getNewletter()
        ];
    }


    private function getPaymentMethods()
    {
        $paymentMethods = [];
        $quote = $this->checkoutSession->getQuote();
        if (!$quote->getIsVirtual()) {
            foreach ($this->paymentMethodManagement->getList($quote->getId()) as $paymentMethod) {
                $paymentMethods[] = [
                    'code' => $paymentMethod->getCode(),
                    'title' => $paymentMethod->getTitle()
                ];
            }
        }

        return $paymentMethods;
    }

    private function getShippingMethods()
    {
        $methodLists = $this->shippingMethodManagement->getList($this->checkoutSession->getQuote()->getId());
        foreach ($methodLists as $key => $method) {
            $methodLists[$key] = $method->__toArray();
        }

        return $methodLists;
    }

    /**
     * Retrieve quote item data
     *
     * @return array
     */
    private function getItemQtyIncrement()
    {
        $itemQty = [];

        try {
            $quoteId = $this->checkoutSession->getQuote()->getId();
            if ($quoteId) {
                /** @var array $quoteItems */
                $quoteItems = $this->quoteItemRepository->getList($quoteId);

                /** @var Item $item */
                foreach ($quoteItems as $item) {
                    $stockItem = $this->stockRegistry->getStockItem($item->getProduct()->getId(), $item->getStore()->getWebsiteId());
                    if ($stockItem->getEnableQtyIncrements() && $stockItem->getQtyIncrements()) {
                        $itemQty[$item->getId()] = $stockItem->getQtyIncrements() ?: 1;
                    }
                }
            }
        } catch (\Exception $e) {
            $itemQty = [];
        }

        return $itemQty;
    }

}
