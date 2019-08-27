<?php


$optsDB = $this->option();
$optsDBaso = Array();
$opts = $this->admin_controls;

$controls = $opts['templates']['options'];

$hideifs = $this->get_admin_hideifs($controls);
echo "<script type='text/javascript' src='" . admin_url('admin-ajax.php') . "?action=pbuilder_admin_fonts'></script>";

echo '
<script type="text/javascript">
	var hideIfs = ' . json_encode($hideifs) . ';
    var pbuilder_url = "' . $this->url . '";
</script>'; //var fontsObj = '.$this->get_google_fonts(true).';

echo '<div id="pbuilder_admin_menu" class="pbuilder_admin_menu_templates wrap pbuilder_controls_wrapper" style="margin:0px;">';

echo '<div id="pbuilder_admin_menu" class="wrap pbuilder_controls_wrapper" style="margin:0px 0px 20px 0px;">';
echo '<h1 style="margin-left: -20px;" class="pbuilder_admin_menu_header">';
echo '<img src="' . IMSCPB_URL . '/images/logob.png">';
echo '<ul style="margin-top:10px;">
<li><a href="https://imsuccesscenter.com/customercare/"><i class="dashicons dashicons-sos"></i></a></li>
<li><a href="http://wpprofitbuilder.com/"><i class="dashicons dashicons-admin-site"></i></a></li>
</ul>';
echo '</h1></div>';

echo $this->get_tag_filters_html();

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
