/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'priceUtils',
    'priceBox',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'zoom-images',
    'mgsslick', 
    'mgsowlcarousel',
    'magnificPopup'
], function ($, _, mageTemplate) {
    'use strict';
    
    return function (widget) {
        $.widget('mage.configurable', widget, {
            

            /**
             * Initialize tax configuration, initial settings, and options values.
             * @private
             */
            _initializeOptions: function () {
                var options = this.options,
                    gallery = $(options.mediaGallerySelector),
                    galleryTemplate = $('#mgs_template_layout').val(),
                    priceBoxOptions = $(this.options.priceHolderSelector).priceBox('option').priceConfig || null;

                if (priceBoxOptions && priceBoxOptions.optionTemplate) {
                    options.optionTemplate = priceBoxOptions.optionTemplate;
                }

                if (priceBoxOptions && priceBoxOptions.priceFormat) {
                    options.priceFormat = priceBoxOptions.priceFormat;
                }
                options.optionTemplate = mageTemplate(options.optionTemplate);

                options.settings = options.spConfig.containerId ?
                    $(options.spConfig.containerId).find(options.superSelector) :
                    $(options.superSelector);

                options.values = options.spConfig.defaultValues || {};
                options.parentImage = $('[data-role=base-image-container] img').attr('src');

                this.inputSimpleProduct = this.element.find(options.selectSimpleProduct);
                
                var currentImages = [];   
                
                $(".product.media .item-image").each(function( index ) {
                    var item = [];
                    var url_video = "";
                    var type = 'image';
                    
                    if($(this).find('.popup-video').length){
                        url_video = $(this).find('.popup-video').attr('href');
                    }else if($(this).find('.lb.video-link').length){
                        url_video = $(this).find('.lb.video-link').attr('href');
                    }
                    if(url_video){
                        type = 'external-video';
                    }
                    
                    item['zoom'] = $(this).attr('data-zoom');
                    item['full'] = $(this).find('.img-fluid').attr('src');
                    item['thumb'] = $(this).find('.img-fluid').attr('src');
                    item['media_type'] = type;
                    item['videoUrl'] = url_video;
                    currentImages.push(item);
                });
                
                options.mediaGalleryInitial = currentImages;
            },
            /**
             * Change displayed product image according to chosen options of configurable product
             * @private
             */
            _changeProductImage: function () {
                var images,
                    imagesToUpdate,
                    initialImages = this.options.mediaGalleryInitial,
                    zoomimg = $('#zoom_image').val(),
                    glr_layout = $('#glr_layout').val(),
                    lbox_image = $('#lbox_image').val(),
                    $_rtl = $('#rtl_theme').val();

                if (this.options.spConfig.images[this.simpleProduct]) {
                    images = $.extend(true, [], this.options.spConfig.images[this.simpleProduct]);
                }
                if (images) {
                    imagesToUpdate = images;
                }else {
                    imagesToUpdate = initialImages;
                }
                /* Update Gallery */
				if(glr_layout == 1 || glr_layout == 2){
					this.updateBaseImageList(imagesToUpdate);
				}else if(glr_layout == 3){
					this.updateFullOwl(imagesToUpdate);
				}else if(glr_layout == 4){
					this.updateBaseImageVertical(imagesToUpdate);
				}else if(glr_layout == 5){
					this.updateBaseImageHorizontal(imagesToUpdate);
				}else if(glr_layout == 6){
					this.updateBaseImageOwl(imagesToUpdate);
				}
				
				if(zoomimg == 1 && glr_layout != 6){
					this.zoomImage();
				}
				
				if(lbox_image == 1){
					this.lightBoxGallery();
				}
            },
            
            updateBaseImageList: function(imagesToUpdate) {
				var img_change = "";
				
				img_change = '<div class="gallery-list">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
				
				$(".product.media").html(img_change);
			},
			
			updateBaseImageOwl: function(imagesToUpdate) {
				var img_change = "";
				var view_type = $('#view_type').val();
				
				img_change = '<div id="owl-carousel-gallery" class="owl-carousel gallery-horizontal">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
				
				$(".product.media").html(img_change);
				
				var $_rtl = $('#rtl_theme').val();
				
				$('#owl-carousel-gallery').owlCarousel({
					items: 1,
					autoplay: false,
					lazyLoad: false,
					nav: true,
					dots: false,
					navText: ["<span></span>","<span></span>"],
					rtl: $_rtl,
				});
			},
			
			updateFullOwl: function(imagesToUpdate) {
				var img_change = "";
				var view_type = $('#view_type').val();
				
				img_change = '<div id="owl-carousel-gallery" class="owl-carousel gallery-5">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
				
				$(".product.media").html(img_change);
				
				if($('#zoom_image').val() == 1){
					$('#owl-carousel-gallery').on('initialized.owl.carousel', function(event) {
						$(".imgzoom").each(function( index ) {
							zoomElement(this);
						});
					});
				}
				
				var $_rtl = $('#rtl_theme').val();
				
				$('#owl-carousel-gallery').owlCarousel({
					items: $('#item-xl').val(),
					autoplay: false,
					lazyLoad: false,
					loop: true,
					nav: true,
					dots: false,
					navText: ["<span></span>","<span></span>"],
					rtl: $_rtl,
					responsive : {
						0 : {
							items : $('#item-xs').val()
						},
						576 : {
							items : $('#item-sm').val()
						},
						768 : {
							items : $('#item-md').val()
						},
						992 : {
							items : $('#item-lg').val()
						},
						1200 : {
							items : $('#item-xl').val()
						}
					}
				});
			},
			
			updateBaseImageHorizontal: function(imagesToUpdate) {
				var img_change = "";
				img_change = '<div class="horizontal-gallery">';
				
				img_change = img_change + '<div id="owl-carousel-gallery" class="owl-carousel gallery-horizontal">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
				
				if(imagesToUpdate.length > 1){
					img_change = img_change + '<div class="horizontal-thumbnail-wrapper"><div id="horizontal-thumbnail" class="owl-carousel horizontal-thumbnail">'+this.generateHtmlThumb(imagesToUpdate)+'</div></div>';
				}
				
				img_change = img_change + '</div>';
				
				$(".product.media").html(img_change);
				var $_rtl = $('#rtl_theme').val();
				
				$('#owl-carousel-gallery').owlCarousel({
					items: 1,
					autoplay: false,
					lazyLoad: false,
					nav: true,
					dots: false,
					navText: ["<span></span>","<span></span>"],
				});
				
				$('#owl-carousel-gallery').on('changed.owl.carousel', function(event) {
					var index = event.item.index;
					$('#horizontal-thumbnail .item-thumb').removeClass('active');
					$('#horizontal-thumbnail .item-thumb[data-owl='+index+']').addClass('active');
					$('#horizontal-thumbnail').trigger('to.owl.carousel', index);
				});
				var $_rtl = $('#rtl_theme').val();
				$('#horizontal-thumbnail').owlCarousel({
					items: 4,
					autoplay: false,
					lazyLoad: false,
					nav: true,
					dots: false,
					rtl: $_rtl,
					navText: ["<span></span>","<span></span>"],
					responsive:{
						0:{ items: 2 },
						576:{ items: 3 }, 
						992:{ items: 4 },
						1200:{ items: 4 }
					},
				});

				
				$('#horizontal-thumbnail .item-thumb').click(function(){
					$('#horizontal-thumbnail .item-thumb').removeClass('active');
					var position = $(this).attr('data-owl');
					$('#owl-carousel-gallery').trigger('to.owl.carousel', position);
					$(this).addClass('active');
				});
				
			},
			
			updateSingleSlide: function(imagesToUpdate) {
				var img_change = '<div class="container"><div class="product-thumbnail gallery-2">';
				
				img_change = img_change + '<div id="owl-carousel-gallery" class="owl-carousel gallery-horizontal">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
				
				if(imagesToUpdate.length > 1){
					img_change = img_change + '<div id="horizontal-thumbnail" class="owl-carousel horizontal-thumbnail">'+this.generateHtmlThumb(imagesToUpdate)+'</div>';
				}
				
				img_change = img_change + '</div></div>';
				
				$(".product.media").html(img_change);
				var $_rtl = $('#rtl_theme').val();
				$('#owl-carousel-gallery').owlCarousel({
					items: 1,
					autoplay: false,
					lazyLoad: false,
					nav: true,
					dots: false,
					navText: ["<span></span>","<span></span>"],
					rtl: $_rtl
				});
				
				$('#owl-carousel-gallery').on('changed.owl.carousel', function(event) {
					var index = event.item.index;
					$('#horizontal-thumbnail .item-thumb').removeClass('active');
					$('#horizontal-thumbnail .item-thumb[data-owl='+index+']').addClass('active');
					$('#horizontal-thumbnail').trigger('to.owl.carousel', index);
				});
				var $_rtl = $('#rtl_theme').val();
				$('#horizontal-thumbnail').owlCarousel({
					items: 4,
					autoplay: false,
					lazyLoad: false,
					nav: true,
					dots: false,
					rtl: $_rtl,
					navText: ["<span></span>","<span></span>"],
					responsive:{
						0:{ items: 2 },
						500:{ items: 3 },
						992:{ items: 4 },
						1200:{ items: 4 }
					},
				});
				
				$('#horizontal-thumbnail .item-thumb').click(function(){
					$('#horizontal-thumbnail .item-thumb').removeClass('active');
					var position = $(this).attr('data-owl');
					$('#owl-carousel-gallery').trigger('to.owl.carousel', position);
					$(this).addClass('active');
				});
				
			},
			
			updateBaseImageVertical: function(imagesToUpdate) {
				var img_change = "";
				if(imagesToUpdate.length > 1){	
					img_change = '<div class="vertical-gallery">';
				}else {
					img_change = '<div class="vertical-gallery no-thumb">';
				}
				
				if(imagesToUpdate.length > 1){	
					img_change = img_change + '<div id="vertical-thumbnail-wrapper"><div id="vertical-thumbnails" class="vertical-thumbnail">'+this.generateHtmlThumb(imagesToUpdate)+'</div></div>';
				}
				
				img_change = img_change + '<div id="owl-carousel-gallery" class="owl-carousel gallery-vertical">'+this.generateHtmlImage(imagesToUpdate)+'</div>';
					
				img_change = img_change + '</div>';
				
				$(".product.media").html(img_change);
				
				$('#owl-carousel-gallery').on('initialized.owl.carousel', function(event) {
					setTimeout(function(){
						var hs = $('#owl-carousel-gallery').height();
						$('.product.media').height(hs);
					}, 200);
					
				});
				var $_rtl = $('#rtl_theme').val();
				$('#owl-carousel-gallery').owlCarousel({
					items: 1,
					autoplay: false,
					lazyLoad: false,
					nav: true,
					dots: false,
					navText: ["<span></span>","<span></span>"],
					rtl: $_rtl
				});
				
				$('#vertical-thumbnails img').load(function(){
					setTimeout(function(){
						$('#vertical-thumbnails').not('.slick-initialized').slick({
							dots: false,
							arrows: true,
							vertical: true,
							slidesToShow: 3,
							slidesToScroll: 3,
							verticalSwiping: true,
							centerMode: true,
							prevArrow: '<span class="icon-angle-up"></span>',
							nextArrow: '<span class="icon-angle-down"></span>',
							responsive: [
								{
									breakpoint: 1199,
									settings: {
										slidesToShow: 2,
										slidesToScroll: 2
									}
								},
								{
									breakpoint: 768,
									settings: {
										slidesToShow: 3,
										slidesToScroll: 3
									}
								},
								{
									breakpoint: 600,
									settings: {
										slidesToShow: 2,
										slidesToScroll: 2
									}
								},
								{
									breakpoint: 360,
									settings: {
										slidesToShow: 1,
										slidesToScroll: 1
									}
								}
							]
						});
					}, 200);
				});
				
				$('#owl-carousel-gallery').on('changed.owl.carousel', function(event) {
					var index = event.item.index;
					$('#vertical-thumbnails .item-thumb').removeClass('active');
					$('#vertical-thumbnails .item-thumb[data-owl='+index+']').addClass('active');
					var wdw = $(window).width();
					var ci = imagesToUpdate.length;
					if(wdw >= 1199 && ci > 3) {
						$('#vertical-thumbnails').slick('slickGoTo', index);
					}else if(wdw < 1199 && wdw >= 768 && ci > 2){
						$('#vertical-thumbnails').slick('slickGoTo', index);
					}else if(wdw < 768 && wdw >= 600 && ci > 3){
						$('#vertical-thumbnails').slick('slickGoTo', index);
					}else if(wdw < 768 && wdw >= 600 && ci > 2){
						$('#vertical-thumbnails').slick('slickGoTo', index);
					}else if(wdw < 360){
						$('#vertical-thumbnails').slick('slickGoTo', index);
					}
				});
				
				$('#owl-carousel-gallery').on('resized.owl.carousel', function(event) {
					var hs = $('#owl-carousel-gallery').height();
					$('.product.media').height(hs);
				});
				
				$('#vertical-thumbnails .item-thumb').click(function(){
					$('#vertical-thumbnails .item-thumb').removeClass('active');
					var position = $(this).attr('data-owl');
					$('#owl-carousel-gallery').trigger('to.owl.carousel', position);
					$(this).addClass('active');
				});
			},
			
			updateOneImage: function(imagesToUpdate) {
				var img_change = "",
					lbox_image = $('#lbox_image').val();
				var $isVideo = false;
				if((imagesToUpdate[0].media_type == 'external-video' || imagesToUpdate[0].media_type == 'video' || imagesToUpdate[0].type == 'video') && imagesToUpdate[0].videoUrl != ""){
					$isVideo = true;
				}
				
				var $class = 'product item-image imgzoom';
				if($isVideo){
					$class = $class + ' item-image-video';
				}
				
				img_change = img_change + '<div class="'+$class+'" data-zoom="'+imagesToUpdate[0].zoom+'">';
				
				if($isVideo){
					img_change = img_change + '<div class="label-video">'+$.mage.__('Video')+'</div>';
				}
			
				if(lbox_image == 1){
					var href = imagesToUpdate[0].zoom;
					var cla = 'lb';
					if($isVideo){
						href = imagesToUpdate[0].videoUrl;
						cla = 'lb video-link';
					}
					img_change = img_change + '<a href="'+href+'" class="'+cla+'"><img class="img-fluid" src="'+imagesToUpdate[0].full+'" alt=""/></a>';
				}else {
					img_change = img_change + '<img class="img-fluid" src="'+imagesToUpdate[0].full+'" alt=""/>';
					if($isVideo){
						img_change = img_change + '<a target="_blank" class="popup-video" href="'+imagesToUpdate[0].videoUrl+'"><span class="ti-video-camera"></span></a>';
					}
				}
				
				img_change = img_change + '</div>';
				
				$(".product.media").html(img_change);
			},
			
			zoomImage: function(){
				$(".imgzoom").each(function( index ) {
					zoomElement(this);
				});
			},
			
			lightBoxGallery: function(){
				$('.product.media').magnificPopup({
					delegate: '.imgzoom .lb',
					type: 'image',
					tLoading: 'Loading image #%curr%...',
					mainClass: 'mfp-img-gallery',
					fixedContentPos: true,
					gallery: {
						enabled: true,
						navigateByImgClick: true,
						preload: [0,1]
					},
					iframe: {
						markup: '<div class="mfp-iframe-scaler">'+
								'<div class="mfp-close"></div>'+
								'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
								'<div class="mfp-bottom-bar">'+
								  '<div class="mfp-title"></div>'+
								  '<div class="mfp-counter"></div>'+
								'</div>'+
								'</div>'
					},
					image: {
						tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
					},
					callbacks: {
						elementParse: function(item) {
							if(item.el.context.className == 'lb video-link') {
								item.type = 'iframe';
							} else {
								item.type = 'image';
							}
						}
					}
				});
			},
			
			generateHtmlImage: function(imagesToUpdate){
				var html = "",
					lbox_image = $('#lbox_image').val();
				$.each(imagesToUpdate, function(index) {
					var $isVideo = false;
					if((imagesToUpdate[index].media_type == 'external-video' || imagesToUpdate[index].media_type == 'video' || imagesToUpdate[index].type == 'video') && imagesToUpdate[index].videoUrl != ""){
						$isVideo = true;
					}
					
					var $class = 'product item-image imgzoom';
					if($isVideo){
						$class = $class + ' item-image-video';
					}
					html = html + '<div class="'+$class+'" data-zoom="'+imagesToUpdate[index].zoom+'">';
					if($isVideo){
						html = html + '<div class="label-video">'+$.mage.__('Video')+'</div>';
					}
					
					if(lbox_image == 1){
						var href = imagesToUpdate[index].zoom;
						var cla = 'lb';
						if($isVideo){
							href = imagesToUpdate[index].videoUrl;
							cla = 'lb video-link';
						}
						html = html + '<a href="'+href+'" class="'+cla+'"><img class="img-fluid" src="'+imagesToUpdate[index].full+'" alt=""/></a>';
					}else {
						html = html + '<img class="img-fluid" src="'+imagesToUpdate[index].full+'" alt=""/>';
						if($isVideo){
							html = html + '<a target="_blank" class="popup-video" href="'+imagesToUpdate[index].videoUrl+'"><span class="ti-video-camera"></span></a>';
						}
					}
					
					html = html + '</div>';
				});
				return html;
			},
			
			generateHtmlThumb: function(imagesToUpdate){
				var html = "",
					lbox_image = $('#lbox_image').val();
					
				$.each(imagesToUpdate, function(index) {
					var $isVideo = false;
					if((imagesToUpdate[index].media_type == 'external-video' || imagesToUpdate[index].media_type == 'video' || imagesToUpdate[index].type == 'video') && imagesToUpdate[index].videoUrl != ""){
						$isVideo = true;
					}
					
					var classth = 'item-thumb';
					if(index == 0){ classth = 'item-thumb active'; }
					
					html = html + '<div class="'+classth+'" data-owl="'+index+'"><img class="img-fluid" src="'+imagesToUpdate[index].thumb+'" alt=""/>';
						if($isVideo){
							html = html + '<div class="popup-video-thumb"><span class="ti-video-camera"></span></div>';
						}
					html = html + '</div>';
				});
				
				return html;
			},
        });
            
        return $.mage.configurable;
    }
});
