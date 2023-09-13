/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */
define(
    [
        'Magento_CheckoutAgreements/js/view/checkout-agreements',
        'Magento_Checkout/js/model/payment/additional-validators',
        'MGS_OSCheckout/js/model/agreement/agreement-validator'
    ],
    function (Component, additionalValidators, agreementValidator) {
        'use strict';

        additionalValidators.registerValidator(agreementValidator);
        return Component.extend({});
    }
);
