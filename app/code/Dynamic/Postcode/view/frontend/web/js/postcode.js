/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver',
    'jquery',
    'mage/utils/wrapper',
    'mage/template',
    'mage/validation',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'jquery/ui'
], function (_, registry, Select, defaultPostCodeResolver, $) {
    'use strict';

    return Select.extend({
        initialize: function () {
            this._super()
            this.update();
            return this;
        },
        /**
         * @param {String} value
         */
        update: function () {
            var options = window.checkoutConfig.postcodeData.postOpt,
            postcodeOptions = [];
            $.each(options, function (index, postcodeOptionValue) {
                    var jsonObject = {
                        value: postcodeOptionValue.value,
                        title: postcodeOptionValue.label,
                        label: postcodeOptionValue.label
                    };                    
                    postcodeOptions.push(jsonObject);
            });
            this.setOptions(postcodeOptions);
        }
    });
});