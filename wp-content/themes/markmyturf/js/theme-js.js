/*	ON HOVER ALT
================================= */

/* jQuery('.featured-services .pbuilder_column').each( function( index ) {
	var alt_text = jQuery(this).find('img').attr('alt');
	console.log(alt_text);
	jQuery('<h3>' + alt_text + '</h3>').insertAfter( jQuery(this).find('img') );
} ) */


/*	CAPTCHA PLACEHOLDER
================================= */

jQuery('.gfield_captcha_input_container input').attr('placeholder', 'Enter Code');

/*	BENEFITS BOX
================================= */

jQuery('#benefits-content .omsc-box').each( function(index) {
	var num = index + 1;
	jQuery(this).find('.omsc-box-inner').append('<div class="number">' + num + '</div>')
	var boxes = jQuery(this).find('.box-content').height();
	if( boxes < 40 ) {
		jQuery(this).find('.box-content').css({
			'line-height' : 3.5,
			'min-height' : 120
		});
	}
	console.log(boxes);
} );

jQuery('#line_marketing a').attr('data-scroll','');