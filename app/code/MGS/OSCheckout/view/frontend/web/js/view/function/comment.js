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
        'uiComponent',
        'MGS_OSCheckout/js/model/one-step-checkout-data'
    ],
    function (ko, Component, OneStepCheckoutData) {
        "use strict";

        var cacheKey = 'deliveryComment';

        return Component.extend({
            defaults: {
                template: 'MGS_OSCheckout/review/comment'
            },
            commentValue: ko.observable(),
            initialize: function () {
                this._super();

                this.commentValue(OneStepCheckoutData.getData(cacheKey));

                this.commentValue.subscribe(function (newValue) {
                    OneStepCheckoutData.setData(cacheKey, newValue);
                });

                return this;
            }
        });
    }
);
