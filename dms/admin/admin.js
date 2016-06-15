!function ($) {

	// --> Initialize
	$(document).ready(function() {

		$.plAdminMeta.init()
		
		$.plAdminMedia.init()
		
		$.plAdminPlugins.init()
	})


	$.plAdminPlugins = {
		
		init: function(){
			$('.pl-rec-plugins').expander({
				slicePoint:       0,
				expandPrefix:     ' ',
				expandText:       'Click here for more details.',
				userCollapseText: '',
				expandEffect: 'fadeIn',
				expandSpeed: 550,
			})
		}
	}

	/*
	 * Data/Settings handling functions.
	 */
	$.plAdminMeta = {
		
		init: function(){
			
			$('#post-formats-select input').change( $.plAdminMeta.checkFormat );
			$('.wp-post-format-ui .post-format-options > a').click( $.plAdminMeta.checkFormat );

			$(window).load(function(){
				$.plAdminMeta.checkFormat();
			})
			
		}
		
		, checkFormat: function( ){
			
			var format = $('#post-formats-select input:checked').attr('value');

			//only run on the posts page
			if(typeof format != 'undefined'){


				$('#post-body div[id^=pagelines-metabox-post-]').hide();
				$('#post-body #pagelines-metabox-post-'+format+'').stop(true,true).fadeIn(500);

			}
			
		}
		
		
	}
	
	$.plAdminMedia = {
		
		init: function(){
			
			jQuery(".redux-opts-upload").click( function( event ) {
		            var activeFileUploadContext = jQuery(this).parent();
		            var relid = jQuery(this).attr('rel-id');
					var $that = jQuery(this);
					var elementID = jQuery(this).attr('id');

		            event.preventDefault();

		            // If the media frame already exists, reopen it.
		            /*if ( typeof(custom_file_frame)!=="undefined" ) {
		                custom_file_frame.open();
		                return;
		            }*/

		            // if its not null, its broking custom_file_frame's onselect "activeFileUploadContext"
		            custom_file_frame = null;

		            // Create the media frame.
		            custom_file_frame = wp.media.frames.customHeader = wp.media({
		                // Set the title of the modal.
		                title: jQuery(this).data("choose"),

		                // Tell the modal to show only images. Ignore if want ALL
		                library: {
		                    type: 'image'
		                },
		                // Customize the submit button.
		                button: {
		                    // Set the text of the button.
		                    text: jQuery(this).data("update")
		                }
		            });

		            custom_file_frame.on( "select", function() {
		                // Grab the selected attachment.
		                var attachment = custom_file_frame.state().get("selection").first();

		                // Update value of the targetfield input with the attachment url.
		                jQuery('.redux-opts-screenshot',activeFileUploadContext).attr('src', attachment.attributes.url);
		                jQuery('#' + relid ).val(attachment.attributes.url).trigger('change');

		                jQuery('.redux-opts-upload',activeFileUploadContext).hide();
		                jQuery('.redux-opts-screenshot',activeFileUploadContext).show();
		                jQuery('.redux-opts-upload-remove',activeFileUploadContext).show();

		                

		        });

		        custom_file_frame.open();
		    });
		
		
			jQuery(".redux-opts-upload-remove").click( function( event ) {
			    var activeFileUploadContext = jQuery(this).parent();
			    var relid = jQuery(this).attr('rel-id');

			    event.preventDefault();

			    jQuery('#' + relid).val('');
			    jQuery(this).prev().fadeIn('slow');
			    jQuery('.redux-opts-screenshot',activeFileUploadContext).fadeOut('slow');
			    jQuery(this).fadeOut('slow');

			     
			});
			
			//media upload
			jQuery(".redux-opts-media-upload").click( function( event ) {
			            var activeFileUploadContext = jQuery(this).parent();
			            var relid = jQuery(this).attr('rel-id');

			            event.preventDefault();

			            // If the media frame already exists, reopen it.
			            /*if ( typeof(custom_file_frame)!=="undefined" ) {
			                custom_file_frame.open();
			                return;
			            }*/

			            // if its not null, its broking custom_file_frame's onselect "activeFileUploadContext"
			            custom_file_frame = null;

			            // Create the media frame.
			            custom_file_frame = wp.media.frames.customHeader = wp.media({
			                // Set the title of the modal.
			                title: jQuery(this).data("choose"),

			                // Tell the modal to show only images. Ignore if want ALL
			                library: {
			                    type: 'video'
			                },
			                // Customize the submit button.
			                button: {
			                    // Set the text of the button.
			                    text: jQuery(this).data("update")
			                }
			            });

			            custom_file_frame.on( "select", function() {
			                // Grab the selected attachment.
			                var attachment = custom_file_frame.state().get("selection").first();

			                // Update value of the targetfield input with the attachment url.
			                jQuery('#' + relid ).val(attachment.attributes.url).trigger('change');


						    jQuery('#_pagelines_video_embed').trigger('keyup');

			                jQuery('.redux-opts-media-upload',activeFileUploadContext).hide();
			                jQuery('.redux-opts-upload-media-remove',activeFileUploadContext).show();
			        });

			        custom_file_frame.open();
			    });
			
			
				jQuery(".redux-opts-upload-media-remove").click( function( event ) {
					
				    var activeFileUploadContext = jQuery(this).parent()
				    var relid = jQuery(this).attr('rel-id')

				    event.preventDefault();

				    jQuery('#' + relid).val('');
				    jQuery(this).prev().fadeIn('slow');
				    jQuery('.redux-opts-screenshot',activeFileUploadContext).fadeOut('slow');
				    jQuery(this).fadeOut('slow');
				});
			
			
		}
		
	}
	
	 



}(window.jQuery);