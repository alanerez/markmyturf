<?php

//$admin_optionsDB = $this->option();
//$admin_options = $this->admin_controls;
$general_options = array();
/*if(array_key_exists('general', $admin_options) && array_key_exists('options', $admin_options['general']))
foreach ($admin_options['general']['options'] as $general_ctrl) {
	if(array_key_exists('type',$general_ctrl) && $general_ctrl['type'] != 'collapsible') {
		if(array_key_exists('name', $general_ctrl) && array_key_exists('std',$general_ctrl)) {
			
			$general_options[$general_ctrl['name']] = $general_ctrl['std'];
			foreach($admin_optionsDB as $optt) {
				if($optt->name == $general_ctrl['name']) {
					if(isset($optt->value)) {
						$general_options[$general_ctrl['name']] = $optt->value;
					}
					break;
				}
			}
		}
	}
	else {
		foreach ($general_ctrl['options'] as $collapsible_ctrl) {
			if(array_key_exists('name',$collapsible_ctrl)) {
				if(array_key_exists('name', $collapsible_ctrl) && array_key_exists('std',$collapsible_ctrl)) {
					
					$general_options[$collapsible_ctrl['name']] = $collapsible_ctrl['std'];
					foreach($admin_optionsDB as $optt) {
						if($optt->name == $collapsible_ctrl['name']) {
							if(isset($optt->value)) {
								$general_options[$collapsible_ctrl['name']] = $optt->value;
							}
							break;
						}
					}
				}
			}
		}
	}
}*/

$high_rezolution_margin = $this->option("high_rezolution_margin");
$high_rezolution_width = $this->option("high_rezolution_width");
$med_rezolution_margin = $this->option("med_rezolution_margin");
$med_rezolution_width = $this->option("med_rezolution_width");
$low_rezolution_margin = $this->option("low_rezolution_margin");
$low_rezolution_width = $this->option("low_rezolution_width");
$css_custom = $this->option("css_custom");


$general_options['high_rezolution_margin'] = isset($high_rezolution_margin->value) && !empty($high_rezolution_margin->value)?(int)$high_rezolution_margin->value:36;//(int) $general_options['high_rezolution_margin'];
$general_options['high_rezolution_width'] = isset($high_rezolution_width->value) && !empty($high_rezolution_width->value)?(int)$high_rezolution_width->value:1200;//(int) $general_options['high_rezolution_width'];
$general_options['med_rezolution_margin'] = isset($med_rezolution_margin->value) && !empty($med_rezolution_margin->value)?(int)$med_rezolution_margin->value:18;//(int) $general_options['med_rezolution_margin'];
$general_options['med_rezolution_width'] = isset($med_rezolution_width->value) && !empty($med_rezolution_width->value)?(int)$med_rezolution_width->value:768;//(int) $general_options['med_rezolution_width'];
$general_options['low_rezolution_margin'] = isset($low_rezolution_margin->value) && !empty($low_rezolution_margin->value)?(int)$low_rezolution_margin->value:12;//(int) $general_options['low_rezolution_margin'];
$general_options['low_rezolution_width'] = isset($low_rezolution_width->value) && !empty($low_rezolution_width->value)?(int)$low_rezolution_width->value:640;//(int) $general_options['low_rezolution_width'];
$general_options['css_custom'] = isset($css_custom->value) && !empty($css_custom->value)?$css_custom->value:'';


$output ='
<style>
#pbuilder_content_wrapper .pbuilder_row > div:last-child, #pbuilder_content_wrapper .pbuilder_row > .pbuilder_row_colwrapper, .anivia_row > div:last-child,  #pbuilder_wrapper.pbuilder_wrapper_one-fourth-right-sidebar, #pbuilder_wrapper.pbuilder_wrapper_one-fourth-left-sidebar, #pbuilder_wrapper.pbuilder_wrapper_one-third-right-sidebar, #pbuilder_wrapper.pbuilder_wrapper_one-third-left-sidebar {
	margin: 0px -'.($general_options['high_rezolution_margin']/2).'px;
}

.pbuilder_column.pbuilder_column-1-1, .pbuilder_column.pbuilder_column-1-2, .pbuilder_column.pbuilder_column-1-3, .pbuilder_column.pbuilder_column-2-3, .pbuilder_sidebar.pbuilder_one-fourth-right-sidebar, .pbuilder_sidebar.pbuilder_one-fourth-left-sidebar, .pbuilder_sidebar.pbuilder_one-third-right-sidebar, .pbuilder_sidebar.pbuilder_one-third-left-sidebar, .pbuilder_column.pbuilder_column-1-4, .pbuilder_column.pbuilder_column-3-4, .pbuilder_column.pbuilder_column-1-5, .pbuilder_column.pbuilder_column-2-5, .pbuilder_column.pbuilder_column-3-5, .pbuilder_column.pbuilder_column-4-5, .pbuilder_wrapper_one-fourth-left-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-third-left-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-fourth-right-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-third-right-sidebar #pbuilder_content_wrapper{
	border-right:'.($general_options['high_rezolution_margin']/2).'px solid transparent;
	border-left:'.($general_options['high_rezolution_margin']/2).'px solid transparent;
}

