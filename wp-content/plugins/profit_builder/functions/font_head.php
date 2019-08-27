<?php


$admin_fonts = array();
$font_options = array();
$used_fonts_string = '';
//echo "<pre>".print_r($admin_options['font'], true)."</pre>";
//echo "<pre>".print_r($optsDBaso, true)."</pre>";
//echo "<pre>".print_r($admin_fonts, true)."</pre>";
//echo "<pre>".print_r($font_options, true)."</pre>";
//echo "<pre>".print_r($fonts, true)."</pre>";
$fonts = $this->options(" WHERE name like '%_font_family' and value <> 'default' ");
foreach($fonts as $font){
    $font_for = str_replace("_font_family", "", $font->name);
    $font_id = 'pbuilder_'.$font->value.'_';
    $style = 'font-family: '.str_replace('+',' ',$font->value).', serif; ';
    $font_name = $font->value.":";

    if($font_for != 'default_all'){
        $font_options = $this->options(" WHERE name like '".$font_for."%' and name <> '".$font->name."' ");
        foreach($font_options as $font_option){
            if($font_option->name == $font_for."_font_style"){
                $font_style = $font_option->value;
                $italic_pos = strpos($font_style,'italic');
                if ($font_style == 'regular'){
                    $style .= 'font-weight:400; font-style: normal; ';
        		}else if($italic_pos !== false){
                    if ($italic_pos > 0) {
        				$style .= 'font-weight:'.substr($font_style,0,$italic_pos).'; ';
        			}else{
        				$style .= 'font-weight: 400; ';
        			}
        			$style .= 'font-style:italic; ';
        		}else {
        			$style .= 'font-weight:'.$font_style.'; font-style: normal; ';
        		}
                $font_id .= $font_style;
                $font_name.= $font_style;
            }else if($font_option->name == $font_for."_font_size"){
                $style .= 'font-size:'.((int)$font_option->value).'px;';
            }else if($font_option->name == $font_for."_line_height"){
                $style .= 'line-height:'.((int)$font_option->value).'px;';
            }
        }
    }else{
        $font_options = $this->options(" WHERE name like '".$font_for."_font_style%' and name <> '".$font->name."' ");
        $font_id .= $font_options[0]->value;
        $font_name.= $font_options[0]->value;
    }
    //$font_id .= '-css';
    $admin_fonts[$font_for] = $style;

    if(!in_array($font->value, $this->standard_fonts))
        wp_enqueue_style($font_id, '//fonts.googleapis.com/css?family='.$font_name.'&subset=all');
}

//if($used_fonts_string != '')
	//$output .= '<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.$used_fonts_string.'&subset=all">';
$output .= '
<style>';//pbuilder_row
if(array_key_exists('default_all', $admin_fonts) && $admin_fonts['default_all'] != 'default'){
	$output .= '
	.pbuilder_column,#colorbox{
		'.$admin_fonts['default_all'].'
	}';
}else if(!array_key_exists('default_all', $admin_fonts)){
    $theme = wp_get_theme();
    $name = $theme->get('Name');
    if($name != "pbtheme"){
        $output .= '
    	#pbuilder_wrapper * {
    		font-family: Open Sans, serif;
    	}';// font-size:16px;line-height:16px;
    }
}
if(array_key_exists('h1', $admin_fonts) && $admin_fonts['h1'] != 'default')
	$output .= '
	#pbuilder_wrapper h1 {
		'.$admin_fonts['h1'].'
	}';
if(array_key_exists('h2', $admin_fonts) && $admin_fonts['h2'] != 'default')
	$output .= '
	#pbuilder_wrapper h2 {
		'.$admin_fonts['h2'].'
	}';
if(array_key_exists('h3', $admin_fonts) && $admin_fonts['h3'] != 'default')
	$output .= '
	#pbuilder_wrapper h3 {
		'.$admin_fonts['h3'].'
	}';
