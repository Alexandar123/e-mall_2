/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define([
    'Magento_Checkout/js/view/shipping-address/address-renderer/default',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (Component, shippingRateService) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MGS_OSCheckout/address/shipping/address-renderer/default'
        },

        /** Set selected customer shipping address  */
        selectAddress: function () {
            if (!this.isSelected()) {
                this._super();

                shippingRateService.estimateShippingMethod();
            }
        }
    });
});
