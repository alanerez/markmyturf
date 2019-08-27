<?php
/**
 * Plugin Name: WP Toolkit
 * Plugin URI: https://wptoolkit.com/
 * Description: Premium Theme, Plugin & WooCommerce Extension Manager
 * Version: 1.2.14
 * Author: WP Toolkit
 * Author URI:  https://wptoolkit.com/ 
 * Copyright: WP Toolkit is based on GPLKit (https://gplkit.com). WP Toolkit is copyright 2016. 
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//** Make sure this plugin runs first
function wpt_this_plugin_first() {
	$this_plugin = plugin_basename( __FILE__ );
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
}
add_action("activated_plugin", "wpt_this_plugin_first");
add_action('upgrader_process_complete', 'wpt_this_plugin_first');

add_action('core_upgrade_preamble', array("WPToolKit_Updates","get_plugin_catalogue"));
add_action('core_upgrade_preamble', array("WPToolKit_Updates","get_theme_catalogue"));

$wptoolkit_plugin_manager_nag_data = get_option( "wptoolkit_plugin_manager_nag_data" );

//** Turns off WPMUDEV Dashboard Nags */
if ( ! class_exists('WPMUDEV_Dashboard_Notice3') && ! class_exists('WPMUDEV_Dashboard_Notice') ) {
	$wpmu_nag = $wptoolkit_plugin_manager_nag_data["wpt_nag_override_wpmudev"];

	if($wpmu_nag !== false && $wpmu_nag == "on"){
		class WPMUDEV_Dashboard_Notice3 {}
		class WPMUDEV_Dashboard_Notice {}
	}
}

//** Turn Off Elegant Themes updates class.
if ( ! class_exists( 'ET_Core_Updates' ) ) {
	$et_nag = $wptoolkit_plugin_manager_nag_data["wpt_nag_override_elegantthemes"];
	if($et_nag !== false && $et_nag == "on"){
		class ET_Core_Updates {}
	}
}

//** Turn Off Woo Updater Nags
if ( ! function_exists( 'woothemes_updater_notice' ) ) {
	$woothemes_nag = $wptoolkit_plugin_manager_nag_data["wpt_nag_override_woothemes"];
	if($woothemes_nag !== false && $woothemes_nag == "on"){
		function woothemes_updater_notice() {}
	}
}

