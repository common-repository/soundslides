jQuery('#attachziptomedia').live('click',function(event){
    	event.preventDefault();
    	jQuery('.soundslides_zip').each(function(){
    		var zipname = jQuery(this).val(); 
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "attach_zip_to_media", "filename": zipname },
				success: function(data){
					if( data == 1){
						jQuery('#ss_message').removeClass('error').addClass('updated').html('<p><strong>Soundslide Project saved to media library.</strong></p>')
					}
					else
						{
						jQuery('#ss_message').removeClass('updated').addClass('error').html('<p><strong>Invalid Format.</strong></p>')
						}
					
				}
			}); 
    	});
    	
	});