define(["jquery",
    "ko",
    "uiComponent",
    "uiRegistry",
    'MGS_OSCheckout/js/model/one-step-checkout-data',

], function ($, ko, Component, uiRegistry, OneStepCheckoutData) {
    "use strict";
    var cacheKey = 'order_comment',
        isVisible = OneStepCheckoutData.getData(cacheKey) ? true : false;
    return Component.extend({
        defaults: {
            template: 'MGS_OSCheckout/order-comment'
        },
        orderCommentValue: ko.observable(),
        isVisible: ko.observable(isVisible),

        initialize: function () {
            var self = this;
            this.customerNote = null;
            this._super();

            uiRegistry.async("checkout.sidebar.summary.comment.")(
                function (customerNote) {
                    this.customerNote = customerNote;
                    uiRegistry.async('checkout.osc.ajax')(
                        function (ajax) {
                            ajax.addMethod(
                                'params',
                                'customerNote',
                                this.paramsHandler.bind(this)
                            );
                        }.bind(this));

                }.bind(this));

            this.orderCommentValue(OneStepCheckoutData.getData(cacheKey));
            this.orderCommentValue.subscribe(function (newValue) {
                OneStepCheckoutData.setData(cacheKey, newValue);
                self.isVisible(true);
            });

            return this;


        },

        paramsHandler: function () {
            var response = false;

            if (this.customerNote.value().length > 0) {
                response = {
                    "customerNote": this.customerNote.value()
                };
            }

            return response;
        }

    });

});

