/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define([
    'MGS_OSCheckout/js/model/address/type/google'
], function (googleAutoComplete) {
    'use strict';

    var addressType = {
        billing: 'checkout.steps.billing-step.billingAddress.billing-address-fieldset',
        shipping: 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset'
    };

    return {
        register: function (type) {
            if (addressType.hasOwnProperty(type)) {
                switch (window.checkoutConfig.mageConfig.autocomplete.type) {
                    case 'google':
                        new googleAutoComplete(addressType[type]);
                        break;
                    case 'pca':
                        break;
                    default :
                        break;
                }
            }
        }
    };
});


