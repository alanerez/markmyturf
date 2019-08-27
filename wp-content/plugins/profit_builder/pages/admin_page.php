<?php

$optsDB = $this->option();
$optsDBaso = Array();
$opts = $this->admin_controls;
if(isset($_GET['section']) && $_GET['section'])
    $section = $_GET['section'];
else
    $section = 'general';

if ($section == 'general') {
    $sel = -1;
    for ($index = 0; $index < count($opts[$section]['options']); $index++) {
        if ($opts[$section]['options'][$index]['label'] == 'Enable live editor for these roles') {
            $sel = $index;
            break;
        }
    }
    if ($sel != -1) {
        global $wp_roles;
        $roles = $wp_roles->get_names();
        $allowed_roles = array('administrator', 'editor', 'author', 'contributor');
        foreach ($roles as $key => $value) {
            if (in_array($key, $allowed_roles))
                $opts[$section]['options'][$sel]['options'][] = array(
                    'name' => 'enable_editor_for_' . $key,
                    'type' => 'checkbox',
                    'label' => $value,
                    'desc' => '',
                    'std' => 'true'
                );
        }
    }
}

$controls = $opts[$section]['options'];

$hideifs = $this->get_admin_hideifs($controls);
echo "<script type='text/javascript' src='" . admin_url('admin-ajax.php') . "?action=pbuilder_admin_fonts'></script>";

echo '
<script type="text/javascript">
	var hideIfs = ' . json_encode($hideifs) . ';
    var pbuilder_url = "' . $this->url . '";
</script>'; //var fontsObj = '.$this->get_google_fonts(true).';

echo '<div id="pbuilder_admin_menu" class="wrap pbuilder_controls_wrapper" style="margin:0px;">';
echo '<h1 style="margin-left: -20px;" class="pbuilder_admin_menu_header">';
echo '<img src="' . IMSCPB_URL . '/images/logob.png">';
echo '<ul style="margin-top:10px;">
<li><a href="https://imsuccesscenter.com/customercare/"><i class="dashicons dashicons-sos"></i></a></li>
<li><a href="http://wpprofitbuilder.com/"><i class="dashicons dashicons-admin-site"></i></a></li>
</ul>';
echo '</h1>';


echo '<ul class="pbuilder_admin_menu_tabs">';
foreach ($opts as $key => $val) {
	if($key == 'templates') continue;
    echo '<li><a href="' . esc_url(site_url('/')) . 'wp-admin/admin.php?page=profitbuilder&section=' . $key . '" ' . (($section == $key) ? 'class="active pbuilder_gradient_primary"' : 'class="pbuilder_gradient"') . '><span>' . $val['label'] . '</span></a></li>';
}

echo '</ul><div style="clear:both;"></div>';
echo '<h2 class="pbuilder_admin_menu_main_title" style="text-align:left; margin:10px 0;">' . $opts[$section]['label'] . '</h2>';
if (array_key_exists('desc', $opts[$section]))
    echo '<span class="pbuilder_admin_menu_main_description">' . $opts[$section]['desc'] . '</span>';
if (is_array($controls)) {

    foreach ($optsDB as $id => $oo) {
        $optsDBaso[$oo->name] = $oo->value;
    }
    foreach ($controls as $control) {
        if ($control['type'] == 'collapsible') {
            if (array_key_exists('options', $control))
                foreach ($control['options'] as $ok => $ov) {
                    if (array_key_exists('name', $control['options'][$ok]) && array_key_exists($control['options'][$ok]['name'], $optsDBaso)) {
                        $control['options'][$ok]['std'] = $optsDBaso[$control['options'][$ok]['name']];
                    }
                }
            if (array_key_exists('name', $control) && array_key_exists($control['name'], $optsDBaso)) {
                $control['std'] = $optsDBaso[$control['name']];
            }
        } else {
            if (array_key_exists('name', $control) && array_key_exists($control['name'], $optsDBaso)) {
                $control['std'] = $optsDBaso[$control['name']];
            }
        }
        echo $this->get_admin_control($control);
    }
}

echo '</div>';


?>