if(array_key_exists('h4', $admin_fonts) && $admin_fonts['h4'] != 'default')
	$output .= '
	#pbuilder_wrapper h4 {
		'.$admin_fonts['h4'].'
	}';
if(array_key_exists('h5', $admin_fonts) && $admin_fonts['h5'] != 'default')
	$output .= '
	#pbuilder_wrapper h5 {
		'.$admin_fonts['h5'].'
	}';
if(array_key_exists('h6', $admin_fonts) && $admin_fonts['h6'] != 'default')
	$output .= '
	#pbuilder_wrapper h6 {
		'.$admin_fonts['h6'].'
	}';
if(array_key_exists('gallery', $admin_fonts) && $admin_fonts['gallery'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_gallery_image_title {
		'.$admin_fonts['gallery'].'
	}';
if(array_key_exists('slider', $admin_fonts) && $admin_fonts['slider'] != 'default')
	$output .= '
	#pbuilder_wrapper .content-slide {
		'.$admin_fonts['slider'].'
	}';
if(array_key_exists('button', $admin_fonts) && $admin_fonts['button'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_button {
		'.$admin_fonts['button'].'
	}';
if(array_key_exists('bullets', $admin_fonts) && $admin_fonts['bullets'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_bullets_wrapper {
		'.$admin_fonts['bullets'].'
	}';
if(array_key_exists('counter', $admin_fonts) && $admin_fonts['counter'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_scrolling_counter {
		'.$admin_fonts['counter'].'
	}';
if(array_key_exists('percentage_chart', $admin_fonts) && $admin_fonts['percentage_chart'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_perchart_percent {
		'.$admin_fonts['percentage_chart'].'
	}';
if(array_key_exists('piechart', $admin_fonts) && $admin_fonts['piechart'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_charts_wrapper + .frb_charts_legend {
		'.$admin_fonts['piechart'].'
	}';
if(array_key_exists('graph', $admin_fonts) && $admin_fonts['graph'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_graph_wrapper  + .frb_charts_legend {
		'.$admin_fonts['graph'].'
	}';
if(array_key_exists('testimonial_name', $admin_fonts) && $admin_fonts['testimonial_name'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_testimonials_name b{
		'.$admin_fonts['testimonial_name'].'
	}';
if(array_key_exists('testimonial_profession', $admin_fonts) && $admin_fonts['testimonial_profession'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_testimonials_name span{
		'.$admin_fonts['testimonial_profession'].'
	}';
if(array_key_exists('testimonial_quote', $admin_fonts) && $admin_fonts['testimonial_quote'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_testimonials_quote{
		'.$admin_fonts['testimonial_quote'].'
	}';
if(array_key_exists('accordion_title', $admin_fonts) && $admin_fonts['accordion_title'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_accordion h3 {
		'.$admin_fonts['accordion_title'].'
	}';
if(array_key_exists('accordion_content', $admin_fonts) && $admin_fonts['accordion_content'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_accordion .ui-accordion-content {
		'.$admin_fonts['accordion_content'].'
	}';
if(array_key_exists('tabs_title', $admin_fonts) && $admin_fonts['tabs_title'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_tabs ul:first-child li a {
		'.$admin_fonts['tabs_title'].'
	}';
if(array_key_exists('tabs_content', $admin_fonts) && $admin_fonts['tabs_content'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_tabs-content {
		'.$admin_fonts['tabs_content'].'
	}';
if(array_key_exists('alert_text', $admin_fonts) && $admin_fonts['alert_text'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_alert_text {
		'.$admin_fonts['alert_text'].'
	}';
if(array_key_exists('features_title', $admin_fonts) && $admin_fonts['features_title'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_features h3.frb_features_title,
	#pbuilder_wrapper a .frb_features h3.frb_features_title {
		'.$admin_fonts['features_title'].'
	}';
if(array_key_exists('features_content', $admin_fonts) && $admin_fonts['features_content'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_features .frb_features_content,
	#pbuilder_wrapper a .frb_features .frb_features_content {
		'.$admin_fonts['features_content'].'
	}';
if(array_key_exists('percentage_bars', $admin_fonts) && $admin_fonts['percentage_bars'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_percentage_bar h5 {
		'.$admin_fonts['percentage_bars'].'
	}';
if(array_key_exists('creative_post_title', $admin_fonts) && $admin_fonts['creative_post_title'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_creative_post_slider_hover > h3 {
		'.$admin_fonts['creative_post_title'].'
	}';
if(array_key_exists('menu_main', $admin_fonts) && $admin_fonts['menu_main'] != 'default')
	$output .= '
	#pbuilder_wrapper ul.frb_menu_header,
	#pbuilder_wrapper ul.frb_menu.frb_menu_horizontal-clean li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_horizontal-squared li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_horizontal-rounded li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_vertical-clean li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_vertical-squared li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_vertical-rounded li a
	{
		'.$admin_fonts['menu_main'].'
	}';


if(array_key_exists('menu_submenu', $admin_fonts) && $admin_fonts['menu_submenu'] != 'default')
	$output .= '
	#pbuilder_wrapper ul.frb_menu.frb_menu_horizontal-clean li li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_horizontal-squared li li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_horizontal-rounded li li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_vertical-rounded li li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_vertical-rounded li li a,
	#pbuilder_wrapper ul.frb_menu.frb_menu_vertical-rounded li li a,
	#pbuilder_wrapper ul.frb_menu li a,{
		'.$admin_fonts['menu_submenu'].'
	}';
if(array_key_exists('searchbox', $admin_fonts) && $admin_fonts['searchbox'] != 'default')
	$output .= '
	#pbuilder_wrapper input.frb_searchinput {
		'.$admin_fonts['searchbox'].'
	}';
if(array_key_exists('image_desc', $admin_fonts) && $admin_fonts['image_desc'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_image_desc {
		'.$admin_fonts['image_desc'].'
	}';
if(array_key_exists('featured_post_title', $admin_fonts) && $admin_fonts['featured_post_title'] != 'default')
	$output .= '
	#pbuilder_wrapper h3.frb_post_title {
		'.$admin_fonts['featured_post_title'].'
	}';
if(array_key_exists('featured_post_meta', $admin_fonts) && $admin_fonts['featured_post_meta'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_post_meta,
	#pbuilder_wrapper .frb_post_meta a {
		'.$admin_fonts['featured_post_meta'].'
	}';
if(array_key_exists('featured_post_content', $admin_fonts) && $admin_fonts['featured_post_content'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_post_content {
		'.$admin_fonts['featured_post_content'].'
	}';
if(array_key_exists('featured_post_button', $admin_fonts) && $admin_fonts['featured_post_button'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_post a.frb_button {
		'.$admin_fonts['featured_post_button'].'
	}';
if(array_key_exists('pricing_table_price', $admin_fonts) && $admin_fonts['pricing_table_price'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_pricing_table .frb_pricing_table_price span div {
		'.$admin_fonts['pricing_table_price'].'
	}';
if(array_key_exists('pricing_table_title', $admin_fonts) && $admin_fonts['pricing_table_title'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_pricing_table .frb_pricing_table_price span div {
		'.$admin_fonts['pricing_table_title'].'
	}';
if(array_key_exists('pricing_table_text', $admin_fonts) && $admin_fonts['pricing_table_text'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_pricing_table .frb_pricing_row_heading {
		'.$admin_fonts['pricing_table_text'].'
	}';
if(array_key_exists('pricing_table_button', $admin_fonts) && $admin_fonts['pricing_table_button'] != 'default')
	$output .= '
	#pbuilder_wrapper .frb_pricing_table .frb_pricing_table_button {
		'.$admin_fonts['pricing_table_button'].'
	}';



// 			Gather Extensions font_head
$extensionData = $this -> frb_get_extensions();
if($extensionData != false){
	foreach($extensionData as $key => $val) {
	$extensionOutput = require($extensionData[$key]['font_head_url']);
	}
}


	$output .= '
</style>';




?>
