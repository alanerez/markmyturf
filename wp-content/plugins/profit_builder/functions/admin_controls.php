<?php

Class pbuilderControl {
	var $html;
	function __construct($object){
		global $pbuilder;
		$wrapper =
			'<div class="pbuilder_control'.(array_key_exists('hide_if', $object) ? ' pbuilder_hidable' : '').(array_key_exists('class',$object) ? ' '.$object['class'] : '').'"><div class="pbuilder_control_left">'.
			(array_key_exists('label', $object) && array_key_exists('name', $object) ? '<label for="'.$object['name'].'">'.$object['label'].' </label>' : '').
			//(array_key_exists('desc', $object) && !empty($object['desc']) ? '<span class="pbuilder_desc">('.$object['desc'].')</span>' : '').
			'</div><div class="pbuilder_control_right"><div class="pbuilder_control_right_inner">';
		$wrapperFull =
			'<div class="pbuilder_control'.(array_key_exists('hide_if', $object) ? ' pbuilder_hidable' : '').(array_key_exists('class',$object) ? ' '.$object['class'] : '').'"><div class="pbuilder_control_full">'.
			(array_key_exists('label', $object) && array_key_exists('name', $object) ? '<label for="'.$object['name'].'">'.$object['label'].' </label>' : '').
			(array_key_exists('desc', $object) ? '<span class="pbuilder_desc">('.$object['desc'].')</span>' : '').
			'</div><div class="pbuilder_control_full"><div class="pbuilder_control_full_inner">';

		$wrapperClose = (array_key_exists('desc', $object) && !empty($object['desc']) ? '<span class="pbuilder_desc">('.$object['desc'].')</span>' : '').'</div></div><div style="clear:both"></div></div>';

        if(!isset($object['noclear']))
            $wrapperClose .= '<div style="clear:both"></div>';

		$html = '';
		switch($object['type']) {
			case 'margin' :
				$html = '<div style="height:'.$object['height'].'"></div>';
				break;
			case 'heading' :
				$hTypes = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
				if(array_key_exists('size', $object) && in_array($object['size'], $hTypes))
					$hType = $object['size'];
				else
					$hType = 'h3';
				$html .= '<'.$hType.' class="pbuilder_admin_menu_heading">'.$object['label'].'</'.$hType.'>';
				break;
			case 'input' :
				$html .= $wrapper;
				$html .= '<input class="pbuilder_input'.(array_key_exists('hide_if', $object) ? ' pbuilder_hidable_control' : '').'" name="'.$object['name'].'" id="pbuilder_'.$object['type'].'_'.$object['name'].'" '.(array_key_exists('std',$object) && $object['std'] != '' ? 'value="'.$object['std'].'"' : '').'/>';
				$html .= $wrapperClose;
				break;

			case 'textarea' :
				$html .= $wrapperFull;
				$html .= '<textarea class="pbuilder_textarea'.(array_key_exists('hide_if',$object) ? ' pbuilder_hidable_control' : '').'" name="'.$object['name'].'" id="pbuilder_'.$object['type'].'_'.$object['name'].'">'.(array_key_exists('std',$object) && $object['std'] != '' ? $object['std'] : '').'</textarea>';
				$html .= $wrapperClose;
				break;

			case 'marginpadding' :
				$html .= $wrapper;
				$html .= 'MARGIN PADDING';
				$html .= $wrapperClose;
				break;


			case 'checkbox' :
				$html .=
					$wrapper.
					'<div class="pbuilder_checkbox'.(array_key_exists('std',$object) && $object['std'] != '' && $object['std'] == 'true' ? ' active' : '').'"></div>'.
					'<input class="pbuilder_checkbox_input'.(array_key_exists('hide_if',$object) ? ' pbuilder_hidable_control' : '').'" name="'.$object['name'].'" id="pbuilder_'.$object['type'].'_'.$object['name'].'" style="display:none;"'.
					(array_key_exists('std',$object) && $object['std'] == 'true' ? ' value="'.$object['std'].'"' : ' value="false"').' />'.
					'<div class="pbuilder_checkbox_label">'.
					'</div><div style="clear:both;"></div>'.
					$wrapperClose;
				break;


			case 'select' :
				$options = $object['options'];
				$html .= $wrapper;
				$html .= '<select class="'.(array_key_exists('hide_if',$object) ? ' pbuilder_hidable_control' : '').'" name="'.$object['name'].'" id="pbuilder_'.$object['type'].'_'.$object['name'].'" style="display:none;">';
				$visibleSelect = '<div class="pbuilder_select pbuilder_gradient '.(array_key_exists('search',$object) && $object['search'] == 'true' ? ' pbuilder_select_with_search' : '').'" data-name="'.$object['name'].'">';

				$count = 0;
				foreach($options as $x => $valx) {
					$html .= '<option value="'.$x.'"'.((array_key_exists('std',$object) && $object['std'] != '' && $object['std'] == $x) ? ' selected="selected"' : '').'>'.$valx.'</option>';
					if ($count == 0) {
						$visibleSelect .=
							'<span>'.(array_key_exists('std',$object) && $object['std'] != '' ? (isset($options[$object['std']]) ? $options[$object['std']] : $valx) : $valx).'</span>'.
							'<div class="drop_button"></div>'.
							(array_key_exists('search',$object) && $object['search'] == 'true' ? '<input class="pbuilder_select_search" placeholder="Search..." value="" style="display:none" />' : '').
							'<ul style="display:none">'.
							'
						<li><a href="#" data-value="'.$x.'"'.((array_key_exists('std',$object) && $object['std'] != '' && $object['std'] == $x) ? ' class="selected"' : '').'>'.$valx.'</a></li>';
					}
					else {
						$visibleSelect .= '
						<li><a href="#" data-value="'.$x.'">'.$valx.'</a></li>';
					}
					$count++;
				}

				$html .= '</select>';
				$visibleSelect .=
					'</ul>'.
					'<div class="clear"></div>'.
					'</div>';
				$html .= $visibleSelect;
				$html .= $wrapperClose;

				break;

			case 'icon' :
				global $pbuilder_icons;
				$dataMin = ((array_key_exists('notNull',$object) && $object['notNull'] == false) ? 0 : 1);
				$current = ((array_key_exists('std',$object) && $object['std'] != '' ) ? $object['std'] : $pbuilder_icons[$dataMin]);
				$html .= $wrapper;
				$html .= '<input class="'.(array_key_exists('hide_if',$object) ? ' pbuilder_hidable_control' : '').'" type="hidden" name="'.$object['name'].'" id="pbuilder_'.$object['type'].'_'.$object['name'].'" data-current="'.array_search($current,$pbuilder_icons).'" data-min="'.$dataMin.'" value="'.$current.'" /><div class="pbuilder_icon_holder"><i class="'.$current.' fawesome"></i></div><a href="#" class="pbuilder_gradient pbuilder_icon_left"><span></span></a><a href="#" class="pbuilder_gradient pbuilder_icon_right"><span></span></a><a href="#" class="pbuilder_gradient pbuilder_icon_pick">Show all</a>';
				$html .= '<div style="clear:both;"></div><span class="pbuilder_icon_drop_arrow"></span><div class="pbuilder_icon_dropdown">';
				if(array_key_exists('notNull', $object) && $object['notNull'] == false) {
					$html .= '<a href="0" title="No icon"><i class="no-icon fawesome"></i></a>';
				}
				foreach($pbuilder_icons as $x => $valx) {
					$html .= '<a href="'.$x.'"><i class="'.$pbuilder_icons[$x].' fawesome"></i></a>';
				}
				$html .= '<div style="clear:both;"></div></div><div style="clear:both;"></div>';
				$html .= $wrapperClose;
				break;
			case 'image' :
				$html .= $wrapper;
				$html .= '<div class="pbuilder_image_holder" id="pbuilder_'.$object['type'].'_'.$object['name'].'_holder">'.(array_key_exists('std',$object) && $object['std'] != '' ? '<img src="'.$object['std'].'" alt="" />' : '').'</div>';
				$html .= '<div class="pbuilder_image_input"><input class="pbuilder_input'.(array_key_exists('hide_if',$object) ? ' pbuilder_hidable_control' : '').'" name="'.$object['name'].'" id="pbuilder_'.$object['type'].'_'.$object['name'].'" value="'.(array_key_exists('std',$object) && $object['std'] != '' ? $object['std'].'" />' : '" /><span>Enter image url...</span>').'</div>';
				$html .= '<a html="'.$object['name'].'" class="pbuilder_image_button pbuilder_gradient_primary" data-input="pbuilder_'.$object['type'].'_'.$object['name'].'">Upload</a>';
				$html .= '<div style="clear:both;"></div>';
				$html .= $wrapperClose;
				break;


			case 'draggable' :
				$html .= '<div class="pbuilder_draggable pbuilder_gradient" data-shortcode="'.$object['name'].'">'.$object['text'].'<span class="draggable_icon"></span></div>';
				break;

			case 'button' :
				$cl = (array_key_exists('class',$object) ? $object['class'] : '');
				$href = (array_key_exists('href',$object) ? $object['href'] : '#');
				$id = (array_key_exists('id',$object) && $object['id'] != '' ? ' id="'.$object['id'].'"' : '');
				$style = (array_key_exists('style', $object) && $object['style'] == 'primary' ? 'pbuilder_gradient_primary' : 'pbuilder_gradient');
				$align = (array_key_exists('align', $object) && $object['align'] == 'right' ? ' style="float:right;"' : '');

				$html .= '<a'.$id.' href="'.$href.'" class="'.$style.' pbuilder_button '.$cl.'"'.$align.'>'.$object['label'].'</a>'.(!array_key_exists('loader', $object) || $object['loader'] == 'true' ? '<img src="'.$pbuilder->url.'images/save-loader.gif" class="pbuilder_save_loader" alt="" />' : '').(!array_key_exists('clear', $object) || $object['clear'] != 'false' ? '<div style="clear:both;"></div>' : '');
				break;

			case 'number' :
				$min = (array_key_exists('min',$object) && $object['min'] != '' ? $object['min'] : 0);
				$max = (array_key_exists('max',$object) && $object['max'] != '' ? $object['max'] : 100);
				$std = (array_key_exists('std',$object) && $object['std'] != '' ? (int)$object['std'] : 0);
				$unit = (array_key_exists('unit',$object) ? $object['unit'] : '');
				$maxStr = ''.$max;

				$html .= $wrapper;
				$html .= '<div class="pbuilder_number_bar_wrapper"><div class="pbuilder_number_bar" data-min="'.$min.'" data-max="'.$max.'" data-std="'.$std.'" data-unit="'.$unit.'"></div></div><input class="pbuilder_input pbuilder_number_amount'.(array_key_exists('hide_if',$object) ? ' pbuilder_hidable_control' : '').'" name="'.$object['name'].'" id="pbuilder_'.$object['type'].'_'.$object['name'].'" value="'.$std.' '.$unit.'"/><div class="pbuilder_number_button pbuilder_gradient"></div><div style="clear:both;"></div>';
				$html .= $wrapperClose;
				break;

			case 'color' :
				$html .= $wrapper;
				$html .= '<div class="pbuilder_color_wrapper">';
				$html .= '<input class="pbuilder_color pbuilder_input'.(array_key_exists('hide_if',$object) ? ' pbuilder_hidable_control' : '').'" name="'.$object['name'].'" id="pbuilder_'.$object['type'].'_'.$object['name'].'" '.(array_key_exists('std',$object) && $object['std'] != '' ? 'value="'.$object['std'].'"' : '').'/>';
				$html .= '</div>';
				$html .= $wrapperClose;
				break;

			case 'collapsible' :
				$open = @$object['open']=="true";
                $html .= '<div class="pbuilder_collapsible pbuilder_collapsible_'.$this->slugify($object['label']).'"><div class="pbuilder_collapsible_header'.($open?' active':'').'">'.$object['label'].'<span class="pbuilder_collapse_trigger pbuilder_gradient'.($open?' active':'').'">'.($open?'-':'+').'</span></div><div class="pbuilder_collapsible_content"'.($open?' style="display:block"':'').'>';
				if(array_key_exists('options', $object)) {
					foreach($object['options'] as $obj) {
						$newControl = new pbuilderControl($obj);
						$html .= $newControl->html;
					}
				}
				$html .='</div></div>';
				break;

			case 'sortable' :
				$item_name = (array_key_exists('item_name',$object) && $object['item_name'] != '' ? $object['item_name'] : 'item');
				$html .= $wrapper;
				$html .= '<div class="pbuilder_sortable_holder" data-name="'.$object['name'].'" data-iname="'.$item_name.'" id="pbuilder_'.$object['type'].'_'.$object['name'].'">';
				$html .= '<div class="pbuilder_sortable">';


				if(array_key_exists('std',$object) && $object['std'] != '') {
					if(array_key_exists('order',$object['std']) && !empty($object['std']['order'])) {
						foreach ($object['std']['order'] as $x => $valx) {
							$sortid = $valx;
							$html .= '<div class="pbuilder_sortable_item pbuilder_collapsible" data-sortid="'.$sortid.'" data-sortname="'.$object['name'].'"><div class="pbuilder_gradient pbuilder_sortable_handle pbuilder_collapsible_header">'.$item_name.' '.$sortid.' - <span class="pbuilder_sortable_delete">delete</span><span class="pbuilder_collapse_trigger">+</span></div><div class="pbuilder_collapsible_content">';

							$controlObj = $object['options'];
							foreach($controlObj as $y => $valy) {
								if(array_key_exists($y,$object['std']['items'][$sortid])) {
									$controlObj[$y]['std'] = $object['std']['items'][$sortid][$y];
								}
								$controlObj[$y]['name'] = 'fsort-'.$sortid.'-'.$y;
								$newControl = new pbuilderControl($controlObj[$y]);
								$html .= $newControl->html;
							}
							$html .='</div></div>';
						}
					}
				}



				$html .= '</div>';
				$html .= '<a href="#" class="pbuilder_sortable_add pbuilder_gradient_primary pbuilder_button">+ Add new '.$item_name.'</a>';
				$html .= '<div style="clear:both;"></div>';
				$html .= '</div>';
				$html .= $wrapperClose;
				break;
            case 'uploader' :
                $html .= $wrapper;
				$html .= '<div id="'.$object['id'].'">'."\r\n";
				$html .= '<p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p>'."\r\n";
				$html .= '</div>'."\r\n";
				$html .= $wrapperClose;
                break;
            case 'div' :
				//$html .= $wrapperFull;

				$html .= '<div class="pbuilder_div '.(array_key_exists('class',$object)?@$object['name']:'').'" id="'.$object['id'].'">'.(array_key_exists('std',$object) && $object['std'] != '' ? $object['std'] : '').'</div>';
                if(array_key_exists('options', $object)) {
					foreach($object['options'] as $obj) {
						$newControl = new pbuilderControl($obj);
						$html .= $newControl->html;
					}
				}
				//$html .= $wrapperClose;
				break;

		}
		$this->html = $html;

	}

	function slugify($text)
	{
	  // replace non letter or digits by -
	  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

	  // transliterate
		if( function_exists('iconv') ){
			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		} else {
			$text = sanitize_title($text);
		}
	  // remove unwanted characters
	  $text = preg_replace('~[^-\w]+~', '', $text);

	  // trim
	  $text = trim($text, '-');

	  // remove duplicate -
	  $text = preg_replace('~-+~', '-', $text);

	  // lowercase
	  $text = strtolower($text);

	  if (empty($text)) {
		return 'n-a';
	  }

	  return $text;
	}
}

?>
