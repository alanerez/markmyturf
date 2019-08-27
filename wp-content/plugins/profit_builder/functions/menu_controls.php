<?php

$groups = $this->groups;

$gorups_array = array();
if(is_array($groups)) {
	$gorups_array= array(
		'pbuilder_shortcode_druggables' => array(
			'type' => 'shortcode-holder',
			'groups' => $groups
		)
	);

}
else {
	$gorups_array = array(
		'pbuilder_shortcode_druggables' => array(
			'type' => 'shortcode-holder',
			'collapsible' => false
		)
	);
}


$pbuilder_layouts = array(
	'pbuilder_layout' => array(
		'input_class' => 'pbuilder_layout',
		'type' => 'select',
		'label' => __('Page layout','profit-builder'),
		'std' => 'full-width',
		'options' => array(
			'full-width' => __('Full width','profit-builder'),
			'one-third-left-sidebar' => __('One third left sidebar','profit-builder'),
			'one-third-right-sidebar' => __('One third right sidebar','profit-builder'),
			'one-fourth-left-sidebar' => __('One fourth left sidebar','profit-builder'),
			'one-fourth-right-sidebar' => __('One fourth right sidebar','profit-builder')
			)
	)
);

$pbuilder_buttons = array(
	'pbuilder_save_page' => array(
		'type' => 'button',
		'style' => 'primary',
		'class' => 'pbuilder_save left',
		'label' => __('Save changes','profit-builder'),
		'clear' => 'false',
		'loader' => 'true'
	
	),
	'pbuilder_save_template' => array(
		'type' => 'button',
		'class' => 'pbuilder_save_template left',
		'label' => __('Save template','profit-builder'),
		'clear' => 'false',
	),
	'pbuilder_load_page' => array(
		'type' => 'button',
		'class' => 'pbuilder_load right',
		'label' => __('Load','profit-builder')
	)
);

$output = $gorups_array;

?>