define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
], function ($, wrapper, quote, shippingFields) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, container) {

            var shippingAddress = quote.shippingAddress(),
                shippingPostcode = $("#shipping-new-address-form [name = 'postcode'] option:selected"),
                shippingPostcodeValue = shippingPostcode.text();

            shippingAddress.postcode = shippingPostcodeValue;

            return originalAction(container);
        });
    };
});