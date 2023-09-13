<?php
namespace Dynamic\Postcode\Plugin;

class LayoutProcessor
{
    /**
     * @param LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $result
    ) {
        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode'] = $this->getConfig();

        // if (isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'])) 
        // {
        //     foreach ($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => $payment) 
        //     {                
        //         $paymentCode = 'billingAddress'.str_replace('-form','',$key);
        //         $exclude = ['before-place-order', 'paypal-method-extra-content', 'paypal-captcha', 'braintree-recaptcha'];
        //         if(!in_array($key, $exclude)){
        //             $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']['postcode'] = $this->getbillingConfig($paymentCode);
        //         }
        //     } 

        // } 

        /*MGS checkout billing address*/
        if (isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['billingAddress']['children']['billing-address-fieldset']['children'])) 
        {
            $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['billingAddress']['children']['billing-address-fieldset']['children']['postcode'] = $this->getMgsbillingConfig();

        }
        return $result;
    }

    /**
     * @return $field
     */
    private function getConfig()
    {
        $field = [
            'component' => 'Dynamic_Postcode/js/postcode',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'postcode'
            ],
            'label' => 'Postcode',
            'value' => '',
            'dataScope' => 'shippingAddress.postcode',
            'provider' => 'checkoutProvider',
            // 'sortOrder' => 80,
            'customEntry' => null,
            'visible' => true,
            'options' => [ ],
            // 'filterBy' => [
            //     'target' => '${ $.provider }:${ $.parentScope }.country_id',
            //     'field' => 'country_id'
            // ],
            'validation' => [
                'required-entry' => true
            ],
            'id' => 'postcode',
            'imports' => [
                'initialOptions' => 'index = checkoutProvider:dictionaries.postcode',
                'setOptions' => 'index = checkoutProvider:dictionaries.postcode'
            ]
        ];


        return $field;
    }
    /**
     * @return $field
     */
    private function getMgsbillingConfig()
    {
        $field = [
            'component' => 'Dynamic_Postcode/js/postcode',
            'config' => [
                'customScope' => 'billingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'postcode'
            ],
            'label' => 'Postcode',
            'value' => '',
            'dataScope' => 'billingAddress.postcode',
            'provider' => 'checkoutProvider',
            // 'sortOrder' => 80,
            'customEntry' => null,
            'visible' => true,
            'options' => [ ],
            // 'filterBy' => [
            //     'target' => '${ $.provider }:${ $.parentScope }.country_id',
            //     'field' => 'country_id'
            // ],
            'validation' => [
                'required-entry' => true
            ],
            'id' => 'postcode',
            'imports' => [
                'initialOptions' => 'index = checkoutProvider:dictionaries.postcode',
                'setOptions' => 'index = checkoutProvider:dictionaries.postcode'
            ]
        ];


        return $field;
    }
    private function getbillingConfig($addressType)
    {
        $field = [
            'component' => 'Dynamic_Postcode/js/postcode',
            'config' => [
                'customScope' => $addressType,
                // 'customEntry' => $addressType.'.postcode',

                // 'customScope' => 'billingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                // 'id' => 'postcode'
            ],
            'label' => 'Postcode',
            'dataScope' => $addressType.'.postcode',
            // 'dataScope' => 'billingAddress.postcode',
            'provider' => 'checkoutProvider',
            // 'sortOrder' => 80,

            'visible' => true,
            'options' => [ ],
            // 'filterBy' => [
            //     'target' => '${ $.provider }:${ $.parentScope }.country_id',
            //     'field' => 'country_id'
            // ],
            'validation' => [
                'required-entry' => true
            ],
            'id' => 'postcode',
            'imports' => [
                'initialOptions' => 'index = checkoutProvider:dictionaries.postcode',
                'setOptions' => 'index = checkoutProvider:dictionaries.postcode'
            ]
        ];


        return $field;
    }
}