/**
 * @author MGS Team
 * @copyright Copyright (c) 2019 Amasty (http://www.magesolution.com)
 * @package MGS_Fbuilder
 */
define([
    'underscore',
    'Magento_Ui/js/grid/massactions',
    'uiRegistry',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (_, Massactions, registry, utils, Collapsible, confirm, alert, $t) {
    'use strict';

    return Massactions.extend({
         /**
         * Default action callback. Sends selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            if(action.type && action.type.indexOf('amasty') == 0){
                selections['action'] = action.type;
            }
            console.log(action.url);
            utils.submit({
                url: action.url,
                data: selections
            });
        },

        applyMassaction: function (parent, action) {
            var data = this.getSelections(),
                action,
                callback;
            action   = this.getAction(action.type);

            var fileElement = jQuery('.action-submenu._active .amasty-file-form input:visible, .action-submenu._active .amasty-file-form select:visible');
            var value = fileElement.length? fileElement[fileElement.length - 1].value : null;

            if (!value) {
                alert({
                    content: 'Required field is empty.'
                });

                return this;
            }

            if (!data.total || !value) {
                alert({
                    content: this.noItemsMsg
                });

                return this;
            }
            var me = this;
            callback = function(){me.massactionCallback(action, data)};

            action.confirm ?
                this._confirm(action, callback) :
                callback();
        },

        massactionCallback: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            var fileElement = jQuery('.action-submenu._active .amasty-file-form input:visible, .action-submenu._active .amasty-file-form select:visible');
            if(fileElement.length){
                selections['amasty_file_field'] = fileElement[fileElement.length - 1].value;
            }

            selections['action'] = action.type;

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            console.log(action.url);
            utils.submit({
                url: action.url,
                data: selections
            });
        }

    });
});
