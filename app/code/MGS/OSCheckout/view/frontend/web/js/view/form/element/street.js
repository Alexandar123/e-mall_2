/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'MGS_OSCheckout/js/model/address/google-auto-complete'
], function (Component, googleAutoComplete) {
    'use strict';

    return Component.extend({

        googleAutocomplete: null,

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super()
                .initAutocomplete();

            return this;
        },

        initAutocomplete: function () {
            var fieldsetName = this.parentName.split('.').slice(0, -1).join('.');

            switch (window.checkoutConfig.mageConfig.autocomplete.type) {
                case 'google':
                    this.googleAutocomplete = new googleAutoComplete(this.uid, fieldsetName);
                    break;
                case 'pca':
                    break;
                default :
                    break;
            }

            return this;
        }
    });
});
