<?php
$pbuilder_controls = array(


	'group_background' => array(
		'type' => 'collapsible',
		'label' => __('Background','profit-builder'),
		'open' => 'true',
		'options' => array(
			'back_type' => array(
				'type' => 'select',
				'label' => __('Type','profit-builder'),
				'label_width' => 0.25,
				'control_width' => 0.75,
				'std' => 'static',
				'options' => array(
					'static' => __('Static','profit-builder'),
					'parallax' => __('Fixed','profit-builder'),
					'parallax_animated' => __('Parallax','profit-builder'),
					'video' => __('Video','profit-builder'),
				),
			),
			'back_color' => array(
				'type' => 'color',
				'label' => __('Color','profit-builder'),
				'label_width' => 0.25,
				'control_width' => 0.75,
				'std' => $this->option('row_back_color')->value,
				'hide_if' => array(
					'back_type' => array('video', 'video_fixed', 'video_parallax')
				)

			),
      'back_color2' => array(
				'type' => 'color',
				'label' => __('2nd Color','profit-builder'),
				'label_width' => 0.25,
				'control_width' => 0.75,
				'std' => $this->option('row_back_color2')->value,
				'hide_if' => array(
					'back_type' => array('video', 'video_fixed', 'video_parallax')
				)

			),
      'gradient_type' => array(
				'type' => 'select',
				'label' => __('Gradient Type','profit-builder'),
				'label_width' => 0.25,
				'control_width' => 0.75,
				'std' => 'linear',
				'options' => array(
					'linear' => __('Linear','profit-builder'),
					'radial' => __('Radial','profit-builder'),
				),
        		'hide_if' => array(
					'back_type' => array('video', 'video_fixed', 'video_parallax')
				)
			),
			'back_image' => array(
				'type' => 'image',
				'label' => __('Image','profit-builder'),
				'label_width' => 0.25,
				'control_width' => 0.75,
				'std' => '',
				'hide_if' => array(
					'back_type' => array('video', 'video_fixed', 'video_parallax')
				)
			),
			'back_image_zoom' => array(
				'type' => 'checkbox',
				'label' => __('Image Zoom Effect','profit-builder'),
				'std' => 'false'

			),
			'back_repeat' => array(
				'type' => 'select',
				'label' => __('Style','profit-builder'),
				'label_width' => 0.25,
				'control_width' => 0.75,
				'std' => 'stretched',
				'options' => array(
					'center' => __('Center','profit-builder'),
					'repeat' => __('Repeat','profit-builder'),
					'repeatx' => __('Repeat Horizontal','profit-builder'),
					'stretched' => __('Stretched','profit-builder')
				),
				'hide_if' => array(
					'back_type' => array('video', 'video_fixed', 'video_parallax')
				)

			)
			/*
			'back_repeat' => array(
				'type' => 'checkbox',
				'label' => __('Repeat image','profit-builder'),
				'std' => 'true',
				'hide_if' => array(
					'back_type' => array('video', 'video_fixed', 'video_parallax')
				)
			)*/
		)
	),

	'group_video_back' => array(
		'type' => 'collapsible',
		'label' => __('Video Background','profit-builder'),
		'options' => array(
			'back_video_source' => array(
				'type' => 'select',
				'label' => __('Source','profit-builder'),
				'std' => 'youtube',
				'options' => array(
					'youtube' => __('Youtube','profit-builder'),
					'vimeo' => __('Vimeo','profit-builder'),
					'html5' => __('HTML5','profit-builder')
				),
				'hide_if' => array(
					'back_type' => array('static', 'parallax', 'parallax_animated')
				)
			),
			'back_video_youtube_id' => array(
				'type' => 'input',
				'label' => __('Youtube video ID','profit-builder'),
				'desc' => 'example: tDvBwPzJ7dY',
				'std' => '',
				'hide_if' => array(
					'back_type' => array('static', 'parallax', 'parallax_animated'),
					'back_video_source' => array('vimeo', 'html5')
				)
			),
			'back_video_vimeo_id' => array(
				'type' => 'input',
				'label' => __('Vimeo video ID','profit-builder'),
				'desc' => 'example: 30300114',
				'std' => '',
				'hide_if' => array(
					'back_type' => array('static', 'parallax', 'parallax_animated'),
					'back_video_source' => array('youtube', 'html5')
				)
			),
			'back_video_html5_img' => array(
				'type' => 'input',
				'label' => __('Image poster url','profit-builder'),
				'desc' => 'If no video is supported',
				'std' => '',
				'hide_if' => array(
					'back_type' => array('static', 'parallax', 'parallax_animated'),
					'back_video_source' => array('youtube', 'vimeo')
				)
			),
			'back_video_html5_mp4' => array(
				'type' => 'input',
				'label' => __('MP4 url','profit-builder'),
				'std' => '',
				'hide_if' => array(
					'back_type' => array('static', 'parallax', 'parallax_animated'),
					'back_video_source' => array('youtube', 'vimeo')
				)
			),
			'back_video_html5_webm' => array(
				'type' => 'input',
				'label' => __('WEBM url','profit-builder'),
				'std' => '',
				'hide_if' => array(
					'back_type' => array('static', 'parallax', 'parallax_animated'),
					'back_video_source' => array('youtube', 'vimeo')
				)
			),
			'back_video_html5_ogv' => array(
				'type' => 'input',
				'label' => __('OGV url','profit-builder'),
				'std' => '',
				'hide_if' => array(
					'back_type' => array('static', 'parallax', 'parallax_animated'),
					'back_video_source' => array('youtube', 'vimeo')
				)
			),
			'back_video_loop' => array(
				'type' => 'checkbox',
				'label' => __('Loop video','profit-builder'),
				'std' => 'true',
				'hide_if' => array(
					'back_type' => array('static', 'parallax', 'parallax_animated')
				)
			)
		)
	),
  'group_border' => array(
        'type' => 'collapsible',
        'label' => __('Border', 'profit-builder'),
        'open' => 'true',
        'options' => array(
            'border' => array(
                'type' => 'border',
                'label' => '',
                'label_width' => 0,
                'control_width' => 1,
                'std' => 'false|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000'
            ),
            'border_round' => array(
              'type' => 'number',
              'label' => __('Roundness','profit-builder'),
              'std' => 0,
              'max' => 100,
              'unit' => 'px',
              'label_width' => 0.49,
              'control_width' => 0.51,
            ),
        )
   ),
  

	'group_spacing' => array(
		'type' => 'collapsible',
		'label' => __('<span style="color:#fba708">Margin</span> and <span style="color:#3ba7f5">Padding</span>', 'profit-builder'),
		'open' => 'true',
		'options' => array(
			'margin_padding' => array(
				'type' => 'marginpadding',
				'label' => '',
				'label_width' => 0,
				'control_width' => 1,
				'std' => '0|0|0|0|0|0|0|0'
			)
		)
	),


);


if($this->option('css_classes')->value == 'true') {
	$classControl = array(

	);
	$pbuilder_controls = array_merge($classControl, $pbuilder_controls);

}
$output = $pbuilder_controls;
?>
