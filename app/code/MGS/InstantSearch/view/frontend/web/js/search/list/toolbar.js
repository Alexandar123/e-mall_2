/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui"

], function($) {
    /**
     * SearchListToolbarForm Widget - this widget is setting cookie and submitting form according to toolbar controls
     */
    $.widget('mage.searchListToolbarForm', {

        options: {
            limitControl: '[data-role="limiter"]',
            limit: 'list_limit',
            limitDefault: '12',
            url: ''
        },

        _create: function () {
            this._bind($(this.options.limitControl), this.options.limit, this.options.limitDefault);
        },

        _bind: function (element, paramName, defaultValue) {
            if (element.is("select")) {
                element.on('change', {paramName: paramName, default: defaultValue}, $.proxy(this._processSelect, this));
            }
        },
        
        _processSelect: function (event) {
            this.changeUrl(
                event.data.paramName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value,
                event.data.default
            );
        },

        changeUrl: function (paramName, paramValue, defaultValue) {
            var decode = window.decodeURIComponent;
            var urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters;
            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined
                    ? decode(parameters[1].replace(/\+/g, '%20'))
                    : '';
            }
            paramData[paramName] = paramValue;
            if (paramValue == defaultValue) {
                delete paramData[paramName];
            }
            paramData = $.param(paramData);

            location.href = baseUrl + (paramData.length ? '?' + paramData : '');
        }
    });

    return $.mage.searchListToolbarForm;
});
