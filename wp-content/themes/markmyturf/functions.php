<?php
add_action( 'wp_enqueue_scripts', 'theme_styles_and_scripts' );
function theme_styles_and_scripts() {
	wp_enqueue_style( 'fontAwesome' , '//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css' );
	wp_enqueue_style( 'customFonts' , '//fonts.googleapis.com/css?family=Open+Sans+Condensed:700|Open+Sans:400,700,800|Oswald' );
    	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

	wp_enqueue_script( 'theme-js' , get_stylesheet_directory_uri() . '/js/theme-js.js' , array('jquery'), '', true);
	wp_enqueue_script( 'modernizr' , '//cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js' , false);
	wp_enqueue_script( 'smooth-scroll' , get_stylesheet_directory_uri() . '/js/smooth-scroll.js' , true);
}

add_action( 'widgets_init', 'remove_default_widgets', 11);
function remove_default_widgets() {
	unregister_sidebar( 'sidebar-1' );
	unregister_sidebar( 'sidebar-2' );
}

function create_widget ( $name, $id, $description, $before_widget, $after_widget ) {

	$args = array(
		'name'          	=> __( $name ),
		'id'           		=> $id,
		'description'   	=> $description,
		'before_widget' 	=> $before_widget,
		'after_widget'  	=> $after_widget,
		'before_title'  	=> '<h3 class="widgettitle">',
		'after_title'   	=> '</h3>'
	);

	register_sidebar( $args );

}

create_widget ( 'Header Widget', 'header-widget', 'Header widget displayed on the right section.', '<section class="widget">', '</section>' );
create_widget ( 'After Content Widget', 'after-content-widget', 'Widget displayed after content.', '<section class="widget">', '</section>' );
create_widget ( 'Footer Images Widget', 'footer-images-widget', 'Widget containing conditional images displayed after content widget.', '<section class="widget">', '</section>' );
create_widget ( 'Footer Widget', 'footer-widget', 'Footer widget displaying copyright content.', '<section class="widget">', '</section>' );

/*	CUSTOM SCRIPTS
================================= */

/*	HIDE GRAVITY FORM LABELS
================================= */

add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );