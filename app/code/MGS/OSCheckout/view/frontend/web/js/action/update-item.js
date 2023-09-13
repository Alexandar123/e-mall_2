/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'MGS_OSCheckout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/shipping-service',
        'MGS_OSCheckout/js/model/one-step-checkout-loader',
        'Magento_Customer/js/customer-data'
    ],
    function (quote,
              resourceUrlManager,
              storage,
              errorProcessor,
              customer,
              methodConverter,
              paymentService,
              shippingService,
              OneStepCheckoutLoader,
              customerData) {
        'use strict';

        var itemUpdateLoader = ['shipping', 'payment', 'total'];

        return function (payload) {
            var isRemove = !('item_qty' in payload);

            if (!customer.isLoggedIn()) {
                payload.cart_id = quote.getQuoteId();
            }

            OneStepCheckoutLoader.startLoader(itemUpdateLoader);

            return storage.post(
                resourceUrlManager.getUrlForUpdateItemInformation(quote, isRemove),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                        return;
                    }
                    quote.setTotals(response.totals);
                    paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                    if (response.shipping_methods && !quote.isVirtual()) {
                        shippingService.setShippingRates(response.shipping_methods);
                    }
                    customerData.reload(['cart'], true);
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(
                function () {
                    OneStepCheckoutLoader.stopLoader(itemUpdateLoader);
                }
            );
        };
    }
);
