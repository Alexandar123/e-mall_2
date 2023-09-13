<?php
/**
 * Data
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

namespace MGS\OSCheckout\Helper;

use MGS\OSCheckout\Model\System\Config\Source\ComponentPosition;
use Exception;
use Magento\Backend\App\Config;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Serialize;

class Data extends AbstractHelper
{

    const CONFIG_MODULE_PATH = 'MGS_OSCheckout';
    const CONFIG_PATH_DISPLAY = 'display_configuration';
    const CONFIG_PATH_DESIGN = 'design_configuration';
    const CONFIG_PATH_GEOIP = 'geoip_configuration';
    const SORTED_FIELD_POSITION = 'MGS_OSCheckout/field/position';
    const XML_PATH_DISABLE_GUEST_CHECKOUT = 'catalog/downloadable/disable_guest_checkout';
    protected $configModule;
    /**
     * @var bool onestepcheckout Method Register
     */
    protected $_flagMethodRegister = false;

    /**
     * @var Address
     */
    protected $_addressHelper;


    /**
     * @type array
     */
    protected $_data = [];

    /**
     * @type StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @type ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Config
     */
    protected $backendConfig;

    /**
     * @var array
     */
    protected $isArea = [];

    /**
     * @var JsonHelper
     */
    protected $json;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context                $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface  $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->json = ObjectManager::getInstance()->get(JsonSerializer::class);

        parent::__construct($context);
    }

	/**
     * @param array $value
     *
     * @return bool|false|string
     */
    public function jsonEncodeData($value)
    {
        try {
            return $this->json->serialize($value);
        } catch (Exception $e) {
            return '{}';
        }
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigGeneral('enabled', $storeId);
    }

    public function getAddressHelper()
    {
        if (!$this->_addressHelper) {
            $this->_addressHelper = $this->objectManager->get(Address::class);
        }

        return $this->_addressHelper;
    }

    /**
     * @param string $field
     * @param null $storeId
     * @return mixed
     */
    public function getModuleConfig($field = '', $storeId = null)
    {
        $field = ($field !== '') ? '/' . $field : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . $field, $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getConfigGeneral($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';
        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/general' . $code, $storeId);
    }

    /**
     * @param $field
     * @param null $scopeValue
     * @param string $scopeType
     * @return array|mixed
     */
    public function getConfigValue($field, $scopeValue = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if ($scopeValue === null && !$this->isArea()) {
            /** @var Config $backendConfig */
            if (!$this->backendConfig) {
                $this->backendConfig = $this->objectManager->get(\Magento\Backend\App\ConfigInterface::class);
            }
            return $this->backendConfig->getValue($field);
        }
        return $this->scopeConfig->getValue($field, $scopeType, $scopeValue);
    }

    /**
     * @param $name
     * @return null
     */
    public function getData($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setData($name, $value)
    {
        $this->_data[$name] = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrentUrl()
    {
        $model = $this->objectManager->get(UrlInterface::class);

        return $model->getCurrentUrl();
    }

    /**
     * @param $ver
     * @param string $operator
     * @return mixed
     */
    public function versionCompare($ver, $operator = '>=')
    {
        $productMetadata = $this->objectManager->get(ProductMetadataInterface::class);
        $version = $productMetadata->getVersion(); //will return the magento version

        return version_compare($version, $ver, $operator);
    }

    /**
     * @param $data
     * @return string
     */
    public function serialize($data)
    {
        if ($this->versionCompare('2.2.0')) {
            return self::jsonEncode($data);
        }

        return $this->getSerializeClass()->serialize($data);
    }

    /**
     * @param $string
     * @return mixed
     */
    public function unserialize($string)
    {
        if ($this->versionCompare('2.2.0')) {
            return self::jsonDecode($string);
        }

        return $this->getSerializeClass()->unserialize($string);
    }

    /**
     * @param mixed $valueToEncode
     * @return string
     */
    public static function jsonEncode($valueToEncode)
    {
        try {
            $encodeValue = self::getJsonHelper()->jsonEncode($valueToEncode);
        } catch (Exception $e) {
            $encodeValue = '{}';
        }

        return $encodeValue;
    }

    /**
     * @param string $encodedValue
     * @return mixed
     */
    public static function jsonDecode($encodedValue)
    {
        try {
            $decodeValue = self::getJsonHelper()->jsonDecode($encodedValue);
        } catch (Exception $e) {
            $decodeValue = [];
        }

        return $decodeValue;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isArea(Area::AREA_ADMINHTML);
    }

    /**
     * @param string $area
     * @return mixed
     */
    public function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {
            /** @var State $state */
            $state = $this->objectManager->get(\Magento\Framework\App\State::class);

            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }

    /**
     * @param $path
     * @param array $arguments
     *
     * @return mixed
     */
    public function createObject($path, $arguments = [])
    {
        return $this->objectManager->create($path, $arguments);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function getObject($path)
    {
        return $this->objectManager->get($path);
    }

    /**
     * @return JsonHelper|mixed
     */
    public static function getJsonHelper()
    {
        return ObjectManager::getInstance()->get(JsonHelper::class);
    }

    /**
     * @return mixed
     */
    protected function getSerializeClass()
    {
        return $this->objectManager->get(Serialize::class);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isOnePage($store = null)
    {
        $moduleEnable = $this->isEnabled($store);
        $isModule = ($this->_request->getRouteName() == 'onestepcheckout');

        return $moduleEnable && $isModule;
    }

    public function isFlagMethodRegister()
    {
        return $this->_flagMethodRegister;
    }

    /**
     * @param bool $flag
     */
    public function setFlagMethodRegister($flag)
    {
        $this->_flagMethodRegister = $flag;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getCheckoutTitle($store = null)
    {
        return $this->getConfigGeneral('title', $store) ?: 'One Step Checkout';
    }

    /************************ General Configuration *************************/
    /**
     * @param null $store
     * @return mixed
     */
    public function getCheckoutDescription($store = null)
    {
        return $this->getConfigGeneral('description', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultCountryId($store = null)
    {
        return $this->objectManager->get('Magento\Directory\Helper\Data')->getDefaultCountry($store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultShippingMethod($store = null)
    {
        return $this->getConfigGeneral('default_shipping_method', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultPaymentMethod($store = null)
    {
        return $this->getConfigGeneral('default_payment_method', $store);
    }

    /**
     * @param $quote
     * @param null $store
     * @return bool
     */
    public function getAllowGuestCheckout($quote, $store = null)
    {
        $allowGuestCheckout = boolval($this->getConfigGeneral('allow_guest_checkout', $store));

        if ($this->scopeConfig->isSetFlag(
            self::XML_PATH_DISABLE_GUEST_CHECKOUT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )
        ) {
            foreach ($quote->getAllItems() as $item) {
                if (($product = $item->getProduct())
                    && $product->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    return false;
                }
            }
        }

        return $allowGuestCheckout;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getShowBillingAddress($store = null)
    {
        return $this->getConfigGeneral('show_billing_address', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getGoogleApiKey($store = null)
    {
        return $this->getConfigGeneral('google_api_key', $store);
    }

    public function getExperianKey($store = null)
    {
        return $this->getConfigGeneral('experian_api_key', $store);
    }

    public function getGoogleSpecificCountry($store = null)
    {
        return $this->getConfigGeneral('google_specific_country', $store);
    }

    public function isGoogleHttps()
    {
        $isEnable = ($this->getAutoDetectedAddress() == 'google');

        return $isEnable && $this->_request->isSecure();
    }

    public function getAutoDetectedAddress($store = null)
    {
        return $this->getConfigGeneral('auto_detect_address', $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isDisableAuthentication($store = null)
    {
        return !$this->getDisplayConfig('enabled_login_link', $store);
    }

    /********************************** Display Configuration *********************
     *
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getDisplayConfig($code = '', $store = null)
    {
        $code = $code ? self::CONFIG_PATH_DISPLAY . '/' . $code : self::CONFIG_PATH_DISPLAY;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isShowItemListToggle($store = null)
    {
        return !!$this->getDisplayConfig('show_item_list_toggle', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function disabledPaymentCoupon($store = null)
    {
        return $this->getDisplayConfig('show_coupon', $store) != ComponentPosition::SHOW_IN_PAYMENT;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function disabledReviewCoupon($store = null)
    {
        return $this->getDisplayConfig('show_coupon', $store) != ComponentPosition::SHOW_IN_REVIEW;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isDisabledComment($store = null)
    {
        return !$this->getDisplayConfig('enabled_comments', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isDisabledOrderComment($store = null)
    {
        return !$this->getDisplayConfig('enabled_order_comments', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function enabledDeliveryTime($store = null)
    {
        return !$this->getDisplayConfig('enabled_delivery_date', $store);
    }

    public function enabledChecknewletter($store = null)
    {
        return !$this->getDisplayConfig('checked_newsletter', $store);
    }

    /**
     * @param null $store
     * @return string 'dd/mm/yy'|'mm/dd/yy'|'yy/mm/dd'
     */
    public function getDeliveryTimeFormat($store = null)
    {
        $deliveryTimeFormat = $this->getDisplayConfig('delivery_time_format', $store);

        return $deliveryTimeFormat ?: \MGS\OSCheckout\Model\System\Config\Source\DeliveryTime::DAY_MONTH_YEAR;
    }

    /**
     * @param null $store
     * @return bool|mixed
     */
    public function getDeliveryTimeOff($store = null)
    {
        return $this->getDisplayConfig('delivery_time_off', $store);
    }

    public function getNewletter($store = null)
    {
        return $this->getDisplayConfig('checked_newsletter', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getShowTOC($store = null)
    {
        return true;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isEnabledTOC($store = null)
    {
        return $this->getDisplayConfig('show_toc', $store) != ComponentPosition::NOT_SHOW;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function disabledPaymentTOC($store = null)
    {
        return $this->getDisplayConfig('show_toc', $store) != ComponentPosition::SHOW_IN_PAYMENT;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function disabledReviewTOC($store = null)
    {
        return $this->getDisplayConfig('show_toc', $store) != ComponentPosition::SHOW_IN_REVIEW;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isDisabledGiftMessage($store = null)
    {
        return false;
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isEnableGiftMessageItems($store = null)
    {
        return false;
    }

    /***************************** Design Configuration *****************************
     *
     * @param string $code
     * @param null $store
     * @return mixed
     */
    public function getDesignConfig($code = '', $store = null)
    {
        $code = $code ? self::CONFIG_PATH_DESIGN . '/' . $code : self::CONFIG_PATH_DESIGN;

        return $this->getModuleConfig($code, $store);
    }

    public function isUsedMaterialDesign()
    {
        return $this->getDesignConfig('page_design') == 'material' ? true : false;
    }

    /***************************** GeoIP Configuration *****************************
     *
     * @param null $store
     * @return mixed
     */
    public function isEnableGeoIP($store = null)
    {
        return boolval($this->getModuleConfig(self::CONFIG_PATH_GEOIP . '/enable_geoip', $store));
    }

    /***************************** Compatible Modules *****************************
     *
     * @return bool
     */
    public function isEnabledMultiSafepay()
    {
        return $this->_moduleManager->isOutputEnabled('MultiSafepay_Connect');
    }

    /**
     * @return mixed
     */
    public function getCurrentThemeId()
    {
        return $this->getConfigValue(\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID);
    }

    public function isShowHeaderFooter($store = null)
    {
        return $this->getDisplayConfig('display_foothead', $store);
    }
}
