/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */


/*jshint browser:true jquery:true*/
/*global alert*/

define(['jquery', 'Magento_Catalog/js/price-utils', 'mage/template', 'jquery/ui', 'Magento_Ui/js/modal/modal'],
    function ($, priceUtil, mageTemplate) {

    "use strict";
	
	return function(widget){
        $.widget('smileEs.rangeSlider', widget, {
            _onSliderChange: function (ev, ui) {
                this.from = ui.values[0];
				this.to   = ui.values[1];
				this._refreshDisplay();
                this._applyRange();
            }
        })
    }

    return $.smileEs.rangeSlider;
});
