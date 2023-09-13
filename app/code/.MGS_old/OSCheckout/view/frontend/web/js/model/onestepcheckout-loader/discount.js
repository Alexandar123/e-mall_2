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
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, quote) {
        'use strict';
        var totals = quote.getTotals(),
            couponCode = ko.observable(null);
        if (totals()) {
            couponCode(totals()['coupon_code']);
        }
        return {
            isLoading: ko.observable(false),
            isAppliedCoupon: ko.observable(couponCode() != null),
            /**
             * Start full page loader action
             */
            startLoader: function () {
                this.isLoading(true);
            },
            /**
             * Stop full page loader action
             */
            stopLoader: function () {
                this.isLoading(false);
            }
        };
    }
);
