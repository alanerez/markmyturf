(function($){
	$(document).ready(function(){
		if($('#wp-content-editor-container').length > 0) {
			if(typeof pbuilderSwitch == 'undefined') pbuilderSwitch = 'off';
            if(typeof pbuilderEnabled == 'undefined') pbuilderEnabled = 'true';

            if(pbuilderEnabled == 'true'){
		    			var html = '<a href="#" id="pbuilder_switch" class="wp-switch-editor switch-pbuilder'+(pbuilderSwitch == 'on' ? ' active' : '')+'">ProfitBuilder</a>';
		    			$('#wp-content-editor-tools .wp-editor-tabs').append(html);

		    			html = '';
		    			html += '<div class="pbuilder_editor_mask'+(pbuilderSwitch == 'on' ? ' active' : '')+'">';
		    			html += '<div class="pbuilder_editor_border">';
		    			html += '<div class="pbuilder_editor_content">';
		    			html += '<div class="pbuilder_editor_buttons">';
		    			html += '<a href="'+ajaxurl+'?action=pbuilder_edit&p='+$('#post_ID').val()+'" id="pbuilder_edit_page" class="pbuilder_button_primary">Edit page</a>';
		    			html += '<a href="'+ajaxurl+'?action=pbuilder_switch&p='+$('#post_ID').val()+'&sw=off" id="pbuilder_deactivate" class="pbuilder_button">Deactivate</a>';
		    			html += '</div>';
		    			html += '</div>';
		    			html += '</div>';
		    			html += '</div>';

		    			$('#wp-content-editor-container').append(html);

							if(pbuilderSwitch == 'on'){
								$('#wp-content-editor-container').height('500px');
								$('#wp-content-editor-container').css('overflow','hidden');
							}
					}



			$('#pbuilder_switch').click(function(e){
				e.preventDefault();
				if(!$(this).hasClass('active')) {
					$('#publishing-action .spinner').show();
					$.get(ajaxurl+'?action=pbuilder_switch&p='+$('#post_ID').val()+'&sw=on', function(response) {
						if(response == 'success') {
							$('#pbuilder_switch').addClass('active').blur();
							$('.pbuilder_editor_mask').addClass('active');
							$('#wp-content-editor-container').height('500px');
							$('#wp-content-editor-container').css('overflow','hidden');
							$('#wp-admin-bar-pbuilder_edit a').attr('href', ajaxurl+'?action=pbuilder_edit&p='+$('#post_ID').val());
							$('#publishing-action .spinner').hide();
						}
						else {
							alert(response);
							$('#publishing-action .spinner').hide();
						}
					});
				}
			});

			$('#pbuilder_disable').click(function(e){
				$('#publishing-action .spinner').show();
				if(confirm("Are you sure you want to purge this page from Profit Builder? The layout and content created for this page in Pofit Builder will be lost!")){
				  $.get(ajaxurl+'?action=pbuilder_disable&p='+$('#post_ID').val(), function(response) {
					if(response == 'success') {
					  $('#pbuilder_disable').addClass('active').blur();
					  $('#publishing-action .spinner').hide();
					  location.reload();
					} else {
					  alert(response);
					}
				  });
				}
			});

			$('#pbuilder_deactivate').click(function(e){
				e.preventDefault();
				$('#publishing-action .spinner').show();
				$.get($(this).attr('href'), function(response){
					$('#pbuilder_switch').removeClass('active');
					$('.pbuilder_editor_mask').removeClass('active');
					$('#wp-content-editor-container').css('overflow','auto');
					$('#wp-content-editor-container').css('height','auto');
					$('#wp-admin-bar-pbuilder_edit a').attr('href', ajaxurl+'?action=pbuilder_edit&p='+$('#post_ID').val()+'&sw=on');
					$('#publishing-action .spinner').hide();
				});
			});

			$('#content-tmce, #content-html').click(function(){
				if($('#pbuilder_switch').hasClass('active')) {
					$('#pbuilder_deactivate').trigger('click');
				}
			});
		}
	});
})(jQuery)
