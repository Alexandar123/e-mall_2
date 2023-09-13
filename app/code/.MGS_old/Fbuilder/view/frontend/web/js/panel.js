if (typeof(WEB_URL) == 'undefined') {
	if(document.getElementById("base_url_input")){	
		var WEB_URL = document.getElementById("base_url_input").value;
	}else{
		if (typeof(BASE_URL) !== 'undefined') {
			var WEB_URL = BASE_URL;
		}else{
			pubUrl = require.s.contexts._.config.baseUrl;
			arrUrl = pubUrl.split('pub/');
			arrUrl = arrUrl[0].split('media/');
			var WEB_URL = arrUrl[0];
		}
	}
}

require([
	"jquery",
	"jquery/ui"
], function($){
	$(document).ready(function(){
		initPanelPopup();
		setSectionPanelPosition($);
		
		
		if($("#sortable_home").length){
			$("#sortable_home").sortable({handle: '.sort-handle'});
		}
		
		if($(".edit-panel.parent-panel").length){
			$('.edit-panel.parent-panel').mouseover(function(){
				$(this).parent().addClass('hover');
			}).mouseout(function(){
				$('.container-panel.hover').removeClass('hover');
			});
		}
		
		if($(".static-can-edit .edit-panel").length){
			$('.static-can-edit .edit-panel').mouseover(function(){
				$(this).parent().addClass('hover');
			}).mouseout(function(){
				$('.static-can-edit.hover').removeClass('hover');
			});
		}
		
		if($(".child-panel").length){
			$('.child-panel').mouseover(function(){
				$(this).parent().addClass('hover');
			}).mouseout(function(){
				$('.child-builder.hover').removeClass('hover');
			});
		}
		
		if($(".moveuplink").length){
			$(".moveuplink").click(function() {
				$(this).parents(".sort-item").insertBefore($(this).parents(".sort-item").prev());
				sendOrderToServer();   
			});
		   
			$(".movedownlink").click(function() {
				$(this).parents(".sort-item").insertAfter($(this).parents(".sort-item").next());
				sendOrderToServer();
			});
		}
		
		if($(".sort-block-container").length){
			$(".sort-block-container").sortable({
				handle: '.sort-handle',
				update: function (event, ui) {
					var data = $(this).sortable('serialize');

					$.ajax({
						data: data,
						type: 'POST',
						url: WEB_URL+'fbuilder/index/sortblock'
					});
				}
			});
		}
	});
});

function copyBlock(blockId){
	additionalData = '/block_id/'+blockId;
	loadAjaxByAction('copyblock', additionalData);	
	return;
}

function sendOrderToServer(){
	require([
		'jquery',
		'jquery/ui'
	], function(jQuery){
		(function($) {
			var order = $("#sortable_home").sortable('serialize');
			$.ajax({
				type: "POST", dataType: "json", url: WEB_URL+'fbuilder/index/sortsection',
				data: order,
				success: function(response) {}
			});
		})(jQuery);
	});		
}

function initPanelPopup(){
	require([
		"jquery",
		"magnificPopup"
	], function($){
		var magnificPopup = $('.popup-link').magnificPopup({
			type: 'iframe',
			iframe: {
				markup: '<div class="mfp-iframe-scaler builder-iframe">'+
						'<div class="mfp-close"></div>'+
						'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
						'</div>'
			}, 
			mainClass: 'mfp-fade',
			removalDelay: 160,
			preloader: false,
			fixedContentPos: false
		});
	});
}

function openPopup(href){
	require([
		"jquery",
		"magnificPopup"
	], function($){
		var magnificPopup = $.magnificPopup.open({
			items: {
				src: href,
			},
			type: 'iframe',
			iframe: {
				markup: '<div class="mfp-iframe-scaler builder-iframe">'+
						'<div class="mfp-close"></div>'+
						'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
						'</div>'
			}, 
			mainClass: 'mfp-fade',
			removalDelay: 160,
			preloader: false,
			fixedContentPos: false
		});
	});
}

