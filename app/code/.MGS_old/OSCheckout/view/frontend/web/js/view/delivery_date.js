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
        'ko',
        'uiComponent',
        'MGS_OSCheckout/js/model/one-step-checkout-data',
        'jquery/ui',
        'jquery/jquery-ui-timepicker-addon'
    ],
    function ($, ko, Component, OneStepCheckoutData) {
        'use strict';
        var cacheKey = 'deliveryTime',
            isVisible = OneStepCheckoutData.getData(cacheKey) ? true : false;
        var cacheKeyHouseSecurityCode = 'houseSecurityCode';

        return Component.extend({
            defaults: {
                template: 'MGS_OSCheckout/delivery-time'
            },
            houseSecurityCodeValue: ko.observable(),
            deliveryTimeValue: ko.observable(),
            isVisible: ko.observable(isVisible),
            initialize: function () {
                this._super();
                var self = this;
                ko.bindingHandlers.datepicker = {
                    init: function (element) {
                        var dateFormat = window.checkoutConfig.mageConfig.deliveryTimeOptions.deliveryTimeFormat,
                            daysOff = window.checkoutConfig.mageConfig.deliveryTimeOptions.deliveryTimeOff,
                            options = {
                                minDate: 0,
                                showButtonPanel: false,
                                dateFormat: dateFormat,
                                showOn: 'both',
                                buttonText: '',
                                beforeShowDay: function (date) {
                                    if (!daysOff)
                                        return [true];

                                    return [daysOff.indexOf(date.getDay()) === -1];
                                }
                            };
                        $(element).datetimepicker(options);
                    }
                };
                this.deliveryTimeValue(OneStepCheckoutData.getData(cacheKey));
                this.deliveryTimeValue.subscribe(function (newValue) {
                    OneStepCheckoutData.setData(cacheKey, newValue);
                    self.isVisible(true);
                });
                //House Security Code
                this.houseSecurityCodeValue(OneStepCheckoutData.getData(cacheKeyHouseSecurityCode));
                this.houseSecurityCodeValue.subscribe(function (newValue) {
                    OneStepCheckoutData.setData(cacheKeyHouseSecurityCode, newValue);
                });
                return this;
            },
            removeDeliveryTime: function () {
                if (OneStepCheckoutData.getData(cacheKey) && OneStepCheckoutData.getData(cacheKey) != null) {
                    OneStepCheckoutData.setData(cacheKey, '');
                    $("#onestepcheckout-delivery-time").attr('value', '');
                    this.isVisible(false);
                }
            },
            canUseHouseSecurityCode: function () {
                if (!window.checkoutConfig.mageConfig.deliveryTimeOptions.houseSecurityCode) {
                    return true;
                }
                return false;
            }
        });
    }
);
