<?php
global $wpdb;

echo '
<script type="text/javascript">
	    var pbuilder_url = "' . $this->url . '";
</script>';

echo '<div id="pbuilder_admin_menu" class="wrap pbuilder_controls_wrapper" style="margin:0px;">';
echo '<h1 style="margin-left: -20px;" class="pbuilder_admin_menu_header">';
echo '<img src="' . IMSCPB_URL . '/images/logob.png">';
echo '<ul style="margin-top:10px;">
<li><a href="https://imsuccesscenter.com/customercare/"><i class="dashicons dashicons-sos"></i></a></li>
<li><a href="http://wpprofitbuilder.com/"><i class="dashicons dashicons-admin-site"></i></a></li>
</ul>';
echo '</h1>';


if(!$funnels=get_option('profit_builder_funnels')){
  $funnels=array();
}



if(isset($_GET['delete'])){
  $funnel_id = $_GET['delete'];
  echo '<div class="notice notice-error"><p>Funnel <strong>'.$funnels[$funnel_id]['name'].'</strong> deleted.</p></div>';
  unset($funnels[$funnel_id]);
  $wpdb->query('DELETE FROM '.$wpdb->postmeta.' WHERE meta_key="profit_builder_funnel" AND meta_value="'.$funnel_id.'"');
  update_option('profit_builder_funnels',$funnels);
}

if(isset($_POST['new_funnel_name'])){
  $funnels[time().rand()]=array('name'=>$_POST['new_funnel_name'],'pages'=>array());
  echo '<div class="notice notice-success"><p>Funnel <strong>'.$_POST['new_funnel_name'].'</strong> created.</p></div>';
  update_option('profit_builder_funnels',$funnels);
}

if(isset($_GET['resetstats'])){
  $funnel_id = $_GET['resetstats'];
  foreach($funnels[$funnel_id]['pages'] as $page_id => $page_data){
    $check_if_page_is_ab = get_post_meta($page_id,'so_split_test_settings',true);
    foreach($check_if_page_is_ab['pages'] as $page){
      $wpdb->query('DELETE FROM '.$wpdb->prefix.'profit_builder_funnel_stats WHERE post_id="'.$page.'"');
    }
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'profit_builder_funnel_stats WHERE post_id="'.$page_id.'"');
  }

  update_option('profit_builder_funnels',$funnels);
  echo '<div class="notice notice-success"><p>Stats for funnel <strong>'.$funnels[$funnel_id]['name'].'</strong> have been reset.</p></div>';
}

