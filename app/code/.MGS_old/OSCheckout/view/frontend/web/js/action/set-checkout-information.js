/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define(
    [
        'Magento_Checkout/js/model/shipping-save-processor',
        'MGS_OSCheckout/js/model/checkout'
    ],
    function (shippingSaveProcessor, Processor) {
        'use strict';

        shippingSaveProcessor.registerProcessor('onestepcheckout', Processor);

        return function () {
            return shippingSaveProcessor.saveShippingInformation('onestepcheckout');
        }
    }
);