function loadAjaxByAction(action, additionalData){
	require([
		"jquery"
	], function($){
		var url = WEB_URL+'fbuilder/index/'+action;
		if(additionalData){
			url +=additionalData;
		}
		$.ajax(url, {
			success: function(data) {
				if(data!=''){
					switch(action) {
						case 'newsection':
							$('#sortable_home').append(data);
							$('#new-section-load img').hide();
							$('#new-section-load .fa').show();
							initPanelPopup();
							$('.edit-panel.parent-panel').mouseover(function(){
								$(this).parent().addClass('hover');
							}).mouseout(function(){
								$('.container-panel.hover').removeClass('hover');
							});
							
							if($(".moveuplink").length){
								$(".moveuplink").click(function() {
									$(this).parents(".sort-item").insertBefore($(this).parents(".sort-item").prev());
									sendOrderToServer();   
								});
							   
								$(".movedownlink").click(function() {
									$(this).parents(".sort-item").insertAfter($(this).parents(".sort-item").next());
									sendOrderToServer();
								});
							}
							break;
						case 'removesection':
							var result = jQuery.parseJSON(data);
							if(isNaN(result.result)){
								alert(data);
							}else{
								$('#panel-section-'+result.result).remove();
								if(result.block_copied){
									$('button.btn-dulicate').remove();
								}
							}
							break;
						case 'copyblock':
							if($('.btn-dulicate').length==0){
								$('.btn-addnewblock').each(function(){
									pageId = $(this).attr('data-page-id');
									blockName = $(this).attr('data-block-name');
									pageType = $(this).attr('data-page-type');
									$(this).parent().append('<button class="action btn btn-default btn-dulicate" type="button" onclick="pasteBlock('+pageId+', \''+blockName+'\', \''+pageType+'\')"><em class="fa fa-plus-circle"></em>&nbsp;Paste Copied Block</button>');
								});
							}
							
							alert('You copied block.');
							break;
					} 
				}
			}
	   });
	});
}

function pasteBlock(pageId, blockName, pageType){
	var url = WEB_URL+'fbuilder/index/pasteblock/page_id/'+pageId+'/block_name/'+blockName+'/page_type/'+pageType;
	setLocation(url);
}
	
function addNewSection(page_id, type){
	additionalData = '/page_id/'+page_id+'/page_type/'+type;
	loadAjaxByAction('newsection', additionalData);
}

function addNewCategorySection(category_id, override){
	additionalData = '/page_id/'+category_id+'/page_type/category/override/'+override;
	loadAjaxByAction('newsection', additionalData);
}

function removeSection(sectionId){
	additionalData = '/id/'+sectionId;
	loadAjaxByAction('removesection', additionalData);
}

function removeBlock(url, blockId){
	require([
		"jquery"
	], function($){
		$.ajax(url, {
			success: function(data) {
				var result = jQuery.parseJSON(data);
				if(isNaN(result.result)){
					alert(data);
				}else{
					$('#block-'+result.result).remove();
					if(result.block_copied){
						$('button.btn-dulicate').remove();
					}
				}
				
			}
	   });
	});
}

function changeBlockCol(url, oldCol, blockId){
	require([
		"jquery"
	], function($){
		$.ajax(url, {
			success: function(data) {
				if(isNaN(data)){
					alert(data);
				}else{
					for(i=1; i<=12; i++){
						if($('#block-'+blockId).hasClass('col-des-'+i)){
							$('#block-'+blockId).removeClass('col-des-'+i);
						}
					}
					
					newClass = 'col-des-'+data;
					$('#block-'+blockId).addClass(newClass);
					
					$('#block-'+blockId+' .edit-panel .change-col a').removeClass('active');
					$('#changecol-'+blockId+'-'+data).addClass('active');
				}
			}
	   });
	});
}

function setSectionPanelPosition($){
	if($(".section-builder").length){
		$(".section-builder").each(function() {
			padding = $(this).css('padding-top');
			$(this).find($('.parent-panel')).css('top', padding);
		});
	}
}

function setLocation(url){
		require([
			"jquery",
			"mage/mage"
		], function($){
			$($.mage.redirect(url, "assign", 0));
		});
	}