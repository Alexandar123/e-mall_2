(function($) {

    $.fn.annotateImage = function(options) {
        var opts = $.extend({}, $.fn.annotateImage.defaults, options);
        var image = this;

        this.image = this;
        this.mode = 'view';

        // Assign defaults
        this.input_field_id = opts.input_field_id;
        this.getUrl = opts.getUrl;
        this.saveUrl = opts.saveUrl;
        this.deleteUrl = opts.deleteUrl;
        this.editable = opts.editable;
        this.useAjax = opts.useAjax;
        this.notes = opts.notes;

		this.pintext = $('#default_pin_text').val();

        // Add the canvas
        this.canvas = $('<div class="image-annotate-canvas"><div class="image-annotate-view"></div><div class="image-annotate-edit"><div class="image-annotate-edit-area"><span>'+this.pintext+'</span></div></div></div>');
        this.canvas.children('.image-annotate-edit').hide();
        //this.canvas.children('.image-annotate-view').hide();
        this.image.after(this.canvas);

        // Give the canvas and the container their size and background
        this.canvas.height(this.height());
        this.canvas.width(this.width());
        this.canvas.css('background-image', 'url("' + this.attr('src') + '")');
        this.canvas.children('.image-annotate-view, .image-annotate-edit').height(this.height());
        this.canvas.children('.image-annotate-view, .image-annotate-edit').width(this.width());

        // Add the behavior: hide/show the notes when hovering the picture
        this.canvas.hover(function() {
            if ($(this).children('.image-annotate-edit').css('display') == 'none') {
                $(this).children('.image-annotate-view').show();
            }
        }, function() {
            //$(this).children('.image-annotate-view').hide();
        });

        this.canvas.children('.image-annotate-view').hover(function() {
            $(this).show();
        }, function() {
            //$(this).hide();
        });

        // load the notes
        if (this.useAjax) {
            $.fn.annotateImage.ajaxLoad(this);
        } else {
            $.fn.annotateImage.load(this);
        }

        // Add the "Add a note" button
        if (this.editable) {
            this.button = $('<button class="image-annotate-add scalable add" id="image-annotate-add" onclick="javascript: return false;"><span><span>'+ $('#add_text').val() +'</span></span></button>');
            this.button.click(function() {
                $.fn.annotateImage.add(image);
				//new Ajax.Autocompleter('image-annotate-text', 'sku_autocomplete_choices', $('#load_product_url').val(), {indicator: 'sku_loader'});
				$("#image-annotate-text").autocomplete({
					source: $('#load_product_url').val(),
					select: function(){
						setBlankPinLabel();
					}
				});
            });
            this.canvas.after(this.button);
        }

        // Hide the original
        this.hide();

        return this;
    };

    /**
    * Plugin Defaults
    **/
    $.fn.annotateImage.defaults = {
        getUrl: 'your-get.rails',
        saveUrl: 'your-save.rails',
        deleteUrl: 'your-delete.rails',
        editable: true,
        useAjax: true,
        notes: new Array()
    };

    $.fn.annotateImage.clear = function(image) {  
        for (var i = 0; i < image.notes.length; i++) {
            image.notes[image.notes[i]].destroy();
        }
        image.notes = new Array();
    };

    $.fn.annotateImage.ajaxLoad = function(image) {
        $.getJSON(image.getUrl + '?ticks=' + $.fn.annotateImage.getTicks(), function(data) {
            image.notes = data;
            $.fn.annotateImage.load(image);
        });
    };

    $.fn.annotateImage.load = function(image) {
        for (var i = 0; i < image.notes.length; i++) {
            image.notes[image.notes[i]] = new $.fn.annotateView(image, image.notes[i]);
        }
    };

    $.fn.annotateImage.getTicks = function() {      
        var now = new Date();
        return now.getTime();
    };

    $.fn.annotateImage.add = function(image) {    
        if (image.mode == 'view') {
            image.mode = 'edit';

            // Create/prepare the editable note elements
            var editable = new $.fn.annotateEdit(image);
           
            $.fn.annotateImage.createSaveButton(editable, image);
            $.fn.annotateImage.createDeleteButton(editable, image);
            $("#image-annotate-edit-ok").css('float', 'right');
            $(".image-annotate-edit-close").css('margin-left', '25px');
        }

    };

    $.fn.annotateImage.createSaveButton = function(editable, image, note) {
        var ok = $('<button class="image-annotate-edit-ok scalable save" id="image-annotate-edit-ok" onclick="javascript: return false;"><span>'+$('#ok_text').val()+'</span></button>');

        ok.click(function() {
			$('#image-annotate-edit-ok').addClass('saving');
            var form = $('#image-annotate-edit-form form');
            var text = $('#image-annotate-text').val();                   
            var label = $('#image-annotate-label').val();                   
            var position = $('#image-annotate-position').val();                   
            var custom_text = $('#image-annotate-custom_text').val();                   
            var custom_label = $('#image-annotate-custom_label').val();                   

            $.fn.annotateImage.appendPosition(form, editable)
            image.mode = 'view';

            // Save via AJAX
            if (image.useAjax) {
                $.ajax({
                    url: image.saveUrl,
                    data: form.serialize(),
                    error: function(e) { alert("An error occured saving that note.") },
                    success: function(data) {
				if (data.annotation_id != undefined) {
					editable.note.id = data.annotation_id;
				}
		    },
                    dataType: "json"
                });
            }

                var test_area = Object();
                 test_area.id = editable.note.id;
                 test_area.height = editable.area.height();
                 test_area.width = editable.area.width();
                 test_area.left = editable.area.position().left;
                 test_area.top = editable.area.position().top;
                
            if (($.trim(text)=='') && ($.trim(custom_text)=='')) {
                alert('Please, enter Product SKU or custom text');
                return false;
            }
            
            //var response = checkSKU();
			
			$.ajax({
				type:"POST",
				url: $('#check_product_url').val(),
				data:$("#annotate-edit-form").serialize(),
				success: function(data, textStatus, xhr) {
					if (200 == xhr.status) {
						response = data;
						
						response = jQuery.parseJSON(response);
						if (response.status != 1) {
							if($.trim(custom_text)==''){
								alert('The product with SKU="'+text+'" ' + response.status);
								return false;
							} 
						}
						
						label = response.label;
						
						// Add to canvas
						if (note) {
							note.resetPosition(editable, text, label, position, custom_text, custom_label);             
						} else {
							editable.note.editable = true;
							note = new $.fn.annotateView(image, editable.note);
							note.resetPosition(editable, text, label, position, custom_text, custom_label);
							image.notes.push(editable.note);
						}  

						$('#'+image.input_field_id).val(JSON.stringify(image.notes));
						editable.destroy();
					}else{
						alert("'.__('Product does not exist').'");
					}
					$('#image-annotate-edit-ok').removeClass('saving');
				}
			});
			
			
        });
        editable.form.append(ok);
    };

    $.fn.annotateImage.createCancelButton = function(editable, image) {
        var cancel = $('<button class="image-annotate-edit-close scalable back" onclick="javascript: return false;"><span><span>'+$('#cancel_text').val()+'</span></span></button>');
        cancel.click(function() {
            editable.destroy();
            image.mode = 'view';
        });
        editable.form.append(cancel);
    };
	
	$.fn.annotateImage.createDeleteButton = function(editable, image) {
        var cancel = $('<button class="image-annotate-edit-delete scalable delete" onclick="javascript: return false;"><span><span>'+$('#delete_text').val()+'</span></span></button>');
        cancel.click(function() {
            editable.destroy();
            image.mode = 'view';
        });
        editable.form.append(cancel);
    };

    $.fn.annotateImage.saveAsHtml = function(image, target) {
        var element = $(target);
        var html = "";
        for (var i = 0; i < image.notes.length; i++) {
            html += $.fn.annotateImage.createHiddenField("text_" + i, image.notes[i].text);
            html += $.fn.annotateImage.createHiddenField("label_" + i, image.notes[i].label);
            html += $.fn.annotateImage.createHiddenField("top_" + i, image.notes[i].top);
            html += $.fn.annotateImage.createHiddenField("left_" + i, image.notes[i].left);
            html += $.fn.annotateImage.createHiddenField("height_" + i, image.notes[i].height);
            html += $.fn.annotateImage.createHiddenField("width_" + i, image.notes[i].width);
        }
        element.html(html);
    };

    $.fn.annotateImage.createHiddenField = function(name, value) {
        return '&lt;input type="hidden" name="' + name + '" value="' + value + '" /&gt;<br />';
    };

    $.fn.annotateEdit = function(image, note) {
        this.image = image;

        if (note) {
            this.note = note;
        } else {
            var newNote = new Object();
            newNote.id = ""+new Date().getTime();
            newNote.top = 100;
            newNote.left = 100;
            newNote.width = $('#pin_width').val();
            newNote.height = $('#pin_height').val();
            newNote.text = "";
            newNote.label = $('#default_pin_text').val();
			newNote.position = "top";
			newNote.custom_text = "";
			newNote.custom_label = "";
            newNote.imgH = this.image.height();
            newNote.imgW = this.image.width();
            this.note = newNote;
        }

        // Set area
        var area = image.canvas.children('.image-annotate-edit').children('.image-annotate-edit-area');
        this.area = area;
		this.area.html('<span>'+this.note.label+'</span>');
        this.area.css('height', this.note.height + 'px');
        this.area.css('width', this.note.width + 'px');
        this.area.css('left', this.note.left + 'px');
        this.area.css('top', this.note.top + 'px');
        

        // Show the edition canvas and hide the view canvas
        image.canvas.children('.image-annotate-view').hide();
        image.canvas.children('.image-annotate-edit').show();

        // Add the note (which we'll load with the form afterwards)
		var formHtml = '<div id="image-annotate-edit-form"><form id="annotate-edit-form"><table class="form-list" cellspacing="0"><tbody><tr><td class="label"><label for="image-annotate-text">Product Sku: </label></td><td class="value">';

		formHtml += '<input id="image-annotate-text" value="'+this.note.text+'" name="text" type="text" class=" input-text"/><div id="sku_loader" style="display:none"></div><div id="sku_autocomplete_choices" class="sku-autocomplete" style="display: none;"></div>';
		
		formHtml += '</td></tr><tr><td class="label"><label for="image-annotate-label">Pin Label: </label></td><td class="value"><input id="image-annotate-label" value="'+this.note.label+'" name="label" type="text" class=" input-text"/></td></tr><tr><td class="label"><label for="image-annotate-position">Popup Position: </label></td><td class="value">';
		
		formHtml += '<select id="image-annotate-position" name="position" class="select">';
			formHtml += '<option value="top"';
			if(this.note.position == 'top'){
				formHtml += ' selected="selected"';
			}
			formHtml += '>Top</option>';
			formHtml += '<option value="right"';
			if(this.note.position == 'right'){
				formHtml += ' selected="selected"';
			}
			formHtml += '>Right</option>';
			
			formHtml += '<option value="center"';
			if(this.note.position == 'center'){
				formHtml += ' selected="selected"';
			}
			formHtml += '>Center</option>';
			
			formHtml += '<option value="bottom"';
			if(this.note.position == 'bottom'){
				formHtml += ' selected="selected"';
			}
			formHtml += '>Bottom</option>';
			
			formHtml += '<option value="left"';
			if(this.note.position == 'left'){
				formHtml += ' selected="selected"';
			}
			formHtml += '>Left</option>';
			
		formHtml += '</select>';
		
		formHtml += '</td></tr><tr><td class="label"><label for="image-annotate-custom_label">Custom Label: </label></td><td class="value"><input id="image-annotate-custom_label" value="'+this.note.custom_label+'" name="custom_label" type="text" class="input-text"/></td></tr><tr><td class="label" colspan="2"><label for="image-annotate-custom_text">Custom Text: </label></td></tr><tr><td class="value" colspan="2"><textarea id="image-annotate-custom_text" name="custom_text" type="text">'+this.note.custom_text+'</textarea></td></tr></form></tbody></table></div>';
        var form = $(formHtml);
        this.form = form;

        $('body').append(this.form);
        this.form.css('left', this.area.offset().left + 'px');
        this.form.css('top', (parseInt(this.area.offset().top) + parseInt(this.area.height()) + 7) + 'px');

        // Set the area as a draggable/resizable element contained in the image canvas.
        // Would be better to use the containment option for resizable but buggy
        area.resizable({
            handles: 'all',

            stop: function(e, ui) {
                form.css('left', area.offset().left + 'px');
                form.css('top', (parseInt(area.offset().top) + parseInt(area.height())) + 'px');
            }
        })
        .draggable({
            containment: image.canvas,
            drag: function(e, ui) {
                form.css('left', area.offset().left + 'px');
                form.css('top', (parseInt(area.offset().top) + parseInt(area.height())) + 'px');
            },
            stop: function(e, ui) {
                form.css('left', area.offset().left + 'px');
                form.css('top', (parseInt(area.offset().top) + parseInt(area.height())) + 'px');
            }
        });

        return this;
    };

    $.fn.annotateEdit.prototype.destroy = function() {
        this.image.canvas.children('.image-annotate-edit').hide();
        this.area.resizable('destroy');
        this.area.draggable('destroy');
        this.area.css('height', '');
        this.area.css('width', '');
        this.area.css('left', '');
        this.area.css('top', '');
        this.form.remove(); 
        ShowHideHotspotsMsg();    
    }

    $.fn.annotateView = function(image, note) {
        this.image = image;

        this.note = note;

        this.editable = (note.editable && image.editable);

        // Add the area
        this.area = $('<div class="image-annotate-area' + (this.editable ? ' image-annotate-area-editable' : '') + '"><div><span>'+this.note.label+'</span></div></div>');
        image.canvas.children('.image-annotate-view').prepend(this.area);

        // Add the note
        this.form = $('<div class="image-annotate-note">' + note.text + '</div>');
        this.form.hide();
        image.canvas.children('.image-annotate-view').append(this.form);
        this.form.children('span.actions').hide();

        // Set the position and size of the note
        this.setPosition();

        // Add the behavior: hide/display the note when hovering the area
        var annotation = this;
        this.area.hover(function() {
            annotation.show();
        }, function() {
            annotation.hide();
        });

        // Edit a note feature
        if (this.editable) {
            var form = this;
            this.area.click(function() {
                form.edit();
				//new Ajax.Autocompleter('image-annotate-text', 'sku_autocomplete_choices', $('#load_product_url').val(), {indicator: 'sku_loader'});
				
				    $("#image-annotate-text").autocomplete({
						source: $('#load_product_url').val(),
						select: function(){
							setBlankPinLabel();
						}
					});
            });
        }
    };

    $.fn.annotateView.prototype.setPosition = function() {
        this.area.children('div').height((parseInt(this.note.height)) + 'px');
        this.area.children('div').width((parseInt(this.note.width)) + 'px');
        this.area.css('left', (this.note.left) + 'px');
        this.area.css('top', (this.note.top) + 'px');
        this.form.css('left', (this.note.left) + 'px');
        this.form.css('top', (parseInt(this.note.top) + parseInt(this.note.height) + 7) + 'px');
    };

    $.fn.annotateView.prototype.show = function() {
        this.form.fadeIn(250);
        if (!this.editable) {
            this.area.addClass('image-annotate-area-hover');
        } else {
            this.area.addClass('image-annotate-area-editable-hover');
        }
    };

    $.fn.annotateView.prototype.hide = function() {    
        this.form.fadeOut(250);
        this.area.removeClass('image-annotate-area-hover');
        this.area.removeClass('image-annotate-area-editable-hover');
    };

    $.fn.annotateView.prototype.destroy = function() {   
        this.area.remove();
        this.form.remove();
    }

    $.fn.annotateView.prototype.edit = function() {
        if (this.image.mode == 'view') {
            this.image.mode = 'edit';
            var annotation = this;

            // Create/prepare the editable note elements
            var editable = new $.fn.annotateEdit(this.image, this.note);

            $.fn.annotateImage.createSaveButton(editable, this.image, annotation);

            // Add the delete button
            var del = $('<button class="image-annotate-edit-delete scalable delete" onclick="javascript: return false;"><span><span>'+$('#delete_text').val()+'</span></span></button>');
            del.click(function() {
                var form = $('#image-annotate-edit-form form');

                $.fn.annotateImage.appendPosition(form, editable)

                if (annotation.image.useAjax) {
                    $.ajax({
                        url: annotation.image.deleteUrl,
                        data: form.serialize(),
                        error: function(e) { alert("An error occured deleting that note.") }
                    });
                }

                for (var i = 0; i < annotation.image.notes.length; i++) {
                    if (annotation.image.notes[i]==editable.note) 
                    {
                        annotation.image.notes.splice(i,1);
                    }
                } 
                
                $('#'+annotation.image.input_field_id).val(JSON.stringify(annotation.image.notes));

                annotation.image.mode = 'view';
                editable.destroy();
                annotation.destroy(); 
              
            });
            editable.form.append(del);
            
            $.fn.annotateImage.createCancelButton(editable, this.image);
        }
    };

    $.fn.annotateImage.appendPosition = function(form, editable) {
        var areaFields = $('<input type="hidden" value="' + editable.area.height() + '" name="height"/>' +
                           '<input type="hidden" value="' + editable.area.width() + '" name="width"/>' +
                           '<input type="hidden" value="' + editable.area.position().top + '" name="top"/>' +
                           '<input type="hidden" value="' + editable.area.position().left + '" name="left"/>' +
                           '<input type="hidden" value="' + editable.note.id + '" name="id"/>');
        form.append(areaFields);
    }

    $.fn.annotateView.prototype.resetPosition = function(editable, text, label, position, custom_text, custom_label) {
        this.form.html(text);
        //this.form.html(label);
        this.form.hide();

        // Resize
        this.area.children('div').height(editable.area.height() + 'px');
        this.area.children('div').width((editable.area.width()) + 'px');
		this.area.children('div').html('<span>'+label+'</span>');
        this.area.css('left', (editable.area.position().left) + 'px');
        this.area.css('top', (editable.area.position().top) + 'px');
        this.form.css('left', (editable.area.position().left) + 'px');
        this.form.css('top', (parseInt(editable.area.position().top) + parseInt(editable.area.height()) + 7) + 'px');

        // Save new position to note
        this.note.top = editable.area.position().top;
        this.note.left = editable.area.position().left;
        this.note.height = editable.area.height();
        this.note.width = editable.area.width();
        this.note.text = text;
        this.note.label = label;
        this.note.position = position;
        this.note.custom_text = custom_text;
        this.note.custom_label = custom_label;
        this.note.id = editable.note.id;
        this.editable = true;
    };

    intersects = function(X1, Y1, H1, L1, X2, Y2, H2, L2) {
        X1 = parseInt(X1);
        Y1 = parseInt(Y1);
        H1 = parseInt(H1);
        L1 = parseInt(L1);
        X2 = parseInt(X2);
        Y2 = parseInt(Y2);
        H2 = parseInt(H2);
        L2 = parseInt(L2);
        a = X1 + L1 < X2;
        b = X1 > X2 + L2;
        c = Y1 + H1 < Y2;
        d = Y1 > Y2 + H2;                            
        if ((a || b || c || d)) {
            return false;
        }
        else
        {
            return true;
        }
    };
    
    ShowHideHotspotsMsg = function() {
           view_is_visible = $(".image-annotate-canvas").find(".image-annotate-view").is(":visible");
           edit_is_visible = $(".image-annotate-canvas").find(".image-annotate-edit").is(":visible");
           if (view_is_visible || edit_is_visible) {
                 $(".hotspots-msg").hide();
           }
           else
           {
                 $(".hotspots-msg").show();
           }
    }
                        
    CheckPosition = function(note, notes) {
        i=0;
        res = true;
        notes.each(function() {
            if (note.id!=notes[i].id) {
                if (intersects(note.left, note.top, note.height, note.width, notes[i].left, notes[i].top, notes[i].height, notes[i].width)){
                     res = false;
                }
            }
            i++;
        });  
        return res;
    };

})(jQuery);