define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.SwatchRenderer', widget, {
			_sortAttributes: function () {
				
				var $productContainer = $(this.options.selectorProduct).parent();
				
				if (typeof $productContainer.attr('data-container') !== typeof undefined && $productContainer.attr('data-container') !== false) {
					var $dataContainer = $productContainer.attr('data-container');
					var $arrDataContainer = $dataContainer.split('product-');
					this.options.mediaCallback += 'view_mode/' + $arrDataContainer[1] + '/';
					
					if($('#product-container').length){
						var $dataDimention = 'data-dimension-'+$arrDataContainer[1];
						if (typeof $('#product-container').attr($dataDimention) !== typeof undefined && $('#product-container').attr($dataDimention) !== false) {
							this.options.mediaCallback += 'dimention/' + $('#product-container').attr($dataDimention) + '/';
						}
					}
				}
				
				this.options.jsonConfig.attributes = _.sortBy(this.options.jsonConfig.attributes, function (attribute) {
					return parseInt(attribute.position, 10);
				});
			}
        });

        return $.mage.SwatchRenderer;
    }
});