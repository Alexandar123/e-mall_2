<?php
/**
 * LayoutProcessor
 *
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 */

namespace MGS\OSCheckout\Block\LayoutOnestepCheckout;

use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\Component\Form\AttributeMapper;
use MGS\OSCheckout\Helper\Data;
use MGS\OSCheckout\Helper\Address;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var Address
     */
    private $addressDataHelper;
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var \Magento\Ui\Component\Form\AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var \Magento\Checkout\Block\Checkout\AttributeMerger
     */
    protected $merger;

    /**
     * @var \Magento\Customer\Model\Options
     */
    private $options;

    /**
     * @type \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * LayoutProcessor constructor.
     * @param CheckoutSession $checkoutSession
     * @param Data $dataHelper
     * @param Address $addressDataHelper
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     */
    public function __construct(
        ScopeConfigInterface          $scopeConfig,
        CheckoutSession               $checkoutSession,
        Data                          $dataHelper,
        Address                       $addressDataHelper,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper               $attributeMapper,
        AttributeMerger               $merger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->dataHelper = $dataHelper;
        $this->addressDataHelper = $addressDataHelper;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process($jsLayout)
    {
        if (!$this->dataHelper->isOnePage()) {
            return $jsLayout;
        }

        /** Billing address fields */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['billingAddress']['children']['billing-address-fieldset']['children'])) {
            $fields = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['billingAddress']
            ['children']['billing-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['billingAddress']
            ['children']['billing-address-fieldset']['children'] = $this->getAddressFieldset($fields, 'billingAddress');
        }

        /** Shipping address fields */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
            $fields = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children'] = $this->getAddressFieldset($fields, 'shippingAddress');

            if ($this->dataHelper->isEnabled()) {
                $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress'];
                $shippingConfig['component'] = 'MGS_OSCheckout/js/view/shipping-method';
                $shippingConfig['children']['customer-email']['component'] = 'MGS_OSCheckout/js/view/form/element/email';
            }
        }

        if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['children']['summary']['children']['comments'])) {
            $fields = $jsLayout['components']['checkout']['children']['sidebar']['children']['children']['summary']['children']['comments'];
            $jsLayout['components']['checkout']['children']['sidebar']['children']['children']['summary']['children'] = $this->getAddressFieldset($fields, 'summary');
        }

        /** Remove billing address in payment method content */
        $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children'];
        foreach ($fields as $code => $field) {
            if ($field['component'] == 'Magento_Checkout/js/view/billing-address') {
                unset($fields[$code]);
            }
        }

        /** Remove billing customer email if quote is not virtual */
        if (!$this->checkoutSession->getQuote()->isVirtual()) {
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['billingAddress']
                ['children']['customer-email']);
        }


        return $jsLayout;
    }

    /**
     * Get address fieldset for shipping/billing address
     *
     * @param $fields
     * @param $type
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressFieldset($fields, $type)
    {
        $elements = $this->getAddressAttributes();

        $systemAttribute = $elements['default'];
        if (sizeof($systemAttribute)) {
            $attributesToConvert = [
                'prefix' => [$this->getOptions(), 'getNamePrefixOptions'],
                'suffix' => [$this->getOptions(), 'getNameSuffixOptions'],
            ];
            $systemAttribute = $this->convertElementsToSelect($systemAttribute, $attributesToConvert);
            $fields = $this->merger->merge(
                $systemAttribute,
                'checkoutProvider',
                $type,
                $fields
            );
        }

        $customAttribute = $elements['custom'];
        if (sizeof($customAttribute)) {
            $fields = $this->merger->merge(
                $customAttribute,
                'checkoutProvider',
                $type . '.custom_attributes',
                $fields
            );
        }

        $this->addCustomerAttribute($fields, $type);
        $this->addAddressOption($fields);


        return $fields;
    }

    /**
     * Move Billing Address After Shipping Address
     *
     * @param array $jsLayout
     * @return array
     */
    private function moveBillingAddress($jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset'])) {

            $jsLayout = $this->removeBillingAddressInPaymentMethod($jsLayout);

            $elements = $this->getAddressAttributes();

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['billing-address'] = $this->getCustomBillingAddressComponent($elements);

            $billingFields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['billing-address']['children']['form-fields']['children'];

            foreach ($billingFields as $fieldName => $shippingField) {
                $fieldConfig = $this->addressDataHelper->getConfigValue($fieldName);
                if (isset($fieldConfig['order'])) {
                    if ($fieldConfig['order'] != '') {
                        $billingFields[$fieldName]['sortOrder'] = $fieldConfig['order'];
                    }
                }
                if (isset($fieldConfig['validation'])) {
                    $validations = explode(',', $fieldConfig['validation']);
                    if (empty($billingFields[$fieldName]['validation'])) {
                        $billingFields[$fieldName]['validation'] = [];
                    }
                    foreach ($validations as $validation) {
                        $billingFields[$fieldName]['validation'][$validation] = 1;
                    }
                }
            }

            foreach (self::DISABLE_FIELDS as $field) {
                $configPath = sprintf(self::CONFIG_DISABLE_FIELD_PATH, $field);
                if ($this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE)) {
                    unset($billingFields[$field]);
                }
            }
        }

        return $jsLayout;
    }

    private function removeBillingAddressInPaymentMethod($jsLayout)
    {
        unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']);
        return $jsLayout;
    }

    /**
     * Add customer attribute like gender, dob, taxvat
     *
     * @param $fields
     * @param $type
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addCustomerAttribute(&$fields, $type)
    {
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            'customer_account_create'
        );
        $addressElements = [];
        foreach ($attributes as $attribute) {
            if (!$this->dataHelper->getAddressHelper()->isCustomerAttributeVisible($attribute)) {
                continue;
            }
            $addressElements[$attribute->getAttributeCode()] = $this->attributeMapper->map($attribute);
        }

        if (sizeof($addressElements)) {
            $fields = $this->merger->merge(
                $addressElements,
                'checkoutProvider',
                $type . '.custom_attributes',
                $fields
            );
        }

        foreach ($fields as $code => &$field) {
            if (isset($field['label'])) {
                $field['label'] = __($field['label']);
            }
        }

        return $this;
    }


    private function addAddressOption(&$fields)
    {
        $fieldPosition = $this->dataHelper->getAddressHelper()->getAddressFieldPosition();

        $arrayField = [];
        $allFieldSection = $this->dataHelper->getAddressHelper()->getSortedField(false);
        foreach ($allFieldSection as $allfield) {
            foreach ($allfield as $field) {
                $arrayField[] = $field->getAttributeCode();
            }
        }

        $this->rewriteFieldStreet($fields);

        foreach ($fields as $code => &$field) {
            $fieldConfig = isset($fieldPosition[$code]) ? $fieldPosition[$code] : [];
            if (!sizeof($fieldConfig)) {
                if (in_array($code, ['country_id'])) {
                    $field['config']['additionalClasses'] = "col-hidden";
                    continue;
                } elseif (in_array($code, $arrayField)) {
                    unset($fields[$code]);
                }
            } else {
                $oriClasses = isset($field['config']['additionalClasses']) ? $field['config']['additionalClasses'] : '';

                $field['sortOrder'] = (isset($field['sortOrder']) && !in_array($code, $arrayField)) ? $field['sortOrder'] : $fieldConfig['sortOrder'];
                if ($code == 'dob') {
                    $field['options'] = ['yearRange' => '-120y:c+nn', 'maxDate' => '-1d', 'changeMonth' => true, 'changeYear' => true];
                }

                $this->rewriteTemplate($field);
            }
        }

        return $this;
    }

    /**
     * Change template to remove valueUpdate = 'keyup'
     *
     * @param $field
     * @param string $template
     * @return $this
     */
    public function rewriteTemplate(&$field, $template = 'MGS_OSCheckout/form/element/input')
    {
        if (isset($field['type']) && $field['type'] == 'group') {
            foreach ($field['children'] as $key => &$child) {
                if ($key == 0 && in_array('street', explode('.', $field['dataScope'])) && $this->dataHelper->isGoogleHttps()) {
                    $this->rewriteTemplate($child, 'MGS_OSCheckout/form/element/street');
                    continue;
                }
                $this->rewriteTemplate($child);
            }
        } elseif (isset($field['config']['elementTmpl']) && $field['config']['elementTmpl'] == "ui/form/element/input") {
            $field['config']['elementTmpl'] = $template;
            if ($this->dataHelper->isUsedMaterialDesign()) {
                $field['config']['template'] = 'MGS_OSCheckout/form/field';
            }
        }

        return $this;
    }

    /**
     * Change template street when enable material design
     * @param $fields
     * @return $this
     */
    public function rewriteFieldStreet(&$fields)
    {

        if ($this->dataHelper->isUsedMaterialDesign()) {
            $fields['country_id']['config']['template'] = 'MGS_OSCheckout/form/field';
            $fields['region_id']['config']['template'] = 'MGS_OSCheckout/form/field';
            foreach ($fields['street']['children'] as $key => $value) {
                $fields['street']['children'][0]['label'] = $fields['street']['label'];
                $fields['street']['children'][$key]['config']['template'] = 'MGS_OSCheckout/form/field';
            }
            $fields['street']['config']['fieldTemplate'] = 'MGS_OSCheckout/form/field';
            unset($fields['street']['label']);
        }

        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAddressAttributes()
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [
            'custom' => [],
            'default' => []
        ];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            $element = $this->attributeMapper->map($attribute);
            if (isset($element['label'])) {
                $label = $element['label'];
                $element['label'] = __($label);
            }

            ($attribute->getIsUserDefined()) ?
                $elements['custom'][$code] = $element :
                $elements['default'][$code] = $element;
        }

        return $elements;
    }

    /**
     * @return \Magento\Customer\Model\Options
     */
    private function getOptions()
    {
        if (!is_object($this->options)) {
            $this->options = ObjectManager::getInstance()->get(\Magento\Customer\Model\Options::class);
        }

        return $this->options;
    }

    /**
     * convert Elements To Select
     */
    private function convertElementsToSelect($elements, $attributesToConvert)
    {
        $codes = array_keys($attributesToConvert);
        foreach (array_keys($elements) as $code) {
            if (!in_array($code, $codes)) {
                continue;
            }
            $options = call_user_func($attributesToConvert[$code]);
            if (!is_array($options)) {
                continue;
            }
            $elements[$code]['dataType'] = 'select';
            $elements[$code]['formElement'] = 'select';

            foreach ($options as $key => $value) {
                $elements[$code]['options'][] = [
                    'value' => $key,
                    'label' => $value,
                ];
            }
        }

        return $elements;
    }

    /**
     * get Custom Billing Address Component
     */
    public function getCustomBillingAddressComponent($elements)
    {

        $fields = [
            'component' => 'MGS_OSCheckout/js/view/billing-address',
            'displayArea' => 'billing-address',
            'provider' => 'checkoutProvider',
            'deps' => ['checkoutProvider'],
            'dataScopePrefix' => 'billingAddress',
            'children' => [
                'form-fields' => [
                    'component' => 'uiComponent',
                    'displayArea' => 'additional-fieldsets',
                    'children' => $this->merger->merge(
                        $elements,
                        'checkoutProvider',
                        'billingAddress',
                        [
                            'country_id' => [
                                'sortOrder' => 115,
                            ],
                            'region' => [
                                'visible' => false,
                            ],
                            'region_id' => [
                                'component' => 'Magento_Ui/js/form/element/region',
                                'config' => [
                                    'template' => 'ui/form/field',
                                    'elementTmpl' => 'ui/form/element/select',
                                    'customEntry' => 'billingAddress.region',
                                ],
                                'validation' => [
                                    'required-entry' => true,
                                ],
                                'filterBy' => [
                                    'target' => '${ $.provider }:${ $.parentScope }.country_id',
                                    'field' => 'country_id',
                                ],
                            ],
                            'postcode' => [
                                'component' => 'Magento_Ui/js/form/element/post-code',
                                'validation' => [
                                    'required-entry' => true,
                                ],
                            ],
                            'company' => [
                                'validation' => [
                                    'min_text_length' => 0,
                                ],
                            ],
                            'fax' => [
                                'validation' => [
                                    'min_text_length' => 0,
                                ],
                            ],
                            'telephone' => [
                                'config' => [
                                    'tooltip' => [
                                        'description' => __('For delivery questions.'),
                                    ],
                                ],
                            ],
                        ]
                    ),
                ],
            ],
        ];
        foreach ($elements as $attributeCode => $attribute) {
            if (!empty($attribute['is_user_defined'])) {
                $fields['children']['form-fields']['children'][$attributeCode]['config']['customScope'] = 'billingAddress.custom_attributes';
                $fields['children']['form-fields']['children'][$attributeCode]['dataScope'] = 'billingAddress.custom_attributes.' . $attributeCode;

            }
        }
        return $fields;
    }
}
