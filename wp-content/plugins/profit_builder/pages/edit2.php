<!DOCTYPE HTML>
<html>
<head>
<title>Profit Builder edit page</title>

</head>
<body>
<?php wp_head(); ?>
<?php if(!is_admin()) { echo 'admin je';}?>

<?php 
wp_editor( 'content', 'intro' ); ?>
<?php do_action( 'in_admin_footer' ); ?>
<?php
do_action('admin_footer', '');
do_action('admin_print_footer_scripts');
do_action("admin_footer-" . $GLOBALS['hook_suffix']);

// get_site_option() won't exist when auto upgrading from <= 2.7
if ( function_exists('get_site_option') ) {
	if ( false === get_site_option('can_compress_scripts') )
		compression_test();
}

?>
</body>
</html>