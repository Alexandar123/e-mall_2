/**
 * @copyright Copyright © 2020 magesolution. All rights reserved.
 * @author    @copyright Copyright (c) 2014 magesolution (<https://www.magesolution.com>)
 * @license <https://www.magesolution.com/license-agreement.html>
 * @Author: ndthien0912<ndthien0912@gmail.com>
 * @github: <https://github.com/magesolution>
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/region',
    'mageUtils',
    'uiLayout'
], function (_, Component, utils, layout) {
    'use strict';
    var template = window.checkoutConfig.mageConfig.isUsedMaterialDesign ? 'MGS_OSCheckout/form/field' : '${ $.$data.template }';
    var inputNode = {
        parent: '${ $.$data.parentName }',
        component: 'Magento_Ui/js/form/element/abstract',
        template: template,
        elementTmpl: 'MGS_OSCheckout/form/element/input',
        provider: '${ $.$data.provider }',
        name: '${ $.$data.index }_input',
        dataScope: '${ $.$data.customEntry }',
        customScope: '${ $.$data.customScope }',
        sortOrder: '${ $.$data.sortOrder }',
        displayArea: 'body',
        label: '${ $.$data.label }'
    };

    return Component.extend({
        initInput: function () {
            layout([utils.template(_.extend(inputNode, {additionalClasses: this.additionalClasses}), this)]);

            return this;
        }
    });
});

