<?php



if(!class_exists('pbuilder_notifications')){
	class pbuilder_notifications {
	  static $version = 1.0;
	  static $slug = 'pbuilder_notifications';
	  static $notifications_url = 'http://license1.imsccheck.com/';
	  static $notifications = array();

	  static function init() {
		add_action( 'admin_notices', array(self::$slug, 'show_notifications') );
		add_action( 'admin_footer', array(self::$slug, 'admin_js') );
		if(!has_action('wp_ajax_imsc_notice_dismiss')){
			add_action( 'wp_ajax_imsc_notice_dismiss', array(self::$slug, 'dismiss_notice') );
		}
		if(isset($_REQUEST['refresh_imsc_notifications'])){
			delete_option('imsc_notifications');
		}

		if($notifications = get_option('imsc_notifications')){
			if($notifications['last_updated']+60*60*6<time() ){
				$new_notifications=self::get_notifications();
				if(count($new_notifications)>0){
					$notifications['last_updated']=time();
					$notifications['list']=$new_notifications;
				}
				foreach($notifications['list'] as $plugin=>$plugin_notifications){
				  foreach($plugin_notifications as $notification=>$notification_data){
					if( !array_key_exists($notification,$notifications['statuses']) ){
						$notifications['statuses'][$notification]='none';
					}
				  }
				}
				update_option('imsc_notifications', $notifications);
			}
		} else {
			//Fetch New List
			$notifications['last_updated']=time();
			$notifications['statuses']=array();
			$new_notifications=self::get_notifications();

			if(count($new_notifications)>0){
				$notifications['last_updated']=time();
				$notifications['list']=$new_notifications;
			}
			foreach($notifications['list'] as $plugin=>$plugin_notifications){
			  foreach($plugin_notifications as $notification=>$notification_data){
				if( !array_key_exists($notification,$notifications['statuses']) ){
					$notifications['statuses'][$notification]='none';
				}
			  }
			}
			update_option('imsc_notifications', $notifications);
		}

		self::$notifications=$notifications;
	  }

	  static function dismiss_notice(){
		if(array_key_exists($_POST['noticeid'],self::$notifications['statuses'])){
			self::$notifications['statuses'][$_POST['noticeid']]='dismissed';
		}
		update_option('imsc_notifications', self::$notifications);
		die();
	  }

	  static function get_notifications(){
		  $res=wp_remote_get( self::$notifications_url.'notifications_proxy.php?notifications='.self::$slug.'&nocache='.time(), array( 'timeout' => 30 ) );
		  if (wp_remote_retrieve_response_code($res) == 200) {
			  $res = wp_remote_retrieve_body($res);
			  $notifications = json_decode($res, true);
		 }
		 return $notifications;
	  }

	  static function show_notifications(){
		global $imsc_displayed_notifications;
		if($imsc_displayed_notifications){
			return;
		}
		if(count(self::$notifications['list'])>0){

		  foreach(self::$notifications['list'] as $plugin=>$plugin_notifications){
			if($plugin!='general' && $plugin!=self::$slug){
				continue;
			}
			foreach($plugin_notifications as $notification=>$notification_data){
			  $time=time();
			  if($notification_data['timestamp_start']<$time && $time<$notification_data['timestamp_end'] && self::$notifications['statuses'][$notification]!='dismissed'){
				echo '<div class="notice imsc_notice '.$notification_data['type'].' is-dismissible" id="imsc_notice_'.$notification.'">';
				  echo '<p>'.$notification_data['message'].'</p>';
				echo '</div>';
				$imsc_displayed_notifications=true;
				return;
			  }
			}
		  }
		}
	  }

	  static function admin_js(){
	    global $imsc_displayed_notifications_js;
		if(!$imsc_displayed_notifications_js){
	    ?>
		  <script>
          jQuery('.imsc_notice').on('click','.notice-dismiss',function(){
              noticeid=jQuery(this).parent().attr('id').replace('imsc_notice_','');
			  imsc_notice_dismiss(noticeid);
          });


		  function imsc_notice_dismiss(noticeid) {
				console.log('Dismiss '+noticeid);
				jQuery.ajax({ type: 'POST',
				  url: ajaxurl,
				  data: {action: 'imsc_notice_dismiss',noticeid:noticeid},
				  dataType: 'json'})
				  .always(function(response) {

					console.log(response);

				  });
			  }
          </script>
         <?php
		}
		$imsc_displayed_notifications_js=true;
	  }
	}

	add_action('init', array('pbuilder_notifications', 'init'));
}
