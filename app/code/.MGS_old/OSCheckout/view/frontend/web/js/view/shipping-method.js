/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define(
    [
        'jquery',
        'underscore',
        'Magento_Checkout/js/view/shipping',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/set-shipping-information',
        'MGS_OSCheckout/js/action/payment-total-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/shipping-rate-service',
        'Magento_Checkout/js/model/shipping-service',
        'MGS_OSCheckout/js/model/checkout-data-resolver',
        'MGS_OSCheckout/js/model/address/auto-complete',
        'Magento_Customer/js/model/address-list',
        'rjsResolver',
        'mage/translate'
    ],
    function ($,
              _,
              Component,
              quote,
              customer,
              setShippingInformationAction,
              getPaymentTotalInformation,
              stepNavigator,
              additionalValidators,
              checkoutData,
              selectBillingAddress,
              selectShippingAddress,
              addressConverter,
              shippingRateService,
              shippingService,
              DataResolver,
              addressAutoComplete,
              addressList,
              resolver) {
        'use strict';

        DataResolver.resolveDefaultShippingMethod();

        /** Set shipping methods to collection */
        shippingService.setShippingRates(window.checkoutConfig.shippingMethods);

        return Component.extend({
            defaults: {
                template: 'MGS_OSCheckout/shipping-method'
            },
            currentMethod: null,
            initialize: function () {
                this._super();
                if (!quote.shippingAddress() && addressList().length >= 1) {
                    selectShippingAddress(addressList()[0]);
                }

                stepNavigator.steps.removeAll();

                //shippingRateService.estimateShippingMethod();
                additionalValidators.registerValidator(this);

                resolver(this.afterResolveDocument.bind(this));

                return this;
            },

            initObservable: function () {
                this._super();

                quote.shippingMethod.subscribe(function (oldValue) {
                    this.currentMethod = oldValue;
                }, this, 'beforeChange');

                quote.shippingMethod.subscribe(function (newValue) {
                    var isMethodChange = ($.type(this.currentMethod) !== 'object') ? true : this.currentMethod.method_code;
                    if ($.type(newValue) === 'object' && (isMethodChange !== newValue.method_code)) {
                        setShippingInformationAction();
                    } else if (shippingRateService.isAddressChange) {
                        shippingRateService.isAddressChange = false;
                        getPaymentTotalInformation();
                    }
                }, this);

                return this;
            },

            afterResolveDocument: function () {
                addressAutoComplete.register('shipping');
            },

            validate: function () {
                if (quote.isVirtual()) {
                    return true;
                }

                var shippingMethodValidationResult = true,
                    shippingAddressValidationResult = true,
                    loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn();

                if (!quote.shippingMethod()) {
                    this.errorValidationMessage($.mage.__('Please specify a shipping method.'));

                    shippingMethodValidationResult = false;
                }

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');

                    if (this.source.get('shippingAddress.custom_attributes')) {
                        this.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    if (this.source.get('params.invalid')) {
                        shippingAddressValidationResult = false;
                    }

                    this.saveShippingAddress();
                }

                return shippingMethodValidationResult && shippingAddressValidationResult && emailValidationResult;
            },
            saveShippingAddress: function () {
                var shippingAddress = quote.shippingAddress(),
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        this.source.get('shippingAddress')
                    );

                //Copy form data to quote shipping address object
                for (var field in addressData) {
                    if (addressData.hasOwnProperty(field) &&
                        shippingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(shippingAddress[field], addressData[field])
                    ) {
                        shippingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' && !_.isEqual(shippingAddress[field], addressData[field])) {
                        shippingAddress = addressData;
                        break;
                    }
                }

                if (customer.isLoggedIn()) {
                    shippingAddress.save_in_address_book = 1;
                }
                selectShippingAddress(shippingAddress);
            },

            saveNewAddress: function () {
                this.source.set('params.invalid', false);
                if (this.source.get('shippingAddress.custom_attributes')) {
                    this.source.trigger('shippingAddress.custom_attributes.data.validate');
                }

                if (!this.source.get('params.invalid')) {
                    this._super();
                }

                if (!this.source.get('params.invalid')) {
                    shippingRateService.isAddressChange = true;
                    shippingRateService.estimateShippingMethod();
                }
            },

            getAddressTemplate: function () {
                return 'MGS_OSCheckout/address/shipping-address';
            }
        });
    }
);
