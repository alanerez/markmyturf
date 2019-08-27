<!DOCTYPE HTML>
<html>
<head>
<title>ProfitBuilder edit page</title>


<?php
global $imscpb_lc;
$id = (isset($_GET['p']) ? (int)$_GET['p'] : 0);
if(isset($_GET['p']) && isset($_GET['sw']) && $_GET['sw'] == 'on') {
	$this->ajax_switch(true);
}
$this->refresh_shortcode_list();
$this->refresh_variables();
$builder = $this->database($id, true);
$items = json_decode(stripcslashes($builder->items));



//Update Row Border
foreach($items->rows as $rowid=>$rowdata){
    if(isset($items->rows[$rowid]->options) && !isset($items->rows[$rowid]->options->border)){
       if(isset($items->rows[$rowid]->options->border_width) &&
          isset($items->rows[$rowid]->options->border_style) &&
          isset($items->rows[$rowid]->options->border_color)){
            $items->rows[$rowid]->options->border = 'false|'.$items->rows[$rowid]->options->border_width.'|'.$items->rows[$rowid]->options->border_style.'|'.$items->rows[$rowid]->options->border_color.'|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';
       } else {
            $items->rows[$rowid]->options->border = 'false|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';
       }
    }  
}


//Update Column Border
foreach($items->columns as $rowid=>$rowcolumns){
  foreach($rowcolumns as $colid=>$column){
    if(isset($items->columns->{$rowid}->{$colid}->options) && !isset($items->columns->{$rowid}->{$colid}->options->border)){
       if(isset($items->columns->{$rowid}->{$colid}->options->border_width) &&
          isset($items->columns->{$rowid}->{$colid}->options->border_style) &&
          isset($items->columns->{$rowid}->{$colid}->options->border_color)){
            $items->columns->{$rowid}->{$colid}->options->border = 'false|'.$items->columns->{$rowid}->{$colid}->options->border_width.'|'.$items->columns->{$rowid}->{$colid}->options->border_style.'|'.$items->columns->{$rowid}->{$colid}->options->border_color.'|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';
       } else {
            $items->columns->{$rowid}->{$colid}->options->border = 'false|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';
       }
    }
  } 
}

$items = json_encode($items);
echo "<script type='text/javascript' src='".admin_url('admin-ajax.php')."?action=pbuilder_admin_fonts'></script>";




echo
'<script type="text/javascript">
//<![CDATA[
	var ajaxurl = "'. admin_url('admin-ajax.php').'";
	var post_id = '.$id.';
	var the_title = "'.get_the_title( $id ).'";
	var pbuilder_sw = "'.$builder->switch.'";
	var pbuilder_items = '.$items.';
  var pbuilder_user = '.(current_user_can('edit_post', $id) ? 'true' : 'false').';
	var pbuilder_main_menu = '.json_encode($this->menu_controls).';
	var pbuilder_row_controls = '.json_encode($this->row_controls).';
  var pbuilder_column_controls = '.json_encode($this->column_controls).';
	var pbuilder_shortcodes = '.json_encode($this->shortcodes).';
	var pbuilder_hideifs = '.json_encode($this->hideifs).';
	var pbuilder_rows = '.json_encode($this->rows).';
	var pbuilder_icons = '.$this->icons.';
	var pbuilder_url = "'.$this->url.'";
	var pbuilder_showall = "'.$this->showall.'";
	var pagenow = 0;
	var pbuilderl = '.(int)$imscpb_lc->GetLevelNo().';
	var postion = {
		top: 0,
		left:0
	};
	var pbuilder_ssl = '.(is_ssl() ? '"https"' : '"http"').';
    var isRtl = false;
//]]>
</script>';//var fontsObj = '.$this->get_google_fonts(true).';

global $title, $hook_suffix, $current_screen, $screen, $wp_locale, $pagenow, $wp_version,
	$current_site, $update_title, $total_update_count, $parent_file;

$screen = set_current_screen();
?>
<script>
	var frbPagePermalink = <?php echo json_encode(get_permalink($id)); ?>;
	var frbPageEditlink = <?php echo json_encode(get_edit_post_link($id)); ?>;
</script>
<?php

do_action('admin_enqueue_styles');
do_action('admin_print_styles');
do_action('admin_enqueue_scripts');
do_action('admin_print_scripts');
do_action('admin_head');
do_action('pbuilder_head');
 ?>
</head>
<body style="margin: 0;">
<div id="pbuilder_editor_popup_shadow"></div>
<div id="pbuilder_editor_popup">
<div class="pbuilder_module_controls pbuilder_gradient"><span class="pbuilder_module_name"><?php _e('Text editor', 'profit-builder'); ?></span> <a href="#" class="pbuilder_close" title="close"></a></div>
<div id="pbuilder_editor_popup_inner"><div id="postdivrich">
<?php
wp_editor( 'content', 'pbuilder_editor', array('textarea_rows' => 3) );
?>

</div>
</div>
<div class="pbuilder_editor_popup_buttons">
<a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close left">Close</a><a href="#" class="pbuilder_gradient_green pbuilder_gradient pbuilder_button pbuilder_popup_edit_submit right">Save Content</a>
<div style="clear: both;"></div>
</div>
<div style="clear: both;"></div></div>
<?php do_action('pbuilder_edit_page_before_body'); ?>
<div id="pbuilder_body" <?php if($this->showall && !current_user_can('edit_post', $id)) echo 'style="border-left-width:0;"'; ?>>
	<div id="pbuilder_body_inner">
		<div id="pbuilder_frame_cover"></div>
        <?php
        $url = get_permalink($id);
        if(substr_count(admin_url(), "https://") > 0 && substr_count($url, "https://") <= 0)
            $url = str_replace("http://", "https://", $url);
        ?>
		<iframe id="pbuilder_body_frame" src="<?php echo $url; ?>" allowfullscreen></iframe>
        <?php do_action('pbuilder_edit_page_body'); ?>
	</div>
</div>
<?php do_action('pbuilder_edit_page_after_body'); ?>
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
