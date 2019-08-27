<?php
		$html = '';
		$sidebar = false;



		if($builder->items != '{}') {
			$items = json_decode(stripslashes($builder->items), true);
			if(array_key_exists('sidebar', $items)
				&& array_key_exists('active', $items['sidebar'])
				&& array_key_exists('items', $items['sidebar'])
				&& array_key_exists('type', $items['sidebar'])
				&& $items['sidebar']['active'] == true) {
				$sidebar = $items['sidebar']['type'];
				$html = '<div class="pbuilder_sidebar pbuilder_'.$items['sidebar']['type'].' pbuilder_row" data-rowid="sidebar"><div class="pbuilder_column">';
				if(is_array($items['sidebar']['items'])) {

					foreach($items['sidebar']['items'] as $sh) {
						if(!is_null($items['items'][$sh])) {
							$html .= '<div class="pbuilder_module" data-shortcode="'.$items['items'][$sh]['slug'].'" data-modid="'.$sh.'">';
							$html .= $this->get_shortcode($items['items'][$sh]);
							$html .= '</div>';
						}
					}

				}
				$html .= '</div><div style="clear:both;"></div></div>';
			}

		}

      if(current_user_can('edit_pages')){
          $output .=
          '<div id="pbuilder_wrapper"'.($builder->items == '{}' ? ' class="empty"' : '').($sidebar != false ? ' class="pbuilder_wrapper_'.$sidebar.'"' : '').'>
            '.($builder->items == '{}'?'<div id="pbuilder_empty_buttons"><ul class="pbuilder_buttons_blue_big">
              <li class="pbuilder_load"><img src="'.IMSCPB_URL.'/images/buttons/blue_load_template.png" /></li>
              <li class="pbuilder_load"><img src="'.IMSCPB_URL.'/images/buttons/blue_load_from_page.png" /></li>
              <li class="pbuilder_build_new_page"><img src="'.IMSCPB_URL.'/images/buttons/blue_build_new_page.png" /></li>
            </ul></div>':'').
            $html.'
            <div id="pbuilder_content_wrapper"'.($sidebar != false ? ' class="pbuilder_content_'.$sidebar.'"' : '').'>
              <div id="pbuilder_content">
          ';
      } else {
        $output .=
          '<div id="pbuilder_wrapper"'.($builder->items == '{}' ? ' class="empty"' : '').($sidebar != false ? ' class="pbuilder_wrapper_'.$sidebar.'"' : '').'>
            '.$html.'
            <div id="pbuilder_content_wrapper"'.($sidebar != false ? ' class="pbuilder_content_'.$sidebar.'"' : '').'>
              <div id="pbuilder_content">
          ';
      }

		if($builder->items != '{}') {
			$rows = $this->rows;

			for($rowId = 0; $rowId<$items['rowCount']; $rowId++) {
				if(array_key_exists($rowId, $items['rowOrder']))
					$row = $items['rowOrder'][$rowId];
				else
					$row = null;
        
        
        $dtNow = new DateTime('now', new DateTimeZone('UTC'));
        $dtNow->modify("+3 days");
  
        if(!current_user_can('edit_pages') && $items['rows'][$row]['options']['schedule_display']=='true'){
          if(!isset($items['rows'][$row]['options']['schedule_startdate'])){
            $items['rows'][$row]['options']['schedule_startdate']=$dtNow->format("Y/m/d H:i:s O");
          }
          if(!isset($items['rows'][$row]['options']['schedule_enddate'])){
            $items['rows'][$row]['options']['schedule_enddate']=$dtNow->format("Y/m/d H:i:s O");
          }
          $start_time=strtotime($items['rows'][$row]['options']['schedule_startdate']);
          $end_time=strtotime($items['rows'][$row]['options']['schedule_enddate']);
          if($start_time>time() || time()>$end_time){
             continue;
          }
        }
          
				if(!is_null($row)) {
					$current = $items['rows'][$row];
					$html = $rows[$current['type']]['html'];
					$html = str_replace('%1$s',$row,$html);
          $html = str_replace('class="pbuilder_row"', 'class="pbuilder_row pbuilder_row_id_'.$row.'"',$html);
  
					$rowCtrl = $this->extract_row_controls($items['rows'][$row]);
					
          if($rowCtrl['row']['back_full']!='false'){
            $html = str_replace('%2$s',$rowCtrl['row']['back'],$html);
          } else {
            $html = str_replace('%2$s','',$html);
          }
					
          $columnInterface = '';
          if($rowCtrl['row']['back_full']=='false'){
            $html = str_replace('class="pbuilder_row_colwrapper">',' class="pbuilder_row_colwrapper">'.$rowCtrl['row']['back'],$html);     
          }
               
          $html = str_replace('class="pbuilder_row_colwrapper"',' class="pbuilder_row_colwrapper" style="'.$rowCtrl['row']['margin_padding'].'" ',$html);
          
          
					$rowCSS = (isset($rowCtrl['row']['id']) && $rowCtrl['row']['id'] != '' ? 'id="'.$rowCtrl['row']['id'].'" ' : '').(isset($rowCtrl['row']['style']) ? 'style="'.$rowCtrl['row']['style'].'" ' : '').(isset($rowCtrl['row']['class']) ? 'class="'.$rowCtrl['row']['class'].'' : 'class="');


					$html = preg_replace('/class=\"/', $rowCSS, $html, 1);
          
          

					foreach($current['columns'] as $colId => $shortcodes) {
            
						if(!isset($items['columns'][$row]) || !isset($items['columns'][$row][$colId])){
							$columnCtrl=false;
						} else {
							$columnCtrl = $this->extract_column_controls($items['columns'][$row][$colId]);
						}
						$colCSS = (isset($columnCtrl['column']['id']) && $columnCtrl['column']['id'] != '' ? 'id="'.$columnCtrl['column']['id'].'" ' : '').(isset($columnCtrl['column']['style']) ? 'style="'.$columnCtrl['column']['style'].'" ' : '').(isset($columnCtrl['column']['class']) ? 'class="'.$columnCtrl['column']['class'].'' : 'class="');
            
            
						$columnInterface = ($columnCtrl?$columnCtrl['column']['back']:'').'<div '.$colCSS.' pbuilder_column_inner pbuilder_droppable" >';

						//$columnInterface .= serialize($columnCtrl);
							foreach($shortcodes as $sh) {
							if(!is_null($items['items'][$sh])) {
								$columnInterface .= '<div class="pbuilder_module" data-shortcode="'.$items['items'][$sh]['slug'].'" data-modid="'.$sh.'">';
                
								$columnInterface .= $this->get_shortcode($items['items'][$sh]);
								$columnInterface .= '</div>';
							}
						}
						$columnInterface .= '</div>';
						$html = str_replace('%'.($colId+3).'$s',$columnInterface,$html);
					}


          $html .='<style>';
          if(!current_user_can('edit_pages') && $items['rows'][$row]['options']['desktop_show'] == 'false'){
            $html .='@media only screen and (min-width : 992px) {.pbuilder_row_id_'.$row.'{display:none;}}';
          }
          if(!current_user_can('edit_pages') && $items['rows'][$row]['options']['tablet_show'] == 'false'){
            $html .='@media only screen and (min-width : 768px) and (max-width : 992px) {.pbuilder_row_id_'.$row.'{display:none;}}';
          }
          if(!current_user_can('edit_pages') && $items['rows'][$row]['options']['mobile_show'] == 'false'){
            $html .='@media only screen and (max-width : 768px) {.pbuilder_row_id_'.$row.'{display:none;}}';
          }
          $html .='</style>';
  

					$output .= $html;
				}
			}
		}


		$output .= '
				</div>
				<div style="clear:both"></div>
			</div>
			<div style="clear:both"></div>
		</div>
		';
?>
