/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'MGS_OSCheckout/js/model/checkout-data-resolver',
        'MGS_OSCheckout/js/model/payment-service',
        'mage/translate'
    ],
    function (ko,
              $,
              Component,
              quote,
              stepNavigator,
              additionalValidators,
              DataResolver,
              PaymentService) {
        'use strict';

        DataResolver.resolveDefaultPaymentMethod();

        return Component.extend({
            defaults: {
                template: 'MGS_OSCheckout/payment'
            },
            isLoading: PaymentService.isLoading,
            errorValidationMessage: ko.observable(false),

            initialize: function () {
                var self = this;

                this._super();

                stepNavigator.steps.removeAll();

                additionalValidators.registerValidator(this);

                quote.paymentMethod.subscribe(function () {
                    self.errorValidationMessage(false);
                });

                return this;
            },

            validate: function () {
                if (!quote.paymentMethod()) {
                    this.errorValidationMessage($.mage.__('Please specify a payment method.'));

                    return false;
                }

                return true;
            }
        });
    }
);