@media screen and (max-width: '.$general_options['high_rezolution_width'].'px) {

	#pbuilder_content_wrapper .pbuilder_row > div:last-child, .anivia_row > div:last-child,  #pbuilder_wrapper.pbuilder_wrapper_one-fourth-right-sidebar, #pbuilder_wrapper.pbuilder_wrapper_one-fourth-left-sidebar, #pbuilder_wrapper.pbuilder_wrapper_one-third-right-sidebar, #pbuilder_wrapper.pbuilder_wrapper_one-third-left-sidebar {
		margin: 0px -'.($general_options['med_rezolution_margin']/2).'px;
	}
	.pbuilder_column.pbuilder_column-1-1, .pbuilder_column.pbuilder_column-1-2, .pbuilder_column.pbuilder_column-1-3, .pbuilder_column.pbuilder_column-2-3, .pbuilder_sidebar.pbuilder_one-fourth-right-sidebar, .pbuilder_sidebar.pbuilder_one-fourth-left-sidebar, .pbuilder_sidebar.pbuilder_one-third-right-sidebar, .pbuilder_sidebar.pbuilder_one-third-left-sidebar, .pbuilder_column.pbuilder_column-1-4, .pbuilder_column.pbuilder_column-3-4, .pbuilder_column.pbuilder_column-1-5, .pbuilder_column.pbuilder_column-2-5, .pbuilder_column.pbuilder_column-3-5, .pbuilder_column.pbuilder_column-4-5, .pbuilder_wrapper_one-fourth-left-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-third-left-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-fourth-right-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-third-right-sidebar #pbuilder_content_wrapper{
		border-right:'.($general_options['med_rezolution_margin']/2).'px solid transparent;
		border-left:'.($general_options['med_rezolution_margin']/2).'px solid transparent;
	}
}
@media screen and (max-width: '.$general_options['med_rezolution_width'].'px) {
	#pbuilder_content_wrapper .pbuilder_row > div:last-child, .anivia_row > div:last-child,  #pbuilder_wrapper.pbuilder_wrapper_one-fourth-right-sidebar, #pbuilder_wrapper.pbuilder_wrapper_one-fourth-left-sidebar, #pbuilder_wrapper.pbuilder_wrapper_one-third-right-sidebar, #pbuilder_wrapper.pbuilder_wrapper_one-third-left-sidebar {
		margin: 0px -'.($general_options['low_rezolution_margin']/2).'px;
	}

	.pbuilder_column.pbuilder_column-1-1, .pbuilder_column.pbuilder_column-1-2, .pbuilder_column.pbuilder_column-1-3, .pbuilder_column.pbuilder_column-2-3, .pbuilder_sidebar.pbuilder_one-fourth-right-sidebar, .pbuilder_sidebar.pbuilder_one-fourth-left-sidebar, .pbuilder_sidebar.pbuilder_one-third-right-sidebar, .pbuilder_sidebar.pbuilder_one-third-left-sidebar, .pbuilder_column.pbuilder_column-1-4, .pbuilder_column.pbuilder_column-3-4, .pbuilder_column.pbuilder_column-1-5, .pbuilder_column.pbuilder_column-2-5, .pbuilder_column.pbuilder_column-3-5, .pbuilder_column.pbuilder_column-4-5, .pbuilder_wrapper_one-fourth-left-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-third-left-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-fourth-right-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-third-right-sidebar #pbuilder_content_wrapper{
		border-right:'.($general_options['low_rezolution_margin']/2).'px solid transparent;
		border-left:'.($general_options['low_rezolution_margin']/2).'px solid transparent;
	}
}
@media screen and (max-width: '.$general_options['low_rezolution_width'].'px) {
	.pbuilder_column.pbuilder_column-1-1, .pbuilder_column.pbuilder_column-1-2, .pbuilder_column.pbuilder_column-1-3, .pbuilder_column.pbuilder_column-2-3, .pbuilder_sidebar.pbuilder_one-fourth-right-sidebar, .pbuilder_sidebar.pbuilder_one-fourth-left-sidebar, .pbuilder_sidebar.pbuilder_one-third-right-sidebar, .pbuilder_sidebar.pbuilder_one-third-left-sidebar, .pbuilder_column.pbuilder_column-1-4, .pbuilder_column.pbuilder_column-3-4, .pbuilder_column.pbuilder_column-1-5, .pbuilder_column.pbuilder_column-2-5, .pbuilder_column.pbuilder_column-3-5, .pbuilder_column.pbuilder_column-4-5, .pbuilder_wrapper_one-fourth-left-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-third-left-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-fourth-right-sidebar #pbuilder_content_wrapper, .pbuilder_wrapper_one-third-right-sidebar #pbuilder_content_wrapper{
		width:100%;
		border-width:0;
	}
	.frb_pricing_column_label {
		display:none;
	}
	.frb_pricing_container_1col table {
		width:100% !important;
	}
	.frb_pricing_container_2col table{
		width:200% !important;
	}
	.frb_pricing_container_3col table{
		width:300% !important;
	}
	.frb_pricing_container_4col table{
		width:400% !important;
	}
	.frb_pricing_container_5col table{
		width:500% !important;
	}
	.frb_pricing_table td {
		border-right:0 !important;
	}
	#pbuilder_content_wrapper .pbuilder_row > div:last-child, .anivia_row > div:last-child,  .pbuilder_wrapper_one-third-left-sidebar .pbuilder_row, .pbuilder_wrapper_one-third-right-sidebar .pbuilder_row, .pbuilder_wrapper_one-fourth-left-sidebar .pbuilder_row, .pbuilder_wrapper_one-fourth-right-sidebar .pbuilder_row, .pbuilder_row_controls  {
		margin: 0px;
	}
	.frb_pricing_controls,
	.frb_pricing_section_responsive,
	.frb_pricing_label_responsive {
		display:block;
	}


}



</style>
';
if(array_key_exists('css_custom', $general_options) && $general_options['css_custom'] != '') {
	$output .='
<style>
'.$general_options['css_custom'].'
</style>
';
}

?>