if ( ! class_exists( 'WPToolKit' ) ) {

	/**
	 * Main WPToolKit Class
	 *
	 * @class WPToolKit
	 * @version	2.3.0
	 */
	final class WPToolKit {
		
		protected static $_instance = null;

		public $program = null;
		
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		public function __construct() {
			
			$this->includes();
			$this->init_hooks();
			
			do_action( 'wpt_loaded' );
		}

		public function init_hooks() {
			add_action( 'init', array( $this, 'init' ), 0 );
		}
		
		public function includes() {
			include_once( 'includes/class-wptapi-admin.php' );
			include_once( 'includes/class-wptapi-updates.php' );
			include_once( 'includes/class-wptapi-plugin.php' );
			include_once( 'includes/class-wptapi-license.php' );
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		public function init() {
			
			add_action( 'admin_enqueue_scripts', array($this,'wpt_enqueue_scripts') );
			
			/* Overrides GravityForms Nag */
			if( class_exists('GFCommon') ){
				$wptoolkit_plugin_manager_nag_data = get_option( "wptoolkit_plugin_manager_nag_data" );
				$GF_nag = $wptoolkit_plugin_manager_nag_data["wpt_nag_override_gravityforms"];
				if($GF_nag !== false && $GF_nag == "on"){
					delete_option( 'rg_gforms_message' );
				}
			}
			
			/* This block of code is just temporary and must be deleted on next version 1.2.10 or later*/
			$nag_first_GF = get_option( "wptoolkit_nagGF" );
			if($nag_first_GF != "nope"){
				$nag_options = get_option( "wptoolkit_plugin_manager_nag_data" );
				$nag_options["wpt_nag_override_gravityforms"] = "on";
				update_option( "wptoolkit_plugin_manager_nag_data", $nag_options );
				update_option( "wptoolkit_nagGF", "nope" );
			}
			/* ***** */
		}

		public function install() {
			wp_schedule_event(time(), 'hourly', 'wptoolkit_hourly_update');
			WPT()->activation();
		}
		public function uninstall() {
			wp_clear_scheduled_hook('wptoolkit_hourly_update');
			WPT()->uninstall();
		}

		public function wpt_enqueue_scripts($hook) {
			wp_enqueue_style( 'wptoolkit-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin-styles.css' );
			wp_enqueue_script( 'wptoolkit-admin-js', plugins_url('assets/js/jquery.mixitup.min.js',__FILE__) );
		}

		public function get_wptoolkit_installed_plugins() {
			return array(
				
			);
		}
		
	}

}
register_activation_hook( __FILE__, array( 'WPToolKit', 'install' ) );
register_deactivation_hook(__FILE__, array( 'WPToolKit', 'uninstall' ) );

function GK() {
	return WPToolKit::instance();
}

// Global for backwards compatibility.
$GLOBALS['wptoolkit'] = GK();

function WPT_remote_download($url, $save_path = false){
	// Use wp_remote_get to fetch the data
	$response = wp_remote_get($url, array("timeout" => PHP_INT_MAX));

	// Save the body part to a variable
	$zip = $response['body'];

	
	// In the header info is the name of the XML or CVS file. I used preg_match to find it
	preg_match("/filename\s*=\s*(\\\"[^\\\"]*\\\"|'[^']*)/i", $response['headers']['content-disposition'], $match);

	if($save_path){
		// Create the name of the file and the declare the directory and path
		$file = trailingslashit($save_path).$match[1];

		// Now use the standard PHP file functions
		$fp = fopen($file, "w");
		fwrite($fp, $zip);
		fclose($fp);
		return true;
	}else{
		if($zip){
			return array("filenam" => $match[1], "body" => $zip);
		}else{ 
			return false;
		}
	}
}

//** Force Wordpress from downloading WPToolkit plugins from our repo
function WPT_updater( $api, $action, $args ) {
	if( $action == 'plugin_information' && empty( $api ) && isset($_GET["type"]) && $_GET["type"] == "WPT" ){

		// fallback for wptoolkit_plugins being deleted
		if ( !get_option('wptoolkit_plugins') ) WPToolKit_Updates::get_plugin_catalogue(false);
		
		$wptoolkit_plugins = get_option('wptoolkit_plugins');
		$the_plugin = $wptoolkit_plugins[$_GET["plugin"]];
		
		$wptoolkit_licence_manager = get_option('wptoolkit_plugin_manager');
		$email = $wptoolkit_licence_manager['activation_email'];
		$licence_key = $wptoolkit_licence_manager['api_key'];
		
		$slug = $the_plugin["plugin_id"];
		
		$res                = new stdClass();
		$res->name          = $the_plugin['name'];
		$res->version       = $the_plugin['version'];
		$res->download_link = 'http://api.wptoolkit.com/?wpt_plugin_download=get&plugin_id='.$slug.'&email='.$email.'&licence_key='.$licence_key."&request=install&site_url=".home_url();
		$res->tested = $the_plugin['version'];
		return $res;
	}
	return $api;
}
add_filter( 'plugins_api', "WPT_updater", 100, 3);

//** Force Wordpress from downloading WPToolkit plugins from our repo
function WPT_theme_updater( $api, $action, $args ) {
	if( $action == 'theme_information' && empty( $api ) && isset($_GET["type"]) && $_GET["type"] == "WPT" ){

		// fallback for wptoolkit_themes being deleted
		if ( !get_option('wptoolkit_themes') ) WPToolKit_Updates::get_theme_catalogue(false);
		
		$wptoolkit_plugins = get_option('wptoolkit_themes');
		$the_theme = $wptoolkit_plugins[$_GET["theme"]];

		$wptoolkit_licence_manager = get_option('wptoolkit_plugin_manager');
		$email = $wptoolkit_licence_manager['activation_email'];
		$licence_key = $wptoolkit_licence_manager['api_key'];
		
		$slug = $the_theme["theme_id"];
		
		$res                = new stdClass();
		$res->name          = $the_theme['name'];
		$res->version       = $the_theme['version'];
		$res->download_link = 'http://api.wptoolkit.com/?wpt_theme_download=get&theme_id='.$slug.'&email='.$email.'&licence_key='.$licence_key."&request=install&site_url=".home_url();
		$res->tested = '10.0';
		return $res;
	}
	return $api;
}
add_filter( 'themes_api', "WPT_theme_updater", 100, 3);

//** Allow Plugin Re-install **/
function WPT_force_reinstall($options){
	
	$options['abort_if_destination_exists'] = false;
	return( $options );
}
add_filter( "upgrader_package_options",'WPT_force_reinstall');

//** Force WPToolkit to update its lists of plugins and themes
function WPT_force_update_lists(){
	WPToolKit_Updates::get_plugin_catalogue();
	die();
}
add_action( 'wp_ajax_get_plugin_catalogue', "WPT_force_update_lists" );
add_action( 'wp_ajax_nopriv_get_plugin_catalogue', "WPT_force_update_lists");

// Adds support for PHP < 5.5
if (!function_exists('array_column')) {
    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();
        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }
        if (!is_array($params[0])) {
            trigger_error(
                'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
                E_USER_WARNING
            );
            return null;
        }
        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;
            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }
            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }
            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }
}


?>
