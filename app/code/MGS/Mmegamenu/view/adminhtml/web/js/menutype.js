define([
    'jquery',
    'Magento_Ui/js/form/element/select'
], function ($, Select) {
    'use strict';

    return Select.extend({
     
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input'
        },
        selectOption: function(id){
            if(($("#"+id).val() == 1)||($("#"+id).val() == undefined)) {
                $('#tab_category').show();
                $('#tab_static_contents').show();
                $('#tab_static_contentt').hide();
            } else if($("#"+id).val() == 2) {
                $('#tab_category').hide();
                $('#tab_static_contents').hide();
                $('#tab_static_contentt').show();
            }
        }
    });
});
