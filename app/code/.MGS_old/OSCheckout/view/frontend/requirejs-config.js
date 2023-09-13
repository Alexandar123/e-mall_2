var config = {};
if (window.location.href.indexOf('onestepcheckout') !== -1) {
    config = {
        map: {
            '*':
                {
                    'Magento_Checkout/js/model/shipping-rate-service': 'MGS_OSCheckout/js/model/shipping/shipping-rate-service',
                    'Magento_Checkout/js/model/shipping-rates-validator': 'MGS_OSCheckout/js/model/shipping/shipping-rates-validator',
                    'Magento_CheckoutAgreements/js/model/agreements-assigner': 'MGS_OSCheckout/js/model/agreement/agreements-assigner'
                },
            'MGS_OSCheckout/js/model/shipping/shipping-rates-validator': {
                'Magento_Checkout/js/model/shipping-rates-validator': 'Magento_Checkout/js/model/shipping-rates-validator'
            },
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'Magento_Checkout/js/model/full-screen-loader': 'MGS_OSCheckout/js/model/one-step-checkout-loader'
            },
            'Magento_Checkout/js/action/set-billing-address': {
                'Magento_Checkout/js/model/full-screen-loader': 'MGS_OSCheckout/js/model/one-step-checkout-loader'
            },
            'Magento_SalesRule/js/action/set-coupon-code': {
                'Magento_Checkout/js/model/full-screen-loader': 'MGS_OSCheckout/js/model/onestepcheckout-loader/discount'
            },
            'Magento_SalesRule/js/action/cancel-coupon': {
                'Magento_Checkout/js/model/full-screen-loader': 'MGS_OSCheckout/js/model/onestepcheckout-loader/discount'
            },
            'MGS_OSCheckout/js/model/one-step-checkout-loader': {
                'Magento_Checkout/js/model/full-screen-loader': 'Magento_Checkout/js/model/full-screen-loader'
            },

        },
        config: {
            mixins: {
                'Magento_Braintree/js/view/payment/method-renderer/paypal': {
                    'MGS_OSCheckout/js/view/payment/braintree-paypal-mixins': true
                },
                'Magento_Checkout/js/action/place-order': {
                    'MGS_OSCheckout/js/action/place-order-mixins': true
                },
                'Magento_Paypal/js/action/set-payment-method': {
                    'MGS_OSCheckout/js/model/set-payment-method-mixin': true
                }
            }
        }
    };

    if (window.location.href.indexOf('#') !== -1) {
        window.history.pushState("", document.title, window.location.pathname);
    }
}
