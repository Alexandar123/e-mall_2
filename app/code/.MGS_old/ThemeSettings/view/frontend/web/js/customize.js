if (typeof(WEB_URL_AJAX) == 'undefined') {
	if (typeof(BASE_URL) !== 'undefined') {
		var WEB_URL_AJAX = BASE_URL;
	}else{
		pubUrlAjax = require.s.contexts._.config.baseUrl;
		arrUrlAjax = pubUrlAjax.split('pub/');
		var WEB_URL_AJAX = arrUrlAjax[0];
	}
}

require([
	'jquery'
], function ($) {
	var frameEl = $("#theme-customize");
	var progress = $('#frame-progress');
	var activePanel = 0;
	function panelAction(){
		/* Open setting when click panel title */
		$("button.panel-title-btn").click(function() {
			activePanel = dataNumber = $(this).attr('data-number');
			$('#panel'+dataNumber).show();
			$('#panel-form').addClass('active-panel');
		});
		
		/* Close setting */
		$("button.btn-close-panel").click(function() {
			activePanel = 0;
			$('.panel-form-group').hide();
			$('#panel-form').removeClass('active-panel');
		});
		
		/* Responsive window */
		$("button.responsive").click(function() {
			$("button.responsive").removeClass('active');
			$(this).addClass('active');
			
			var responsiveType = $(this).attr('data-responsive');
			if(responsiveType!='full'){
				$(".left-panel-collapsible").show();
				$(".preview .theme").removeClass('desktop').removeClass('phone');
				$(".preview .theme").addClass(responsiveType);
			}else{
				$(".preview .theme").removeClass('desktop').removeClass('phone');
				$(".left-panel-collapsible").hide();
			}
			
		});
		
		/* Color input */
		if($('.panel-input-color').length){
			$('.panel-input-color').attr('data-hex', true).mColorPicker();
			
			$('.panel-input-color').change(function(){
				var backgroundColor = $(this).css("background-color");
				$(this).css("color", backgroundColor);
			});
			
			$('.panel-input-color').each(function(){
				var inputColor = $(this);
				var backgroundColor = inputColor.css("background-color");
				inputColor.css("color", backgroundColor);
				inputColor.bind('colorpicked', function () {
					progress.show();
					var loadStyle = 0;
					if(inputColor[0].hasAttribute('data-load-style') && (inputColor.attr('data-load-style')==1)){
						loadStyle = 1;
					}
					$.ajax({
						url: WEB_URL_AJAX + 'mgsthemesetting/theme/save',
						data: { 
							id : inputColor.attr('id'), 
							value : inputColor.val(),
							style : loadStyle
						},
						success: function(data) {
							if(loadStyle && (data!='')){
								frameEl.contents().find("#themesetting_customize_temp").html(data);
							}
							if(inputColor[0].hasAttribute('data-reload') && (inputColor.attr('data-reload')==1)){
								document.getElementById('theme-customize').contentDocument.location.reload(true);
							}else{
								progress.hide();
							}
						}
					});
				});
			});
			
			/* Upload image */
			$('.input-image').change(function(){
				progress.show();
				var imageField = $(this);
				fileName = imageField.val();
				allowExtensions = imageField.attr('accept');
				uploadType = imageField.attr('data-type');
				allowExtensions = allowExtensions.split(',');
				arrName = fileName.split('.');
				extensionName = arrName[arrName.length - 1];
				extensionName = '.' + extensionName.toLowerCase();
				if(allowExtensions.includes(extensionName)){
					var loadStyle = 0;
					if(imageField[0].hasAttribute('data-load-style') && (imageField.attr('data-load-style')==1)){
						loadStyle = 1;
					}
					var formData = new FormData();
					formData.append('file', imageField[0].files[0]);
					formData.append('id', imageField.attr('data-id'));
					formData.append('style', loadStyle);
					formData.append('save_path', imageField.attr('data-save-path'));
					if(imageField[0].hasAttribute('data-store-path')){
						formData.append('store_path', imageField.attr('data-store-path'));
					}
					$.ajax({
						url: WEB_URL_AJAX + 'mgsthemesetting/theme/upload',
						type: "POST",
						data: formData,
						contentType: false,
						cache: false,
						processData:false,
						success: function(data){
							var result = jQuery.parseJSON(data);
							if(result.result=='success'){
								if(uploadType=='image'){
									$('#'+imageField.attr('data-id')+'_field .img-preview img').remove();
									$('#'+imageField.attr('data-id')+'_field .img-preview').append('<img src="'+result.data+'"/>');
								}else{
									$('#'+imageField.attr('data-id')+'_field .img-preview .icon-container').remove();
									url = result.data;
									arrUrl = url.split('/');
									resultFileName = arrUrl[arrUrl.length-1];
									extensionName = resultFileName.split('.');
									$('#'+imageField.attr('data-id')+'_field .img-preview').append('<div class="icon-container"><span class="file-icon"><span>'+extensionName[extensionName.length-1]+'</span></span><span class="text">'+resultFileName+'</span></div>');
								}
								$('#'+imageField.attr('data-id')+'_field .select-img').hide();
								$('#'+imageField.attr('data-id')+'_field .img-action').show();
								
								if(loadStyle && (result.style!='')){
									frameEl.contents().find("#themesetting_customize_temp").html(result.style);
								}
								if(imageField[0].hasAttribute('data-reload') && (imageField.attr('data-reload')==1)){
									document.getElementById('theme-customize').contentDocument.location.reload(true);
								}else{
									progress.hide();
								}
							}
						}
					});
				}
			});
			
			/* Remove image */
			$('.btn-button-remove').click(function(){
				progress.show();
				var btn = $(this);
				fileType = btn.attr('data-type');
				var loadStyle = 0;
				if(btn[0].hasAttribute('data-load-style') && (btn.attr('data-load-style')==1)){
					loadStyle = 1;
				}
				var imgEl = btn.attr('data-element');
				var imgSrc = $('#'+imgEl+'_field .img-preview img').attr('src');
				
				$.ajax({
					url: WEB_URL_AJAX + 'mgsthemesetting/theme/remove',
					data: { 
						id : imgEl,
						src: imgSrc,
						style : loadStyle
					},
					success: function(data) {
						if(fileType=='image'){
							$('#'+imgEl+'_field .img-preview img').remove();
						}else{
							$('#'+imgEl+'_field .img-preview .icon-container').remove();
						}
						$('#'+imgEl+'_field .img-action').hide();
						$('#'+imgEl+'_field .select-img').css('display','flex');
						if(loadStyle && (data!='')){
							frameEl.contents().find("#themesetting_customize_temp").html(data);
						}
						if(btn[0].hasAttribute('data-reload') && (btn.attr('data-reload')==1)){
							document.getElementById('theme-customize').contentDocument.location.reload(true);
						}else{
							progress.hide();
						}
					}
				});
			});
			
			/* Checkbox boolean */
			$('.checkbox-temp').click(function(){
				if($(this).prop("checked") == true){
					var elementValue = 1;
				}else{
					var elementValue = 0;
				}
				var realElId = $(this).attr('id').replace('_temp','');
				$('#'+realElId).val(elementValue).change();
			});
			
			/* Checkbox for multiple select */
			$('.multiple-checkbox').click(function(){
				var checkEl = $(this);
				var checkValue = checkEl.val();
				var inputElId = checkEl.attr('data-parent');
				var inputEl = $('#'+inputElId);
				var inputValue = inputEl.val();
				inputValue = inputValue.split(",");
				if(checkEl.prop("checked") == true){
					inputValue.push(checkValue);
				}else{
					inputValue.splice(inputValue.indexOf(checkValue), 1);
				}
				inputValue.toString();
				inputEl.val(inputValue).change();
			});
			
			/* Slider range input */
			$('.slider-input .slider').change(function(){
				var sliderEl = $(this);
				var dataInput = sliderEl.attr('data-input');
				$('#'+dataInput).val(sliderEl.val()).change();
			});
			
			$('.slider-input .slider').on('input', function() {
				var sliderEl = $(this);
				var dataInput = sliderEl.attr('data-input');
				$('#'+dataInput+'_field .value span').html(sliderEl.val());
			});

		}
		
		
		/* Hidden fields with depend condition */
		var arDependField = [];
		$("div.field").each(function(order) {
			if($(this)[0].hasAttribute('data-depend')){
				hiddenEl = $(this);
				/* Hide field */
				hiddenEl.hide();
				
				/* Show field if conditions are true */
				var dataDepend = hiddenEl.attr('data-depend').toString();
				dataDependReplace = dataDepend.replace(/==/g, ':').replace(/!=/g, ':').replace(/_and_/g, '-').replace(/_or_/g, '-');
				
				var arrCondition = dataDependReplace.split('-');
				$.each( arrCondition, function( i, val ) {
					var arrEl = val.split(':');		
					elId = arrEl[0];

					dataDepend = dataDepend.replace(new RegExp(elId, 'g'), "$('#"+elId+"').val()");
					dataDepend = dataDepend.replace(/_and_/g, '&&').replace(/_or_/g, '||');
					arDependField[order] = [elId, hiddenEl.attr('id'), dataDepend];
				});
			}
		});
		
		if(arDependField.length){
			$.each( arDependField, function( i, val ) {
				if (typeof val !== 'undefined'){
					if(eval(val[2])){
						$('#'+val[1]).show();
					}else{
						$('#'+val[1]).hide();
					}
					
					$('#'+val[0]).change(function(){
						if(eval(val[2])){
							$('#'+val[1]).show();
						}else{
							$('#'+val[1]).hide();
						}
					});
				}
			});
		}
		
		/* On input change */
		$(".panel-input").change(function() {
			if(!$(this).hasClass("panel-input-color")){
				progress.show();
				var el = $(this);
				var elementId = el.attr('id');
				if(el.hasClass("checkbox")){
					if(el.prop("checked") == true){
						var elementValue = 1;
					}else{
						var elementValue = 0;
					}
				}else{
					var elementValue = el.val();
				}
				var loadStyle = 0;
				if(el[0].hasAttribute('data-load-style') && (el.attr('data-load-style')==1)){
					loadStyle = 1;
				}
				$.ajax({
					url: WEB_URL_AJAX + 'mgsthemesetting/theme/save',
					data: { 
						id : elementId, 
						value : elementValue,
						style : loadStyle
					},
					success: function(data) {
						if(loadStyle && (data!='')){
							frameEl.contents().find("#themesetting_customize_temp").html(data);
						}
						if(el[0].hasAttribute('data-reload') && (el.attr('data-reload')==1)){
							document.getElementById('theme-customize').contentDocument.location.reload(true);
						}else{
							progress.hide();
						}
					}
				});
			}
		});
	};
	$(document).ready(function(){
		panelAction();
	});
	
	/* Show loading progress on frame */
	frameEl.load(function(){
		$('#left-panel-container').css('opacity', '0.5');
		progress.hide();
		$.ajax({
			url: WEB_URL_AJAX + 'mgsthemesetting/theme/navigation',
			data: { 
				activepanel : activePanel
			},
			success: function(data) {
				$('#left-panel-container').html(data);
				$('#left-panel-container').css('opacity', '1');
				panelAction();
			}
		});
		$(this).contents().find("a").click(function(){
			aEl = $(this);
			if(aEl[0].hasAttribute('href') && btoa(aEl.attr('href'))!='' && btoa(aEl.attr('href'))!='#'){
				progress.show();
				aHref = btoa(aEl.attr('href'));
				url = WEB_URL_AJAX + 'mgsthemesetting/theme/customize/referrer/'+aHref;
				top.window.location.href = url;
				return false;
			}
		});
	});
});