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
        'Magento_Checkout/js/model/quote',
        'Magento_SalesRule/js/action/set-coupon-code',
        'Magento_SalesRule/js/action/cancel-coupon',
        'MGS_OSCheckout/js/model/onestepcheckout-loader/discount'
    ],
    function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction, discountLoader) {
        'use strict';
        var totals = quote.getTotals(),
            couponCode = ko.observable(null),
            isApplied = discountLoader.isAppliedCoupon;

        if (totals()) {
            couponCode(totals()['coupon_code']);
        }
        return Component.extend({
            defaults: {
                template: 'MGS_OSCheckout/review/discount'
            },
            isBlockLoading: discountLoader.isLoading,
            couponCode: couponCode,

            /**
             * Applied flag
             */
            isApplied: isApplied,

            /**
             * Coupon code application procedure
             */
            apply: function () {
                if (this.validate()) {
                    setCouponCodeAction(couponCode(), isApplied);
                }
            },

            /**
             * Cancel using coupon
             */
            cancel: function () {
                if (this.validate()) {
                    couponCode('');
                    cancelCouponAction(isApplied);
                }
            },

            /**
             * Coupon form validation
             *
             * @returns {Boolean}
             */
            validate: function () {
                var form = '#discount-form';

                return $(form).validation() && $(form).validation('isValid');
            }
        });
    }
);
