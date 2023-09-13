/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, storage) {
    'use strict';

    var cacheKey = 'one-step-checkout-data';

    var getData = function () {
        return storage.get(cacheKey)();
    };

    var saveData = function (checkoutData) {
        storage.set(cacheKey, checkoutData);
    };
    return {
        setData: function (key, data) {
            var obj = getData();
            obj[key] = data;
            saveData(obj);
        },
        getData: function (key) {
            if (typeof key === 'undefined') {
                return getData();
            }
            return getData()[key];
        }
    }
});
