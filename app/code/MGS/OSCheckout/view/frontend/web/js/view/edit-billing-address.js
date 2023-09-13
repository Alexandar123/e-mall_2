/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define([
    'ko',
    'Magento_Customer/js/model/address-list'
], function (ko, addressList) {
    'use strict';

    return function (address) {
        addressList().some(function (currentAddress, index, addresses) {
            if (currentAddress.getKey() === address.getKey()) {
                addressList.replace(currentAddress, address);
            }
        });

        addressList.valueHasMutated();

        return address;
    };
});
