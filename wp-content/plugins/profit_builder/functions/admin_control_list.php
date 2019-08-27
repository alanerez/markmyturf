<?php

$pbuilder_google_font_names = $this->get_google_fonts();
$pbuilder_google_font_variants = $this->get_font_variants();

$templates = $this->option('templates');
if ($templates->value != '')
    $templates = json_decode($templates->value, true);
else
    $templates = array();

if (count($templates) <= 0)
    $templates = array("");

$limit = (int) (defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : 96);
$limit = $limit < 96 ? 96 : $limit;

/* exec( 'systeminfo', $output );
  foreach ($output as $value){
  if (preg_match( '|Total Physical Memory\:([^$]+)|', $value, $m)){
  $memory = trim($m[1]);
  break;
  }
  }
  $memory = (int)str_replace(",", "", $memory); */

$pbuilder_admin_options = array(
    'general' => array(
        'label' => __('General options', 'profit-builder'),
        'options' => array(
            array(
                'type' => 'collapsible',
                'open' => 'true',
                'label' => __('Utility options', 'profit-builder'),
                'options' => array(
                    /* array(
                      'type' => 'heading',
                      'label' => __('Utility options','profit-builder')
                      ), */
                    array(
                        'name' => 'save_overwrite',
                        'type' => 'checkbox',
                        'label' => __('Save over post content', 'profit-builder'),
                        'desc' => __('overwrite old post content when saving - recomended', 'profit-builder'),
                        'std' => 'true'
                    ),
                    array(
                        'name' => 'css_classes',
                        'type' => 'checkbox',
                        'label' => __('Show CSS controls', 'profit-builder'),
                        'desc' => __('Display shortcode controls for setting classes', 'profit-builder'),
                        'std' => 'false'
                    ),
                    array(
                        'name' => 'css_custom',
                        'type' => 'textarea',
                        'label' => __('Custom CSS code', 'profit-builder'),
                        'std' => ''
                    ),
                    array(
                        'type' => 'heading',
                        'label' => __('Dimensions', 'profit-builder')
                    ),
                    array(
                        'type' => 'number',
                        'name' => 'bottom_margin',
                        'label' => __('Default module margin', 'profit-builder'),
                        'std' => 24,
                        'unit' => 'px'
                    ),
                    array(
                        'type' => 'number',
                        'name' => 'memory_limit',
                        'label' => __('Change memory limit', 'profit-builder'),
                        'std' => $limit,
                        'min' => 96,
                        'max' => 256,
                        'unit' => 'MB'
                    ),
					array(
                        'type' => 'heading',
                        'label' => __('reCaptcha', 'profit-builder')
                    ),
                    array(
                        'type' => 'input',
                        'name' => 'recaptcha_key',
                        'label' => __('reCaptcha Site Key', 'profit-builder'),
                        'std' => ''
                    ),
					array(
                        'type' => 'input',
                        'name' => 'recaptcha_secret',
                        'label' => __('reCaptcha Secret Key', 'profit-builder'),
                        'std' => ''
                    ),
                )
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Enable live editor for these roles', 'profit-builder'),
                'options' => array()
            ),
            array(
                'type' => 'collapsible',
                'label' => __('High resolution', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'high_rezolution_width',
                        'label' => __('Content width', 'profit-builder'),
                        'type' => 'number',
                        'min' => 900,
                        'max' => 2000,
                        'std' => 1200,
                        'step' => 2,
                        'unit' => 'px'
                    ),
                    array(
                        'name' => 'high_rezolution_margin',
                        'label' => __('Column margin', 'profit-builder'),
                        'type' => 'number',
                        'std' => 48,
                        'unit' => 'px'
                    ),
                )
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Medium resolution', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'med_rezolution_width',
                        'label' => __('Content width', 'profit-builder'),
                        'type' => 'number',
                        'min' => 600,
                        'max' => 1200,
                        'std' => 960,
                        'step' => 2,
                        'unit' => 'px'
                    ),
                    array(
                        'name' => 'med_rezolution_margin',
                        'label' => __('Column margin', 'profit-builder'),
                        'type' => 'number',
                        'std' => 36,
                        'unit' => 'px'
                    ),
                    array(
                        'name' => 'med_rezolution_hide_sidebar',
                        'type' => 'checkbox',
                        'label' => __('Hide sidebar', 'profit-builder'),
                        'std' => 'false'
                    ),
                )
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Low resolution', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'low_rezolution_width',
                        'label' => __('Content width', 'profit-builder'),
                        'type' => 'number',
                        'min' => 400,
                        'max' => 900,
                        'std' => 768,
                        'step' => 2,
                        'unit' => 'px'
                    ),
                    array(
                        'name' => 'low_rezolution_margin',
                        'label' => __('Column margin', 'profit-builder'),
                        'type' => 'number',
                        'std' => 24,
                        'unit' => 'px'
                    ),
                    array(
                        'name' => 'low_rezolution_hide_sidebar',
                        'type' => 'checkbox',
                        'label' => __('Hide sidebar', 'profit-builder'),
                        'std' => 'false'
                    ),
                )
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Mobile devices', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'mob_rezolution_hide_sidebar',
                        'type' => 'checkbox',
                        'label' => __('Hide sidebar', 'profit-builder'),
                        'std' => 'true'
                    ),
                )
            ),
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            )
        )
    ),
    'font' => array(
        'label' => __('Typography options', 'profit-builder'),
        'desc' => __('Font and typography settings for each shortcode. The "Default" option will use the theme fonts.', 'profit-builder'),
        'options' => array(
            array(
                'type' => 'heading',
                'label' => __('Default Font Override', 'profit-builder'),
                'desc' => __('Font and typography settings for each shortcode. The "Default" option will use the theme fonts.', 'profit-builder'),
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Default Font for Elements', 'profit-builder'),
                'open' => 'true',
                'options' => array(
                    array(
                        'name' => 'default_all_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'Open+Sans',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'default_all_font_style',
                        'type' => 'select',
                        'std' => '300',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('default_all_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'default_all_font_family' => array('default')
                        )
                    ),
                /* array(
                  'name' => 'default_all_font_size',
                  'type' => 'number',
                  'label' => __('Font size','profit-builder'),
                  'std' => 16,
                  'unit' => 'px',
                  'hide_if' => array(
                  'default_all_font_family' => array('default')
                  )
                  ),
                  array(
                  'name' => 'default_all_line_height',
                  'type' => 'number',
                  'label' => __('Line height','profit-builder'),
                  'std' => 16,
                  'unit' => 'px',
                  'hide_if' => array(
                  'default_all_font_family' => array('default')
                  )
                  ) */
                )
            ), // default font
            array(
                'type' => 'collapsible',
                'label' => __('Content Block', 'profit-builder'),
                'open' => 'true',
                'options' => array(
                    array(
                        'name' => 'content_block_all_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 13,
                        'unit' => 'px',
                    ),
                /* array(
                  'name' => 'default_all_font_size',
                  'type' => 'number',
                  'label' => __('Font size','profit-builder'),
                  'std' => 16,
                  'unit' => 'px',
                  'hide_if' => array(
                  'default_all_font_family' => array('default')
                  )
                  ),
                  array(
                  'name' => 'default_all_line_height',
                  'type' => 'number',
                  'label' => __('Line height','profit-builder'),
                  'std' => 16,
                  'unit' => 'px',
                  'hide_if' => array(
                  'default_all_font_family' => array('default')
                  )
                  ) */
                )
            ),
            array(
                'name' => 'force_cufon_override',
                'type' => 'checkbox',
                'std' => 'true',
                'label' => __('Force Cufon Override', 'profit-builder'),
            ),
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Heading', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('H1 typography', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'h1_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'h1_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('h1_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'h1_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h1_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h1_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h1_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h1_font_family' => array('default')
                        )
                    )
                )
            ), // h1 font
            array(
                'type' => 'collapsible',
                'label' => __('H2 typography', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'h2_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'h2_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('h2_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'h2_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h2_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h2_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h2_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h2_font_family' => array('default')
                        )
                    )
                )
            ), // h2 font
            array(
                'type' => 'collapsible',
                'label' => __('H3 typography', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'h3_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'h3_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('h3_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'h3_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h3_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h3_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h3_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h3_font_family' => array('default')
                        )
                    )
                )
            ), // h3 font
            array(
                'type' => 'collapsible',
                'label' => __('H4 typography', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'h4_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'h4_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('h4_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'h4_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h4_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h4_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h4_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h4_font_family' => array('default')
                        )
                    )
                )
            ), // h4 font
            array(
                'type' => 'collapsible',
                'label' => __('H5 typography', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'h5_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'h5_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('h5_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'h5_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h5_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h5_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h5_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h5_font_family' => array('default')
                        )
                    )
                )
            ), // h5 font
            array(
                'type' => 'collapsible',
                'label' => __('H6 typography', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'h6_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'h6_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('h6_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'h6_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h6_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h6_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'h6_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'h6_font_family' => array('default')
                        )
                    )
                )
            ), // h6 font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Buttons', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Text font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'button_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'button_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('button_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'button_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'button_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'button_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'button_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'button_font_family' => array('default')
                        )
                    )
                )
            ), // button font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Counter', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Number font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'counter_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'counter_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('counter_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'counter_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'counter_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'counter_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'counter_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'counter_font_family' => array('default')

                        )
                    )
                )
            ), // counter font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Percentage Chart', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Number font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'percentage_chart_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'percentage_chart_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('percentage_chart_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'percentage_chart_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'percentage_chart_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'percentage_chart_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'percentage_chart_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'percentage_chart_font_family' => array('default')
                        )
                    )
                )
            ), // percentage chart font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Piechart', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Legend font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'piechart_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'piechart_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('piechart_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'piechart_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'piechart_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'piechart_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'piechart_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'piechart_font_family' => array('default')
                        )
                    )
                )
            ), // piechart font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Graph', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Legend font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'graph_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'graph_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('graph_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'graph_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'graph_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'graph_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'graph_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'graph_font_family' => array('default')
                        )
                    )
                )
            ), // graph font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Bullet Lists', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Text font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'bullets_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'bullets_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('bullets_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'bullets_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'bullets_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'bullets_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'bullets_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'bullets_font_family' => array('default')
                        )
                    )
                )
            ), // bullets font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Gallery', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Title font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'gallery_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'gallery_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('gallery_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'gallery_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'gallery_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'gallery_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'gallery_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'gallery_font_family' => array('default')
                        )
                    )
                )
            ), // gallery font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Slider', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Text font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'slider_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'slider_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('testimonial_name_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'slider_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'slider_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'slider_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'slider_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'slider_font_family' => array('default')
                        )
                    )
                )
            ), // slider font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Testimonials', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Name font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'testimonial_name_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'testimonial_name_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('testimonial_name_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'testimonial_name_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'testimonial_name_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'testimonial_name_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'testimonial_name_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'testimonial_name_font_family' => array('default')
                        )
                    )
                )
            ), // testimonial_name font
            array(
                'type' => 'collapsible',
                'label' => __('Profession font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'testimonial_profession_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'testimonial_profession_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('testimonial_profession_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'testimonial_profession_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'testimonial_profession_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'testimonial_profession_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'testimonial_profession_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'testimonial_profession_font_family' => array('default')
                        )
                    )
                )
            ), // testimonial_profession font
            array(
                'type' => 'collapsible',
                'label' => __('Quote font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'testimonial_quote_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'testimonial_quote_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('testimonial_quote_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'testimonial_quote_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'testimonial_quote_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'testimonial_quote_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'testimonial_quote_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'testimonial_quote_font_family' => array('default')
                        )
                    )
                )
            ), // testimonial_quote font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Tabs', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Title font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'tabs_title_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'tabs_title_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('tabs_title_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'tabs_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'tabs_title_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'tabs_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'tabs_title_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'tabs_title_font_family' => array('default')
                        )
                    )
                )
            ), // tabs_title font
            array(
                'type' => 'collapsible',
                'label' => __('Content font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'tabs_content_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'tabs_content_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('tabs_content_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'tabs_content_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'tabs_content_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'tabs_content_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'tabs_content_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'tabs_content_font_family' => array('default')
                        )
                    )
                )
            ), // tabs_content font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Accordion', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Title font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'accordion_title_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'accordion_title_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('accordion_title_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'accordion_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'accordion_title_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'accordion_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'accordion_title_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'accordion_title_font_family' => array('default')
                        )
                    )
                )
            ), // accordion_title font
            array(
                'type' => 'collapsible',
                'label' => __('Content font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'accordion_content_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'accordion_content_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('accordion_content_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'accordion_content_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'accordion_content_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'accordion_content_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'accordion_content_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'accordion_content_font_family' => array('default')
                        )
                    )
                )
            ), // accordion_content font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Alert box', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Text font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'alert_text_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'alert_text_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('alert_text_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'alert_text_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'alert_text_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'alert_text_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'alert_text_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'alert_text_font_family' => array('default')
                        )
                    )
                )
            ), // alert_text font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Nav menu', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Main font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'menu_main_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'menu_main_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('menu_main_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'menu_main_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'menu_main_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'menu_main_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'menu_main_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'menu_main_font_family' => array('default')
                        )
                    )
                )
            ), // menu_main font
            array(
                'type' => 'collapsible',
                'label' => __('Submenu font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'menu_submenu_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'menu_submenu_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('menu_submenu_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'menu_submenu_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'menu_submenu_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'menu_submenu_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'menu_submenu_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'menu_submenu_font_family' => array('default')
                        )
                    )
                )
            ), // menu_submenu font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Features', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Title font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'features_title_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'features_title_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('features_title_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'features_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'features_title_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'features_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'features_title_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'features_title_font_family' => array('default')
                        )
                    )
                )
            ), // features_title font
            array(
                'type' => 'collapsible',
                'label' => __('Content font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'features_content_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'features_content_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('features_content_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'features_content_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'features_content_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'features_content_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'features_content_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'features_content_font_family' => array('default')
                        )
                    )
                )
            ), // features_content font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Percentage Bars', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Title font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'percentage_bars_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'percentage_bars_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('percentage_bars_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'percentage_bars_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'percentage_bars_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 20,
                        'unit' => 'px',
                        'hide_if' => array(
                            'percentage_bars_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'percentage_bars_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 24,
                        'unit' => 'px',
                        'hide_if' => array(
                            'percentage_bars_font_family' => array('default')
                        )
                    )
                )
            ), // creative_post_slider font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Creative Post Slider', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Title font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'creative_post_title_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'creative_post_title_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('creative_post_title_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'creative_post_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'creative_post_title_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 20,
                        'unit' => 'px',
                        'hide_if' => array(
                            'creative_post_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'creative_post_title_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 24,
                        'unit' => 'px',
                        'hide_if' => array(
                            'creative_post_title_font_family' => array('default')
                        )
                    )
                )
            ), // creative_post_slider font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Searchbox', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Text font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'searchbox_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'searchbox_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('searchbox_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'searchbox_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'searchbox_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'searchbox_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'searchbox_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'searchbox_font_family' => array('default')
                        )
                    )
                )
            ), // searchbox font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Image', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Description font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'image_desc_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'image_desc_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('image_desc_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'image_desc_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'image_desc_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'image_desc_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'image_desc_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'image_desc_font_family' => array('default')
                        )
                    )
                )
            ), // image_desc font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Pricing table', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Title font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'pricing_table_title_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'pricing_table_title_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('pricing_table_title_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'pricing_table_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'pricing_table_title_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'pricing_table_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'pricing_table_title_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'pricing_table_title_font_family' => array('default')
                        )
                    )
                )
            ), // pricing_table_title font
            array(
                'type' => 'collapsible',
                'label' => __('Price font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'pricing_table_price_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'pricing_table_price_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('pricing_table_price_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'pricing_table_price_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'pricing_table_price_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'pricing_table_price_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'pricing_table_price_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'pricing_table_price_font_family' => array('default')
                        )
                    )
                )
            ), // pricing_table_price font
            array(
                'type' => 'collapsible',
                'label' => __('Button font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'pricing_table_button_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'pricing_table_button_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('pricing_table_button_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(

                            'pricing_table_button_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'pricing_table_button_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 60,
                        'unit' => 'px',
                        'hide_if' => array(
                            'pricing_table_button_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'pricing_table_button_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 60,
                        'unit' => 'px',
                        'hide_if' => array(
                            'pricing_table_button_font_family' => array('default')
                        )
                    )
                )
            ), // pricing_table_button font
            array(
                'type' => 'collapsible',
                'label' => __('Text font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'pricing_table_text_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'pricing_table_text_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('pricing_table_text_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'pricing_table_text_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'pricing_table_text_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'pricing_table_text_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'pricing_table_text_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'pricing_table_text_font_family' => array('default')
                        )
                    )
                )
            ), // pricing_table_button font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            ),
            array(
                'type' => 'heading',
                'label' => __('Featured post', 'profit-builder')
            ),
            array(
                'type' => 'collapsible',
                'label' => __('Title font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'featured_post_title_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'featured_post_title_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('featured_post_title_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'featured_post_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'featured_post_title_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'featured_post_title_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'featured_post_title_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'featured_post_title_font_family' => array('default')
                        )
                    )
                )
            ), // featured_post_title font
            array(
                'type' => 'collapsible',
                'label' => __('Content font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'featured_post_meta_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'featured_post_meta_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('featured_post_meta_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'featured_post_meta_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'featured_post_meta_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'featured_post_meta_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'featured_post_meta_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'featured_post_meta_font_family' => array('default')
                        )
                    )
                )
            ), // featured_post_meta font
            array(
                'type' => 'collapsible',
                'label' => __('Meta links font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'featured_post_excerpt_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'featured_post_excerpt_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('featured_post_excerpt_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'featured_post_excerpt_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'featured_post_excerpt_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'featured_post_excerpt_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'featured_post_excerpt_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'featured_post_excerpt_font_family' => array('default')
                        )
                    )
                )
            ), // featured_post_excerpt font
            array(
                'type' => 'collapsible',
                'label' => __('Button font', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'featured_post_button_font_family',
                        'class' => 'pbuilder_font_select',
                        'type' => 'select', 'search' => 'true',
                        'std' => 'default',
                        'label' => __('Font family', 'profit-builder'),
                        'options' => $pbuilder_google_font_names
                    ),
                    array(
                        'name' => 'featured_post_button_font_style',
                        'type' => 'select',
                        'std' => 'default',
                        'label' => __('Font style', 'profit-builder'),
                        'options' => $this->get_font_variants('featured_post_button_font_family', $pbuilder_google_font_variants),
                        'hide_if' => array(
                            'featured_post_button_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'featured_post_button_font_size',
                        'type' => 'number',
                        'label' => __('Font size', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'featured_post_button_font_family' => array('default')
                        )
                    ),
                    array(
                        'name' => 'featured_post_button_line_height',
                        'type' => 'number',
                        'label' => __('Line height', 'profit-builder'),
                        'std' => 16,
                        'unit' => 'px',
                        'hide_if' => array(
                            'featured_post_button_font_family' => array('default')
                        )
                    )
                )
            ), // featured_post_button font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            )
        )
    ),
    'color' => array(
        'label' => __('Color options', 'profit-builder'),
        'desc' => __('Default colors used when the shortcode is dropped on the page. Specific colors can be set for each shortcode while it is being modified.', 'profit-builder'),
        'options' => array(
            array(
                'type' => 'margin',
                'height' => '40px'
            ),
            array(
                'type' => 'color',
                'name' => 'main_color',
                'label' => __('Main color', 'profit-builder'),
                'std' => '#27a8e1'
            ),
            array(
                'type' => 'color',
                'name' => 'light_main_color',
                'label' => __('Lighter main color', 'profit-builder'),
                'desc' => __('Color used when main color is hovered etc.', 'profit-builder'),
                'std' => '#57bce8'
            ),
            array(
                'type' => 'color',
                'name' => 'dark_back_color',
                'label' => __('Dark background color', 'profit-builder'),
                'std' => '#376a6e'
            ),
            array(
                'type' => 'color',
                'name' => 'light_back_color',
                'label' => __('Light background color', 'profit-builder'),
                'std' => '#f4f4f4'
            ),
            array(
                'type' => 'color',
                'name' => 'dark_border_color',
                'label' => __('Dark border color', 'profit-builder'),
                'std' => '#376a6e'
            ),
            array(
                'type' => 'color',
                'name' => 'light_border_color',
                'label' => __('Light border color', 'profit-builder'),
                'std' => '#ebecee'
            ),
            array(
                'type' => 'color',
                'name' => 'title_color',
                'label' => __('Title color', 'profit-builder'),
                'std' => '#232323'
            ),
            array(
                'type' => 'color',
                'name' => 'text_color',
                'label' => __('Text color', 'profit-builder'),
                'std' => '#808080'
            ),
            array(
                'type' => 'color',
                'name' => 'main_back_text_color',
                'label' => __('Text color over main color', 'profit-builder'),
                'desc' => __('Used in places where main color is the background color', 'profit-builder'),
                'std' => '#ffffff'
            ),
            array(
                'type' => 'color',
                'name' => 'row_back_color',
                'label' => __('Row background color', 'profit-builder'),
                'std' => ''
            ),
            array(
                'type' => 'color',
                'name' => 'column_back_color',
                'label' => __('Column background color', 'profit-builder'),
                'std' => ''
            ),
            array(
                'type' => 'number',
                'name' => 'column_back_opacity',
                'label' => __('Column background opacity', 'profit-builder'),
                'std' => 100,
                'unit' => '%'
            ),
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            )
        )
    ),
    'integrations' => array(
        'label' => __('Miscellaneous options', 'profit-builder'),
        'desc' => __('Miscellaneous configuration options for features in ProfitBuilder', 'profit-builder'),
        'options' => array(
            /* array(
              'type' => 'collapsible',
              'open' => 'true',
              'label' => __('Social Media Settings','profit-builder'),
              'options' => array(

              array(
              'name' => 'activate',
              'type' => 'checkbox',
              'label' => __('Activate on ALL Posts','profit-builder'),
              'std' => 'false'
              ),

              )
              ), // featured_post_button font */
            array(
                'type' => 'collapsible',
                'open' => 'true',
                'label' => __('Facebook Settings', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'url_to_comment',
                        'type' => 'input',
                        'label' => 'URL to comment on:',
                        'desc' => '',
                        'std' => get_bloginfo("url"),
                        'class' => 'fb_pbuilder_control',
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'fb_language',
                        'std' => 'en_US',
                        'label' => 'Language',
                        'desc' => '',
                        'class' => 'fb_pbuilder_control',
                        'options' => $this->get_languages(),
                    ),
                    array(
                        'name' => 'fb_no_posts',
                        'type' => 'input',
                        'label' => 'Number of posts:',
                        'desc' => 'The number of posts to display by default',
                        'std' => '10',
                        'class' => 'fb_pbuilder_control',
                    ),
                    array(
                        'name' => 'fb_width',
                        'type' => 'input',
                        'label' => 'Width:',
                        'desc' => '',
                        'std' => '100',
                        'class' => 'fb_pbuilder_control',
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'fb_width_type',
                        'std' => '%',
                        'label' => 'Width Type',
                        'desc' => '',
                        'class' => 'fb_pbuilder_control',
                        'options' => array(
                            'px' => "px",
                            '%' => "%",
                        ),
                    ),
                    array(
                        'name' => 'fb_color_scheme',
                        'type' => 'select',
                        'search' => 'true',
                        'std' => 'light',
                        'label' => 'Color Scheme',
                        'desc' => '',
                        'class' => 'fb_pbuilder_control',
                        'options' => array(
                            'light' => "Light",
                            'dark' => "Dark",
                        ),
                    ),
                    array(
                        'name' => 'fb_form_title',
                        'type' => 'input',
                        'label' => 'Form Title:',
                        'desc' => 'Just in case you need to add a title above your comment form, e.g., &lt;h3&gt;Comments&lt;/h3&gt;',
                        'std' => '',
                        'class' => 'fb_pbuilder_control',
                    ),
                    array(
                        'type' => 'input',
                        'name' => 'fb_source_url',
                        'label' => 'Source URL:',
                        'desc' => 'Facebook Comments Source URL',
                        'std' => '',
                        'class' => 'fb_pbuilder_control',
                    ),
                )
            ), // featured_post_button font
            array(
                'type' => 'button',
                'class' => 'pbuilder_save',
                'label' => __('Save options', 'profit-builder')
            )
        )
    ),
    'templates' => array(
        'label' => __('Templates Management', 'profit-builder'),
        'options' => array(
            array(
                'type' => 'collapsible',
                'open' => 'true',
                'label' => __('Import/Export', 'profit-builder'),
                'options' => array(
                    array(
                        'name' => 'pb_page_templates',
                        'class' => 'pbuilder_page_templates_select',
                        'type' => 'select',
                        'search' => 'true',
                        //'std' => 'default',
                        'noclear' => true,
                        'label' => __('Select Template', 'profit-builder'),
                        'options' => $templates
                    ),
                    array(
                        'type' => 'button',
                        'class' => 'pbuilder_page_template_remove',
                        'label' => __('Delete', 'profit-builder'),
                        'clear' => 'false',
                    ),
                    array(
                        'type' => 'button',
                        'class' => 'pbuilder_page_template_export',
                        'label' => __('Export', 'profit-builder')
                    ),
                    array(
                        'id' => 'pbuilder_page_template_import',
                        'type' => 'uploader',
                    ),
                ),
            ),
            array(
                'type' => 'collapsible',
                'open' => 'true',
                'label' => __('Install Templates', 'profit-builder'),
                'options' => array(
                    array(
                        'type' => 'div',
                        'id' => 'pb_page_templates',
                        'class' => 'pbuilder_page_templates_select',
                        'search' => 'true',
                        'std' => $this->get_templates_list(),
                    ),
                ),
            ),
        ),
    ),
);

$output = $pbuilder_admin_options;
?>
