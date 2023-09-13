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
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/totals',
        'MGS_OSCheckout/js/model/payment-service'
    ],
    function ($, shippingService, totalService, paymentService) {
        'use strict';

        var blockLoader = {
            shipping: {
                queue: 0,
                service: shippingService
            },
            payment: {
                queue: 0,
                service: paymentService
            },
            total: {
                queue: 0,
                service: totalService
            }
        };

        return {
            getServices: function (blocks) {
                var services = {
                    payment: blockLoader.payment.service,
                    total: blockLoader.total.service
                };

                if (typeof blocks !== 'undefined') {
                    services = {};
                    $.each(blocks, function (index, block) {
                        if (blockLoader.hasOwnProperty(block)) {
                            services[block] = blockLoader[block].service;
                        }
                    });
                }

                return services;
            },

            /**
             * Start full page loader action
             */
            startLoader: function (blocks) {
                var services = this.getServices(blocks);
                $.each(services, function (index, service) {
                    blockLoader[index].queue += 1;
                    service.isLoading(true);
                });
            },

            /**
             * Stop full page loader action
             */
            stopLoader: function (blocks) {
                var services = this.getServices(blocks);
                $.each(services, function (index, service) {
                    blockLoader[index].queue -= 1;
                    if (blockLoader[index].queue == 0) {
                        service.isLoading(false);
                    }
                });
            }
        };
    }
);
