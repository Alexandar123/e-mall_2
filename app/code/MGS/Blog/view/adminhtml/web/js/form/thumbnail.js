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
                $('div[data-index="video_thumbnail_type"]').hide();
                $('div[data-index="video_thumb_id"]').hide();
                $('.thumbnail').show();
            } else if($("#"+id).val() == 'video') {
                $('div[data-index="video_thumbnail_type"]').show();
                $('div[data-index="video_thumb_id"]').show();
                $('.thumbnail').hide();
                }
            }
        });
    });
