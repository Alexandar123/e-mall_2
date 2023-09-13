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
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data'
    ],
    function ($,
              _,
              Component,
              quote,
              customer,
              checkoutData) {
        'use strict';

        var isEnableGeoIp = window.checkoutConfig.mageConfig.geoIpOptions.isEnableGeoIp,
            geoIpData = window.checkoutConfig.mageConfig.geoIpOptions.geoIpData;
        return Component.extend({

            initialize: function () {
                this.initGeoIp();
                this._super();
                return this;
            },
            initGeoIp: function () {
                if (isEnableGeoIp) {
                    if (!quote.isVirtual()) {

                        /**
                         * Set Geo Ip data to shippingAddress
                         */
                        if ((!customer.isLoggedIn() && checkoutData.getShippingAddressFromData() == null)
                            || (customer.isLoggedIn() && checkoutData.getNewCustomerShippingAddress() == null)) {
                            checkoutData.setShippingAddressFromData(geoIpData);
                        }
                    } else {

                        /**
                         * Set Geo Ip data to billingAddress
                         */
                        if ((!customer.isLoggedIn() && checkoutData.getBillingAddressFromData() == null)
                            || (customer.isLoggedIn() && checkoutData.setNewCustomerBillingAddress() == null)) {
                            checkoutData.setBillingAddressFromData(geoIpData);
                        }
                    }
                }
            }
        });
    }
);