if(isset($_GET['edit'])){
		$funnel_id = $_GET['edit'];

    echo '<div class="postbox">';
    echo '<h2 style="padding:15px;border-bottom:1px solid #e1e1e1;margin:0px;"><span>Pages in '.$funnels[$funnel_id]['name'].' funnel</span></h2>';
    echo '<div class="inside">';

    if(isset($_POST['funnel_page_order'])){
       $page_order = explode(',',$_POST['funnel_page_order']);
       $pages_new=array();
       foreach($page_order as $page_id){
          $pages_new[$page_id]=$funnels[$funnel_id]['pages'][$page_id];
          update_post_meta($page_id,'profit_builder_funnel',$funnel_id);
       }
       $funnels[$funnel_id]['pages'] = $pages_new;
       $funnels[$funnel_id]['name'] = $_POST['funnel_name'];
       update_option('profit_builder_funnels', $funnels);
       echo '<div class="notice notice-success"><p>Funnel <strong>'.$funnels[$funnel_id]['name'].'</strong> Updated.</p></div>';
    }

    if(isset($_GET['remove_page'])){
       echo '<div class="notice notice-success"><p>Page <strong>'.get_the_title((int)$_GET['remove_page']).' </strong> removed from funnel.</p></div>';
       unset($funnels[$funnel_id]['pages'][(int)$_GET['remove_page']]);
       delete_post_meta((int)$_GET['remove_page'],'profit_builder_funnel');
       update_option('profit_builder_funnels', $funnels);
    }


		if(array_key_exists($funnel_id,$funnels)){

      echo '<form method="post" action="'.admin_url('admin.php?page=profitbuilder_funnels&edit='.$funnel_id).'">';
			echo '<input type="text" name="funnel_name" value="'.$funnels[$funnel_id]['name'].'" /><br />';

      if(count($funnels[$funnel_id]['pages'])>0){
				echo '<p>You can drag each page title to rearange the order of the pages in the funnel.</p>';

				echo '<ul id="funnel_pages">';
				foreach($funnels[$funnel_id]['pages'] as $funnel_page=>$funnel_page_data){
					$check_if_page_is_ab = get_post_meta($funnel_page,'so_split_test_settings',true);

          echo '<li id="'.$funnel_page.'">
          <span class="pbuilder_funnel_page_drag"><span class="dashicons dashicons-sort"></span></span>
          <span><a href="'.get_permalink($funnel_page).'">'.$this->truncit(get_the_title($funnel_page),40).($check_if_page_is_ab?'(A/B Test)':'').'</a></span>
          <a href="'.admin_url('admin.php?page=profitbuilder_funnels&edit='.$funnel_id.'&remove_page='.$funnel_page).'" class="button" style="float:right;">Remove page</a>
          </li>';
				}
				echo '</ul>';
			} else {
				echo 'There are no pages in this funnel.';
			}

      echo '<br />';
      echo '<input type="hidden" name="funnel_page_order" id="funnel_page_order" value="'.implode(',',array_keys($funnels[$funnel_id]['pages'])).'" />';
      echo '<input type="submit" class="button button-primary" value="Update Funnel" /> <a href="'.admin_url('admin.php?page=profitbuilder_funnels').'" class="button">Close</a>';

      echo '</form>';
		} else {
			echo "Funnel not found";
		}

    echo '<script>

      jQuery(document).ready(function () {
        jQuery("#funnel_pages").sortable({
          stop: function( event, ui ) {
            jQuery("#funnel_page_order").val(jQuery("#funnel_pages").sortable("toArray"));
          },
          handle: ".pbuilder_funnel_page_drag"
        });

        jQuery("#funnel_pages").disableSelection();

      });

    </script>';

    echo '</div></div>';
}


echo '<br /><h3>Create new funnel:</h3><form method="post" action="'.admin_url('admin.php?page=profitbuilder_funnels').'">Funnel name: <input type="text" name="new_funnel_name" id="new_funnel_name" value="" /> <input type="submit" class="button button-primary" value="Create Funnel" /></form>';



