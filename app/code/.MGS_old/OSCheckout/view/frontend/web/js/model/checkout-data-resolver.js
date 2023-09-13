/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define(
    [
        'Magento_Checkout/js/checkout-data'
    ],
    function (checkoutData) {
        'use strict';

        return {

            /**
             * Set default shipping method to local storage
             */
            resolveDefaultShippingMethod: function () {
                if (!checkoutData.getSelectedShippingRate() && window.checkoutConfig.selectedShippingRate) {
                    checkoutData.setSelectedShippingRate(window.checkoutConfig.selectedShippingRate);
                }
            },

            /**
             * Set default payment method to local storage
             */
            resolveDefaultPaymentMethod: function () {
                if (!checkoutData.getSelectedPaymentMethod() && window.checkoutConfig.selectedPaymentMethod) {
                    checkoutData.setSelectedPaymentMethod(window.checkoutConfig.selectedPaymentMethod);
                }
            }
        }
    }
);
