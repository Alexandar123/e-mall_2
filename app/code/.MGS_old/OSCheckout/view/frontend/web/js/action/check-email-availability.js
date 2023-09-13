/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define([
    'mage/storage',
    'MGS_OSCheckout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote'
], function (storage, resourceUrlManager, quote) {
    'use strict';

    return function (email) {
        return storage.post(
            resourceUrlManager.getUrlForCheckIsEmailAvailable(quote),
            JSON.stringify({
                customerEmail: email
            }),
            true
        );
    };

});
