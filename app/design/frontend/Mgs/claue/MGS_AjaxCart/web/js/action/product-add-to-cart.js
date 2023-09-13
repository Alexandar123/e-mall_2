require([
    'jquery',
    'Magento_Ui/js/modal/modal',
], function($, modal) {
    jQuery('#product-addtocart-button').on('click', function(){
        var form = jQuery('#product_addtocart_form');
        var isValid = form.valid();
        if(isValid){
            jQuery(this).addClass('disabled tocart-loading');
            var data = form.serializeArray();
            data.push({
                name: 'ajax',
                value: 1
            });
            jQuery.ajax({
                url: form.attr('action'),
                data: jQuery.param(data),
                type: 'post',
                dataType: 'json',
                beforeSend: function(xhr, options) {
                    jQuery('#mgs-ajax-loading').show();
                },
                success: function(response, status) {
                    if (status == 'success') {
                        if (response.ui) {
                            jQuery('#mgs-ajax-loading').hide();
                            if (response.animationType == 'popup') {
                                $('.page.messages').hide();

                                if($(document).find('.ajaxCartForm').length){
                                    $(document).find('.ajaxCartForm .modal-header .action-close').trigger('click');
                                }
                                $('body').append('<div id="popup_ajaxcart_success" class="popup__main popup--result"></div>');

                                var options =
                                {
                                    type: 'popup',
                                    modalClass: "success-ajax--popup viewBox",
                                    responsive: true,
                                    innerScroll: true,
                                    title: false,
                                    buttons: false
                                };

                                var popup = modal(options, $('#popup_ajaxcart_success'));
                                $('#popup_ajaxcart_success').html(response.ui + response.related);
                                $('#popup_ajaxcart_success').trigger('contentUpdated');
                                $('#popup_ajaxcart_success').modal('openModal').on('modalclosed', function() {
                                    $('#popup_ajaxcart_success').parents('.success-ajax--popup').remove();
                                });
                                setTimeout(function () {
                                    $('.page.messages .message').hide();
                                    $('.page.messages').show();
                                }, 2000);
                            } else if(response.animationType == 'flycart') {
                                var $animatedObject = jQuery('<div class="flycart-animated-add" style="position: absolute;z-index: 99999;">'+response.image+'</div>');

                                var left = $_this.offset().left;
                                var top = $_this.offset().top;

                                $animatedObject.css({top: top-1, left: left-1});
                                jQuery('html').append($animatedObject);

                                jQuery('#footer-cart-trigger').addClass('active');
                                jQuery('#footer-mini-cart').slideDown(300);

                                var gotoX = jQuery("#fixed-cart-footer").offset().left + 20;
                                var gotoY = jQuery("#fixed-cart-footer").offset().top;

                                if($(document).find('.ajaxCartForm').length){
                                    $(document).find('.ajaxCartForm .modal-header .action-close').trigger('click');
                                }

                                $animatedObject.animate({
                                    opacity: 0.6,
                                    left: gotoX,
                                    top: gotoY
                                }, 2000,
                                function () {
                                    $animatedObject.fadeOut('fast', function () {
                                        $animatedObject.remove();
                                        jQuery('html').removeClass('add-item-success');
                                    });
                                });
                            } else {
                                $('.product_quickview_content').modal('closeModal');
                                $("header.page-header").addClass("show-sticky-menu");
                                $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog("open");
                                setTimeout(function(){
                                    $("header.page-header").removeClass("show-sticky-menu");
                                    $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog("close");
                                },5000);
                                $("#product-addtocart-button > span").text('Add to cart');
                            }

                        } else {
                            window.location.reload(true);
                        }
                    }
                }
            });
            return false;
        }
    });
});
