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
            if(($("#"+id).val() == 'image')||($("#"+id).val() == undefined)) {
                $('div[data-index="video_big_type"]').hide();
                $('div[data-index="video_big_id"]').hide();
                $('.image_big').show();
            } else if($("#"+id).val() == 'video') {
                $('div[data-index="video_big_type"]').show();
                $('div[data-index="video_big_id"]').show();
                $('.image_big').hide();
            }
        },
    });
});