echo '<br /><table class="wp-list-table widefat fixed striped posts  ">';
echo '<thead><tr>
  <td id="cb" class="manage-column column-cb check-column">'.(count($funnels)>0?'<label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox">':'').'</th>
  <td width="200px;">Funnel Name</td>
  <td></td>
  </tr>
  </thead>';
  if(count($funnels)>0){
    foreach($funnels as $funnel_id=>$funnel){
      echo '<tr id="funnel_'.$funnel_id.'">';
      echo '<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-'.$funnel_id.'">Select '.$funnel['name'].'</label><input id="cb-select-'.$funnel_id.'" type="checkbox" name="funnels[]" value="'.$funnel_id.'"></th>';
      echo '<td>'.$funnel['name'];
      echo '<div class="row-actions">
      <span class="edit"><a href="'.admin_url('admin.php?page=profitbuilder_funnels&edit='.$funnel_id).'" aria-label="Edit '.$funnel['name'].'">Edit</a> | </span>
      <span class="trash"><a href="'.admin_url('admin.php?page=profitbuilder_funnels&delete='.$funnel_id).'" class="submitdelete" aria-label="Delete '.$funnel['name'].'">Delete Funnel</a></span>
      </div>';
      echo '</td>';
      echo '<td>';
      $pages_total=count($funnels[$funnel_id]['pages']);
      $step=1;
      foreach($funnels[$funnel_id]['pages'] as $funnel_page=>$funnel_page_data){
        $check_if_page_is_ab = get_post_meta($funnel_page,'so_split_test_settings',true);
        $check_post_type=get_post_type($funnel_page);

        $total_page_views=0;
        $total_page_unique_views=0;
        $total_page_conversions=0;

		if ($step==1){$icocol="#008ee8";}else{$icocol="#61a039";}

        echo '<div class="pbfunnel_step '.($step%2==0?'pbfunnel_step_alt':'').' '.($check_if_page_is_ab?'pbfunnel_step_ab':'').' '.($check_post_type=='product'?'pbfunnel_step_product':'').'">';
          if($check_if_page_is_ab){
            // If page is A/B
            foreach($check_if_page_is_ab['pages'] as $page){
              $page_stats = $this->get_funnel_stats($page);
              $daily_conversions = $page_stats['days'];
              $total_page_views=$page_stats['totals']['views'];
              $total_page_unique_views=$page_stats['totals']['views_unique'];
              $total_page_conversions=$page_stats['totals']['conversions'];

              echo '<div class="pbfunnel_page_stats">
                    <div class="pbfunnel_page_stats_buttons">
                      <a href="'.get_edit_post_link($funnel_page).'" class="pbfunnel_page_stats_buttons_edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                      <a href="'.get_permalink($funnel_page).'" class="pbfunnel_page_stats_buttons_view"><i class="fa fa-eye" aria-hidden="true"></i></a>
                    </div>
                    <div class="pbfunnel_page_stats_title">'.$this->truncit(get_the_title($page),40).'</div>
					<div class="pbfunnel_page_stats_content">
						<div class="pbuilder_tooltip pbso_total_views pbfunnel_page_stats_views"><i class="fa fa-refresh fa-users" style="color:'.$icocol.';"></i> '.$this->numberAbbreviation($total_page_views).'</div>
						<div class="pbuilder_tooltip pbso_unique_views pbfunnel_page_stats_unique_views"><i class="fa fa-user" style="color:'.$icocol.';"></i> '.$this->numberAbbreviation($total_page_unique_views).'</div>';

						if ($step==1 && $total_page_conversions > 0 ){
							echo '<div class="pbuilder_tooltip pbso_conversions pbfunnel_page_stats_unique_views"><i class="fa fa-envelope" style="color:'.$icocol.';"></i> '.$this->numberAbbreviation($total_page_conversions).'</div>';
						}

						//<div class="pbuilder_tooltip pbso_conversion_percent pbfunnel_page_stats_unique_views"><i class="fa fa-check-square" aria-hidden="true"></i> '.round($total_page_conversions/$total_page_unique_views*100,1).'%</div>';
						if($total_page_conversions){
						  echo '<br /><img style="margin: 0 auto;" title="Conversions '.key( array_slice( $daily_conversions, 0, 1, TRUE ) ).' to '.key( array_slice( $daily_conversions, -1, 1, TRUE ) ).'" src="http://chart.apis.google.com/chart?chs=175x30&cht=ls&chf=bg,s,FFFFFF00&chco=0077CC&chm=B,e5f2fa,0,0,0&chd=t:' . implode(',',$daily_conversions) . '&chds=' . min($daily_conversions).','.max($daily_conversions) . '" width="175" height="30">';
						}

						if($step<$pages_total) echo '<div class="pbfunnel_page_conversions">'.($total_page_unique_views>0?round($total_page_conversions/$total_page_unique_views*100,1):0).'%</div>';
					echo '</div>';
              echo '</div>';
            }
          } else {
            // If page is not A/B

            $page_stats = $this->get_funnel_stats($funnel_page);
            $daily_conversions = $page_stats['days'];
            $total_page_views=$page_stats['totals']['views'];
            $total_page_unique_views=$page_stats['totals']['views_unique'];
            $total_page_conversions=$page_stats['totals']['conversions'];

            echo '<div class="pbfunnel_page_stats">';
            echo '<div class="pbfunnel_page_stats_buttons">
              <a href="'.get_edit_post_link($funnel_page).'" class="pbfunnel_page_stats_buttons_edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
              <a href="'.get_permalink($funnel_page).'" class="pbfunnel_page_stats_buttons_view"><i class="fa fa-eye" aria-hidden="true"></i></a>
            </div>
                  <div class="pbfunnel_page_stats_title">'.$this->truncit(get_the_title($funnel_page),40).'</div>
				  <div class="pbfunnel_page_stats_content">
					  <div class="pbuilder_tooltip pbso_total_views pbfunnel_page_stats_views"><i class="fa fa-users" style="color:'.$icocol.';"></i> '.$this->numberAbbreviation($total_page_views).'</div>
					  <div class="pbuilder_tooltip pbso_unique_views pbfunnel_page_stats_unique_views"><i class="fa fa-user" style="color:'.$icocol.';"></i> '.$this->numberAbbreviation($total_page_unique_views).'</div>';

					 if ($step==1 && $total_page_conversions > 0 ){
						echo '<div class="pbuilder_tooltip pbso_conversions pbfunnel_page_stats_unique_views"><i class="fa fa-envelope" style="color:'.$icocol.';"></i> '.$this->numberAbbreviation($total_page_conversions).'</div>';
					}

					  //<div class="pbuilder_tooltip pbso_conversion_percent pbfunnel_page_stats_unique_views"><i class="fa fa-check-square" aria-hidden="true"></i> '.round($total_page_conversions/$total_page_unique_views*100,1).'%</div>';
					  if($total_page_conversions){
						echo '<br /><img style="margin: 0 auto;" title="Conversions '.key( array_slice( $daily_conversions, 0, 1, TRUE ) ).' to '.key( array_slice( $daily_conversions, -1, 1, TRUE ) ).'" src="http://chart.apis.google.com/chart?chs=175x30&cht=ls&chf=bg,s,FFFFFF00&chco=0077CC&chm=B,e5f2fa,0,0,0&chd=t:' . implode(',',$daily_conversions) . '&chds=' . min($daily_conversions).','.max($daily_conversions) . '" width="175" height="30">';
					  }
            	  if($step<$pages_total) echo '<div class="pbfunnel_page_conversions">'.($total_page_unique_views>0?round($total_page_conversions/$total_page_unique_views*100,1):0).'%</div>';
				  echo '</div>';
            echo '</div>';
          }
        echo '</div>';

        echo '<style>
         .pbso_total_views:hover:after{
           content:"Total Views";
         }
         .pbso_unique_views:hover:after{
           content:"Unique Views";
         }
         .pbso_conversions:hover:after{
           content:"Total Conversions";
         }
         .pbso_conversion_percent:hover:after{
           content:"Conversion Percent";
         }
        </style>';


        $step++;
      }
      echo '<a href="'.admin_url('admin.php?page=profitbuilder_funnels&resetstats='.$funnel_id).'" style="float: right;" class="button">Reset Stats</a>';
      echo '</td>';
      echo '</tr>';
    }
  } else {
    echo '<tr><td colspan="3" class="pbuilder_funnels_empty">';
    echo '<ul class="pbuilder_buttons_blue_big">
      <li><img src="'.IMSCPB_URL.'/images/buttons/blue_create_funnel.png" /></li>
      <li><a href="'.admin_url('post-new.php?post_type=page').'"><img src="'.IMSCPB_URL.'/images/buttons/blue_build_new_page.png" /></a></li>
    </ul>';
    echo '</td></tr>';
  }
echo '</table>';

 echo '</div>';

?>
