<?php
function imscpb_remove_filters() {
    if (is_admin())
        remove_all_filters('switch_theme');
}


add_action("init", "imscpb_remove_filters");
//-----------------------------------------------------------
// Make sure plugin paths and definitions are in place
//-----------------------------------------------------------
if (!defined('WP_CONTENT_DIR'))
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_CONTENT_URL'))
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_PLUGIN_DIR'))
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
if (!defined('WP_PLUGIN_URL'))
    define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
$wp_dir = __FILE__;
$wp_dir = str_replace("\\", "/", $wp_dir);
$wp_dir = explode("/", $wp_dir);
$index = count($wp_dir) - 2;
$pluginfolder = $wp_dir[$index];
$url = WP_PLUGIN_URL;
if (substr_count(admin_url(), "https://") > 0 && substr_count($url, "https://") <= 0)
    $url = str_replace("http://", "https://", $url);
define('IMSCPB_SLUG', $pluginfolder);
define('IMSCPB_DIR', WP_PLUGIN_DIR . "/" . $pluginfolder);
define('IMSCPB_URL', $url . "/" . $pluginfolder);
require_once 'functions/plugin-update-checker.php';
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$imscpb_uc = PucFactory::buildUpdateChecker(
                'http://wpprofitbuilder.com/download/profit_builder/profit_builder.json', IMSCPB_FILE, 'profit-builder', 12
);
//print_r($imscpb_uc);
//$imscpb_uc->checkForUpdates();
//-----------------------------------------------------------
// LICENSE CHECK
//-----------------------------------------------------------
/* * **********use sql query for update awesome font icon name in database ************ */

if (!get_option('pb_update_font_check', false)) {
  global $wpdb;
  $wpdb->query("UPDATE " . $wpdb->prefix . "posts SET `post_content` = replace(post_content, 'ba ba-', 'fa fa-')");
  update_option('pb_update_font_check', true);
}

if (!class_exists("imscpblicense")) {

    class imscpblicense {

        var $name;
        var $pref;
        var $menutitle;
        var $serverurl;
        var $authurl;
        var $memurl;
        var $state;
        var $email;
        var $authkey;
        var $levelnum;
        var $pluginfile;
        var $updateurl;
        var $pluginadmin;
        var $parentmenu;
        var $minlevel;
        var $icmember;
        var $icserverurl;
        var $icauthlocation;
        var $icmemberurl;
		    var $fontsjson;
		    var $fontsjsondecoded;

        function __construct($Info = array("pluginname" => "", "pluginprefix" => "", "pluginfile" => "", "pluginadmin" => "", "updateurl" => "", "pluginname" => "", "menutitle" => "", "serverurl" => "", "authlocation" => "", "memberurl" => "", "parentmenu" => "")) {
            $wp_dir = __FILE__;
            $wp_dir = str_replace("\\", "/", $wp_dir);
            $wp_dir = explode("/", $wp_dir);
            for ($index = 0; $index < 4; $index++)
                unset($wp_dir[count($wp_dir) - 1]);
            $wp_dir = implode("/", $wp_dir);
            include_once($wp_dir . "/wp-load.php");
            $this->name = $Info["pluginname"];
            $this->pref = $Info["pluginprefix"];
            $this->menutitle = $Info["menutitle"];
            $this->serverurl = $Info["serverurl"];
            $this->authurl = $Info["authlocation"];
            $this->memurl = $Info["memberurl"];
            $this->pluginfile = $Info['pluginfile'];
            $this->updateurl = isset($Info['updateurl']) ? $Info['updateurl'] : "";
            $this->pluginadmin = $Info['pluginadmin'];
            $this->parentmenu = isset($Info['parentmenu']) ? $Info['parentmenu'] : "";
            $this->pluginname = $Info["pluginname"];
            //add_filter( 'http_request_args', array(&$this, 'updates_exclude'), 5, 2 );
            register_activation_hook($this->pluginfile, array(&$this, 'check_activation'));
            add_action($this->pref . 'check_event', array(&$this, 'check_update'));
            register_deactivation_hook($this->pluginfile, array(&$this, 'check_deactivation'));
            $this->state = $this->pref . "activation_state";
            $this->email = $this->pref . "email";
            $this->authkey = $this->pref . "authkey";
            $this->levelnum = $this->pref . "levelnum";
            $this->minlevel = $Info["minlevel"];
            $this->icmember = $this->pref . "icmember";
            $this->icserverurl = "imsuccesscenter.com";
            $this->icauthlocation = "/inner_circle/wp-content/plugins/license-checker/authorize_domain.php";
            $this->icmemberurl = "http://imsuccesscenter.com/inner_circle/wp-login.php?action=lostpassword";
            if (get_option($this->state) && get_option($this->email) != "" && get_option($this->authkey) !== "" && get_option($this->levelnum) > 0) {
                $this->update_encryptedoption($this->state, get_option($this->state));
                $this->update_encryptedoption($this->email, get_option($this->email));
                $this->update_encryptedoption($this->authkey, get_option($this->authkey));
                $this->update_encryptedoption($this->levelnum, get_option($this->levelnum));
            }
            /* $this->update_encryptedoption($this->state , 'true');
              $this->update_encryptedoption($this->email , 'a@a.com');
              $this->update_encryptedoption($this->authkey , '12345678');
              $this->update_encryptedoption($this->levelnum , '2'); */
            add_action('init', array(&$this, 'init'));
            add_action('plugins_loaded', array(&$this, 'init'));
            add_filter('cron_schedules', array(&$this, 'add_12hours_cron'));
            register_activation_hook(__FILE__, array(&$this, 'CheckCron'));
            add_action($this->pref . '12hours_event', array(&$this, 'VerifyLicense'));
            add_action('admin_menu', array(&$this, 'admin_menu'), 7000);
        }

        function init() {
            $this->CheckCron();
            if (isset($_POST['act']) && $_POST['act'] == $this->pref . 'install_license') {
                if (!isset($_POST["email"]) || !isset($_POST["authkey"]) || $_POST["email"] == "" || $_POST["authkey"] == "") {
                    $msg = "Please enter email and authorization key";
                    echo 'failure:<div class="error"><p>' . $msg . '</p></div><br />';
                    exit;
                } else {
                    delete_transient('pb_templates');
                    if ($this->VerifyLicense(trim($_POST["email"]), trim($_POST["authkey"]), trim(@$_POST["icmember"]))) {
                        echo "success:Thanks! The " . $this->name . " has been activated. <a href='admin.php?page=" . $this->pluginadmin . "'>Click here to go to the admin panel...</a>\r\n";
                        exit;
                    } else {
                        echo 'failure:<div class="error"><p>' . __("Sorry, the API key was incorrect.", "") . '</p></div><br />';
                        exit;
                    }
                }
            }
        }

        function admin_menu() {
            global $imscpbsettings;
            if (!$this->CheckLicense()) {
                add_menu_page($this->menutitle, $this->menutitle, 'administrator', $this->pref . 'install_license', array(&$this, 'menupage'), $imscpbsettings['icon']);
            } elseif ($this->parentmenu != "" && $this->CheckLicense()) {
                //$this->licensepage($this->parentmenu);
            }
        }


        function ActivationMessage($ShowActive = false) {
            $msg = "";
            if ($this->get_decryptedoption($this->state) != 'true')
                $msg = "";
            else if ($ShowActive)
                $msg = '<p>' . $this->name . ' is Activated </p>';
            if ($msg != "")
                echo '<div id="message" class="updated fade">' . $msg . '</div>';
        }

        function menupage() {
            global $imscpbsettings;
            $buttonText = "Activate Now";
            $this->ActivationMessage(true);
            if ($this->get_decryptedoption($this->email) == "-1")
                $this->update_encryptedoption($this->email, "");
            if ($this->get_decryptedoption($this->authkey) == "-1")
                $this->update_encryptedoption($this->authkey, "");

			if ( is_plugin_active('sellosity-core/sellosity.php')){
			?>
            <div style="width:100%;height:<?php echo $imscpbsettings['headerheight'] ?>;background:black url('<?php echo $imscpbsettings['headerbg'] ?>') repeat-x;margin-right:20px;margin-bottom:20px;">
                <h2 style="margin:0px;padding:0px;margin-left:-19px;margin-top:-4px;margin-bottom:20px;"><img src="<?php echo $imscpbsettings['headerlogo'] ?>"></h2></div>
            	<div id="error"></div>
            	<div class="dvlicense">
                <h1>Please Activate Your Copy of Sellosity</h1>
                <p>Once you activate Sellosity, ProfitBuilder will activate as well and you'll be able to use it. <a href="admin.php?page=imscso_install_license">Click here</a> to activate your copy of Sellosity.</p>
            	</div>
            </div>
            <?php
			} else {

			?>
      <div class="wrap" style="margin:0px;">
      <h1 style="background: #FFFFFF; border-bottom: 4px solid #0074c7; margin-left: -22px; padding: 6px;">
      <img style="max-height: 50px;display:inline-block; margin: 10px 0 10px 20px;" src="<?php echo IMSCPB_URL; ?>/images/logob.png">
      <ul style="float: right;margin-top: 10px;">
      <li style="display: inline-block;width: 60px;"><a style="text-decoration: none;" href="https://imsuccesscenter.com/customercare/"><i style="font-size: 56px;" class="dashicons dashicons-sos"></i></a></li>
      <li style="display: inline-block;width: 60px;"><a style="text-decoration: none;" href="http://wpprofitbuilder.com/"><i style="font-size: 56px;" class="dashicons dashicons-admin-site"></i></a></li>
      </ul>
    </h1>
  </div>
            <div id="error"></div>
            <div class="dvlicense">
                <h1>Please Activate Your Copy of <?php echo $this->name ?></h1>
                <strong>Note - </strong> If you have spare licenses available we will automatically add this domain "<?php echo $_SERVER['SERVER_NAME'] ?>" to your license pool<br /><br />
                <form id="actform" method="post">
                    <table>
                        <tr>
                            <th>Email address:</th>
                            <td><input type="text" name="email" value="<?php echo $this->get_decryptedoption($this->email); ?>" /></td>
                        </tr>
                        <tr>
                            <th>Authorization key:</th>
                            <td>
                                <input type="text" name="authkey" value="<?php echo $this->get_decryptedoption($this->authkey); ?>" />
                                <p align=right>I'm an Inner Circle Member: <input type="checkbox" name="icmember" value="1" <?php
                                    if ($this->get_decryptedoption($this->email) == 1) {
                                        echo "checked";
                                    }
                                    ?>/></p>
                                <a class="forgot" href="<?php echo $this->memurl; ?>">Forgot Your Details? Click Here</a>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <input type="hidden" name="act" value="<?php echo $this->pref; ?>install_license" />
                                <input type="submit" id="btnsubmit" name="btnsubmit" value="<?php echo $buttonText; ?>" />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <style>
                .dvlicense{
                }
                .dvlicense table th{
                    width: 200px;
                    text-align: left;
                    font-weight: bold;
                    vertical-align: top;
                    font-size: 12px;
                }
                .dvlicense table td input[type="text"]{
                    width: 300px;
                }
                .dvlicense table td .forgot{
                    display: block;
                    text-align: right;
                    color: blue;
                    text-decoration: none;
                    font-weight: bold;
                    font-size: 12px;
                }
                .dvlicense table td #btnsubmit{
                    float: right;
                    font-size: 12px;
                }
            </style>
            <script>
                var j = jQuery;
                j(document).ready(function () {
                    j("#actform").submit(function () {
                        j("#btnsubmit").val("Activating please wait.....");
                        j("#btnsubmit").attr("disabled", 'true');
                        j.ajax({
                            type: "POST",
                            data: j(this).serialize(),
                            cache: false,
                            url: "<?php echo admin_url("admin.php?page=" . $this->pref . "install_license"); ?>",
                            success: function (data) {
                                if (data.indexOf("success:") >= 0) {
                                    data = data.replace("success:", "");
                                    j("#message").html('<p>' + data + '<br /></p>');
                                    j("#error").html("");
                                    //alert(data);
                                    window.location.href = "<?php echo $this->pluginadmin; ?>";
                                } else {
                                    //alert(data);
                                    j("#error").html(data.replace("failure:", ""));
                                    j("#message").html('<p><strong>Notice: - </strong> <?php echo $this->name ?> needs to be <span style="color: blue;">Activated</span> before it can be used<br /></p>');
                                }
                                j("#btnsubmit").val("Activate Plugin");
                                j("#btnsubmit").removeAttr("disabled");
                            }
                        });
                        return false;
                    });
                });
            </script>
            <?php
			}
        }

        function CheckLicense($ShowMessage = false) {
      			if ( is_plugin_active('sellosity-core/sellosity.php') && $this->checksellosity_active()) {
      			   return true;
      			}
      			if ($ShowMessage)
                $this->ActivationMessage();
            if ($this->get_decryptedoption($this->state) != 'true')
                return false;
            else
                return true;
        }

        function GetLevelNo() {
            $LevelNo = 0;
            if ( is_plugin_active('sellosity-core/sellosity.php') && $this->checksellosity_active()) {
      			   return 2;
      			}
            if ($this->CheckLicense())
                $LevelNo = (int) $this->get_decryptedoption($this->levelnum, 0);
            return $LevelNo;
        }

        function add_12hours_cron($schedules) {
            $schedules['12hours'] = array(
                'interval' => 43200,
                'display' => __('Once in 12 Hours')

            );
            return $schedules;
        }

        function CheckCron() {
            $hook = $this->pref . "12hours_event";
            if (!wp_get_schedule($hook)) {
                wp_schedule_event(current_time('timestamp'), "12hours", $hook);
            }
        }

        function VerifyLicense($email = "-1", $authkey = "-1", $icmember = "0") {
      			$endpoints = array("http://license1.imsccheck.com/authorize_domain.php","http://license2.imsccheck.com/authorize_domain.php","http://license3.imsccheck.com/authorize_domain.php");

      			$msg = "";
                  $activated = false;
                  if($this->get_decryptedoption($this->email, "") != "" && $email == "-1")
                      $email = $this->get_decryptedoption($this->email);
                  if($this->get_decryptedoption($this->authkey, "") != "" && $authkey == "-1")
                      $authkey = $this->get_decryptedoption($this->authkey);
                  if ($this->get_decryptedoption($this->icmember, "") != "" && $icmember == "0")
                      $icmember = $this->get_decryptedoption($this->icmember);

      			$remote_access_fail = false;
            $domain = $_SERVER['SERVER_NAME'];
            $selected_endpoint = $endpoints[array_rand($endpoints, 1)];
      			$lc_response = wp_remote_get($selected_endpoint."?plugin=".$this->pluginname."&email=".urlencode($email)."&domain=".$domain."&authkey=".$authkey."&icmember=".$icmember, array('sslverify' => false, 'timeout' => 30));

      			if (!is_wp_error($lc_response) && !empty($lc_response['response']['code']) && $lc_response['response']['code'] == 200) {
      				$returned_value = $lc_response['body'];


      				if( (int)$returned_value > 0 && $returned_value >= $this->minlevel ) {

      					$this->update_encryptedoption($this->state,'true');
                          if($icmember == "1") $returned_value=2;
                          $this->update_encryptedoption($this->email, $email);
                          $this->update_encryptedoption($this->authkey, $authkey);
                          $this->update_encryptedoption($this->levelnum, $returned_value."|".$this->minlevel);
                          $this->update_encryptedoption($this->icmember, $icmember);
                          $activated = true;
                      } else if($returned_value == '0'){
                          if($email == "-1") $email = "";
                          if($authkey == "-1") $authkey = "";
                          $this->update_encryptedoption($this->state,"false");
                          $this->update_encryptedoption($this->email, $email);
                          $this->update_encryptedoption($this->authkey, $authkey);
                          $this->update_encryptedoption($this->levelnum, "");
                          $this->update_encryptedoption($this->icmember, $icmember);
                          $activated = false;
                      } else if($this->CheckLicense()){
      				    $activated = true;
      				} else {
      				    $activated = false;
      				}
                  } else if($this->CheckLicense()){
      			   $activated = true;
      			} else {
      			   $activated = false;
      			}

            return $activated;
        }

        function updates_exclude($r, $url) {
            if (0 !== strpos($url, 'http://api.wordpress.org/plugins/update-check'))
                return $r; // Not a plugin update request. Bail immediately.
            $plugins = unserialize($r['body']['plugins']);
            unset($plugins->plugins[plugin_basename(__FILE__)]);
            unset($plugins->active[array_search(plugin_basename(__FILE__), $plugins->active)]);
            $r['body']['plugins'] = serialize($plugins);
            return $r;
        }

        function plugin_get($i) {
            if (!function_exists('get_plugins'))
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            $plugin_folder = get_plugins('/' . plugin_basename(dirname($this->pluginfile)));
            $plugin_file = basename(( $this->pluginfile));
            return $plugin_folder[$plugin_file][$i];
        }

        function check_activation() {
            wp_schedule_event(time(), 'twicedaily', array(&$this, $this->pref . 'check_event'));
        }

        function check_update() {
            global $wp_version;
            $plugin_folder = plugin_basename(dirname($this->pluginfile));
            $plugin_file = basename(( $this->pluginfile));
            if (defined('WP_INSTALLING'))
                return false;
            $response = wp_remote_get($this->updateurl);
            list($version, $url) = explode('|', $response['body']);
            if ($this->plugin_get("Version") == $version)
                return false;
            $plugin_transient = get_site_transient('update_plugins');
            $a = array(
                'slug' => $plugin_folder,
                'new_version' => $version,
                'url' => $this->plugin_get("AuthorURI"),
                'package' => $url
            );
            $o = (object) $a;
            $plugin_transient->response[$plugin_folder . '/' . $plugin_file] = $o;
            set_site_transient('update_plugins', $plugin_transient);
        }

        function check_deactivation() {
            wp_clear_scheduled_hook($this->pref . 'check_event');
        }

        function licensepage($parent_slug, $menu_title = "Update License") {
            add_submenu_page($parent_slug, $menu_title, $menu_title, 'manage_options', $parent_slug . '-updatelicense', array(&$this, 'updatelicense'));
        }

        function updatelicense($buttonText = "Update License") {
            $this->menupage($buttonText);
        }

        function encryptoptions($array) {
            $compressedarray = json_encode($array);
            $encrypted = urlencode(strtr(base64_encode(addslashes(serialize($compressedarray))), '+/=', '-_,'));
            update_option($this->pref . "data", $encrypted);
        }

        function update_encryptedoption($item, $value) {
            $encrypted = get_option($this->pref . "data");
            $decryptedoptions = urldecode(unserialize(stripslashes(base64_decode(strtr($encrypted, '-_,', '+/=')))));
            $uncompressedarray = json_decode($decryptedoptions, true);
            $uncompressedarray[$item] = $value;
            $compressedarray = json_encode($uncompressedarray);
            $encrypted = urlencode(strtr(base64_encode(addslashes(serialize($compressedarray))), '+/=', '-_,'));
            update_option($this->pref . "data", $encrypted);
        }

        function get_decryptedoption($item) {
            $encryptedoptions = get_option($this->pref . "data");
            $decryptedoptions = urldecode(unserialize(stripslashes(base64_decode(strtr($encryptedoptions, '-_,', '+/=')))));
            $uncompressedarray = json_decode($decryptedoptions, true);
            return $uncompressedarray[$item];
        }

		function checksellosity_active() {
            $encryptedoptions = get_option("imscso_data");
            $decryptedoptions = urldecode(unserialize(stripslashes(base64_decode(strtr($encryptedoptions, '-_,', '+/=')))));
            $uncompressedarray = json_decode($decryptedoptions, true);
			if($uncompressedarray['imscso_activation_state'] == 'true'){
				return true;
			} else {
				return false;
			}
        }

    }
}


//include_once 'functions/imscpblicense.php';
$imscpbsettings = array(
    "headerlogo" => IMSCPB_URL . "/images/hl.jpg",
    "headerbg" => IMSCPB_URL . "/images/hbg.jpg",
    "headerheight" => "100px",
    "icon" => IMSCPB_URL . "/images/icon.png",
    "pluginname" => "WP ProfitBuilder",
    "pluginprefix" => "imscpb_",
    "pluginfile" => __FILE__,
    "pluginadmin" => "admin.php?page=profitbuilder",
    "menutitle" => "Activate WP ProfitBuilder",
    "serverurl" => "wpprofitbuilder.com",
	  "pluginname" => "profitbuilder",
    "parentmenu" => "profitbuilder",
    "authlocation" => "/members/wp-content/plugins/license-checker/authorize_domain.php",
    "memberurl" => "http://wpprofitbuilder.com/members/wp-login.php?action=lostpassword",
    "minlevel" => 1,
);


global $imscpb_lc;
if (class_exists('imscpblicense')) {
    $imscpb_lc = new imscpblicense($imscpbsettings);
}

if(!$imscpb_lc->CheckLicense()){
    return;
}

global $pbuilder;
$pbuilder = new ProfitBuilder(IMSCPB_FILE);

class ProfitBuilder {

    var $main, $path, $name, $url, $menu_controls, $row_controls, $shortcodes, $rows, $icons, $showall, $yoast, $hideifs, $groups;
    var $standard_fonts = array("Arial", "Arial+Black", "Tahoma", "Trebuchet+MS", "Verdana", "Century+Gothic", "Geneva", "Lucida", "Lucida+Sans", "Lucida+Grande", "Courier", "Courier+New", "Georgia", "Times", "Times+New+Roman", "MS+Serif", "New+York", "Palatino", "Palatino+Linotype", "Courier", "Courier+New", "Lucida+Console", "Monaco", "Helvetica", "Impact");
    var $standard_fonts_variants = array("regular", "italic");

    function __construct($file) {
        $this->main = $file;
        $this->set_memory_limit();
        $this->init();
        return $this;
    }

    function init() {
    		global $wpdb,$imscpb_lc;

    		$profit_builder_version=get_option('profit_builder_version');
    		if(!$profit_builder_version || version_compare($profit_builder_version,'2.0.0','<')){
    			update_option('profit_builder_version','2.0.0');
    			$wpdb->query('ALTER TABLE '.$wpdb->prefix.'profit_builder_pages ADD COLUMN page_status varchar(20) DEFAULT "publish"');
    			$wpdb->query('ALTER TABLE '.$wpdb->prefix.'profit_builder_pages ADD COLUMN page_date varchar(20) DEFAULT 0');
    			$wpdb->query('ALTER TABLE '.$wpdb->prefix.'profit_builder_pages CHANGE COLUMN `id` `id` MEDIUMINT(9) NOT NULL DEFAULT "0"');
    			$wpdb->query('ALTER TABLE '.$wpdb->prefix.'profit_builder_pages DROP PRIMARY KEY');
    			$wpdb->query('ALTER TABLE '.$wpdb->prefix.'profit_builder_pages ADD COLUMN `page_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

    		}

        $profit_builder_templates_version=get_option('profit_builder_templates_version');
    		if(!$profit_builder_templates_version || version_compare($profit_builder_templates_version,'2.0.0','<')){
          update_option('profit_builder_templates_version','2.0.0');
          $this->upgrade_page_content();
        } else if( $profit_builder_templates_version == '2.0.0' ){
          update_option('profit_builder_templates_version','2.1.8');
          $this->upgrade_page_content_218();
        }


        load_plugin_textdomain('profit-builder', false, dirname(plugin_basename(IMSCPB_FILE)) . '/languages/');
        add_filter('theme_page_templates', array(&$this, 'theme_page_templates'), 10, 3);
        add_filter('template_include', array(&$this, 'template_include'), 10, 1);
        $this->activate();

        require_once('functions/pb_notifications.php');

        $this->path = dirname(IMSCPB_FILE);
        $this->name = basename($this->path);
        $this->url = plugins_url("/{$this->name}/");
        /*
         * Code Added by Asim Ashraf - DevBatch
         * fixed css/javascript file issue over SSL
         * Date: 2-32014
         * Edit start
         */
        if (!defined('FORCE_SSL_ADMIN') && @$_SERVER["HTTP_X_FORWARDED_PROTO"] == "https") {
            $this->url = str_replace("http://", "https://", $this->url);
            define('FORCE_SSL_ADMIN', true);
            $_SERVER['HTTPS'] = 'on';
        }
        /*
         * Code Added by Asim Ashraf - DevBatch
         * fixed css/javascript file issue over SSL
         * Edit End
         */
        $this->groups = array(
            array(
                'id' => 'Basic',
                'label' => 'Basic',
                'img' => $this->url . 'images/icons/basic-shortcodes.png'
            ),
            array(
                'id' => 'Charts, Bars, Counters',
                'label' => 'Charts, Bars, Counters',
                'img' => $this->url . 'images/icons/charts-shortcodes.png'
            ),
            array(
                'id' => 'Content',
                'label' => 'Content',
                'img' => $this->url . 'images/icons/content-shortcodes.png'
            ),
            array(
                'id' => 'Advanced',
                'label' => 'Advanced',
                'img' => $this->url . 'images/icons/content-advanced.png'
            )
        );
        $this->groups = apply_filters("pbuilder_" . "groups", $this->groups);
        $this->admin_controls = array();
        $this->menu_controls = array();
        $this->row_controls = array();
        $this->column_controls = array();
        $this->shortcodes = array();
        $this->icons = array();


        if (is_admin()) {
            $this->admin_controls = $this->get_admin_controls();
            $this->admin_controls = apply_filters("pbuilder_" . "admin_controls", $this->admin_controls);
            $this->menu_controls = $this->get_menu_controls();
            $this->menu_controls = apply_filters("pbuilder_" . "menu_controls", $this->menu_controls);
            $this->row_controls = $this->get_row_controls();
            $this->row_controls = apply_filters("pbuilder_" . "row_controls", $this->row_controls);
            $this->column_controls = $this->get_column_controls();
            $this->column_controls = apply_filters("pbuilder_" . "column_controls", $this->column_controls);
            $this->shortcodes = $this->get_shortcodes();
            $this->shortcodes = apply_filters("pbuilder_" . "shortcodes", $this->shortcodes);
        }
        $this->rows = $this->get_rows();
        $this->rows = apply_filters("pbuilder_" . "rows", $this->rows);
        if (is_admin()) {
            $this->icons = $this->get_icons();
            $this->icons = apply_filters("pbuilder_" . "icons", $this->icons);
        }
        $this->showall = false;
        $this->yoast = false;
        $this->hideifs = array('parents' => array(), 'children' => array());
        define('FBUILDER_URL', $this->url);
        require_once($this->path . '/functions/shortcodes.php');
        $opt = $this->option('showall');
        if (!empty($opt) && $opt->value == 'true') {
            $this->showall = true;
            add_action('wp_ajax_nopriv_pbuilder_edit', array(&$this, 'ajax_edit'));
            add_action('wp_ajax_nopriv_pbuilder_shortcode', array(&$this, 'ajax_shortcode'));
            add_action('wp_ajax_nopriv_pbuilder_pages', array(&$this, 'ajax_pages'));
            add_action('wp_ajax_nopriv_pbuilder_lfpfields', array(&$this, 'ajax_pbuilder_lfpfields'));
            add_action('wp_ajax_nopriv_pbuilder_page_content', array(&$this, 'ajax_page_content'));
			      add_action('wp_ajax_nopriv_pbuilder_load_revision', array(&$this, 'ajax_load_revision'));

            add_action('wp_ajax_nopriv_pbuilder_contact_form', array(&$this, 'ajax_contact_form'));
        }
        if (is_admin()) {
            register_activation_hook($this->main, array(&$this, 'wp_activate'));
            add_action('admin_menu', array(&$this, 'admin_menu'));
            add_action('init', array(&$this, 'global_admin_includes'));
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_head', array(&$this, 'admin_head'));
            add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
            add_action('save_post', array(&$this, 'save_post'), 10, 3);
            add_action('admin_footer', array(&$this, 'admin_footer'));
            add_action('wp_ajax_pbuilder_check', array(&$this, 'ajax_check'));
            add_action('wp_ajax_pbuilder_switch', array(&$this, 'ajax_switch'));
			      add_action('wp_ajax_pbuilder_disable', array(&$this, 'ajax_disablepb'));
            add_action('wp_ajax_pbuilder_shortcode', array(&$this, 'ajax_shortcode'));
            add_action('wp_ajax_pbuilder_save', array(&$this, 'ajax_save'));
            add_action('wp_ajax_pbuilder_pages', array(&$this, 'ajax_pages'));
      			add_action('wp_ajax_pbuilder_page_revisions', array(&$this, 'ajax_revisions'));
            add_action('wp_ajax_pbuilder_lfpfields', array(&$this, 'ajax_pbuilder_lfpfields'));
      			add_action('wp_ajax_pbuilder_load_revision', array(&$this, 'ajax_load_revision'));


            if($imscpb_lc->GetLevelNo() == 2){
              add_action('wp_ajax_pbuilder_show_abtest', array(&$this, 'ajax_abtest'));

        			add_action('wp_ajax_pbuilder_add_funnel', array(&$this, 'ajax_add_funnel'));
        			add_action('wp_ajax_pbuilder_page_funnels', array(&$this, 'ajax_funnels'));
        			add_action('wp_ajax_pbuilder_funnel_pages', array(&$this, 'ajax_show_funnel_pages'));
        			add_action('wp_ajax_pbuilder_funnel_delete', array(&$this, 'ajax_funnel_delete'));

        			add_action('wp_ajax_pbuilder_funnel_add_page', array(&$this, 'ajax_funnel_add_page'));
        			add_action('wp_ajax_pbuilder_funnel_update', array(&$this, 'ajax_funnel_update_pages'));
        			add_action('wp_ajax_pbuilder_funnel_remove_page', array(&$this, 'ajax_funnel_remove_page'));
              add_action( 'delete_post', array(&$this,'funnel_cleanup'), 10 );
            }

            add_action('wp_ajax_pbuilder_lfpflow_fields', array(&$this, 'ajax_get_lfpflow_fields'));




            add_action('wp_ajax_pbuilder_import', array(&$this, 'ajax_import'));
            add_action('wp_ajax_pbuilder_export', array(&$this, 'ajax_export'));
            add_action('wp_ajax_pbuilder_export_template', array(&$this, 'ajax_export_template'));
            add_action('wp_ajax_pbuilder_remove_template', array(&$this, 'ajax_remove_template'));
            add_action('wp_ajax_pbuilder_page_content', array(&$this, 'ajax_page_content'));




            add_action('wp_ajax_pbuilder_export_html', array(&$this, 'ajax_export_html'));
            add_action('wp_ajax_pbuilder_template_save', array(&$this, 'ajax_template_save'));
            add_action('wp_ajax_pbuilder_admin_save', array(&$this, 'ajax_admin_save'));
            add_action('wp_ajax_pbuilder_edit', array(&$this, 'ajax_edit'));
            add_action('wp_ajax_pbuilder_contact_form', array(&$this, 'ajax_contact_form'));
            add_action('wp_ajax_nopriv_pbuilder_contact_form', array(&$this, 'ajax_contact_form'));
            add_action('wp_ajax_pbuilder_clone_post', array(&$this, 'ajax_pbuilder_clone_post'));
            add_action('wp_ajax_pbuilder_draft_post', array(&$this, 'ajax_pbuilder_clone_post'));
            add_action('wp_ajax_pbuilder_admin_template_install', array(&$this, 'ajax_admin_template_install'));
            add_action('wp_ajax_pbuilder_copy', array(&$this, 'ajax_copy'));
            add_action('wp_ajax_pbuilder_paste', array(&$this, 'ajax_paste'));
            add_action('wp_ajax_pbuilder_admin_fonts', array(&$this, 'ajax_admin_fonts'));
            // Ajax calls
            add_theme_support('post-thumbnails');
            // Add Filters
            add_filter('post_row_actions', array(&$this, 'add_liveedit_links'), 10, 2);
            add_filter('page_row_actions', array(&$this, 'add_liveedit_links'), 10, 2);
            add_action('post_submitbox_misc_actions', array(&$this, 'post_submitbox_misc_actions'));
            //add_action('post_submitbox_start', array(&$this, 'post_submitbox_misc_actions'));
            add_action('admin_print_footer_scripts', array(&$this, 'pb_bg_admin_print_footer_scripts'));
            add_action('admin_init', array(&$this, 'pb_bg_admin_init'));
        } else {
            //add_action('wp', array(&$this, 'refresh_variables'));
            add_action('wp_head', array(&$this, 'wp_head'));
            add_action('init', array(&$this, 'frontend_includes'));
            add_filter('the_content', array(&$this, 'replace_content'), 999);
            
            add_filter('get_the_excerpt', array(&$this, 'remove_content_before_excerpt_filter'), 9);
            add_filter('get_the_excerpt', array(&$this, 'add_content_after_excerpt_filter'),11);
        
            
            add_filter('get_the_excerpt', array(&$this, 'excerpt_filter'), 10);
            add_action('wp_print_footer_scripts', array(&$this, 'wp_print_footer_scripts'));
            add_action('template_redirect', array(&$this, 'check_evergreen_cookies'));
        }
        add_action('pbuilder_head', array(&$this, 'edit_page_includes'));
        add_action('admin_print_styles', array(&$this, 'admin_print_styles'));
        add_action('wp_print_styles', array(&$this, 'front_print_styles'));
        add_action('admin_bar_menu', array(&$this, 'admin_bar'), 81);
        add_action('init', array(&$this, 'check_leadpages'), 1);
        
        add_action('wp_ajax_pbuilder_fetch_shopify_single', array(&$this,'pbuilder_fetch_shopify_single') );
        add_action('wp_ajax_pbuilder_fetch_shopify_grid', array(&$this,'pbuilder_fetch_shopify_grid') );
        add_action('wp_ajax_pbuilder_fetch_shopify_grid_product', array(&$this,'pbuilder_fetch_shopify_grid_product') );
          

        if($imscpb_lc->GetLevelNo() == 2){
          add_action('wp_ajax_so_setup_split_test', array(&$this,'pbso_setup_split_test') );
          add_action('wp_ajax_so_update_split_test', array(&$this,'pbso_update_split_test') );
          add_action('wp_ajax_so_reset_split_test', array(&$this,'pbso_reset_split_test') );

          add_action('wp_ajax_so_delete_page', array(&$this,'pbso_delete_page') );
          add_action('wp_ajax_so_add_page', array(&$this,'pbso_add_page') );
          add_action('wp_ajax_so_clone_page', array(&$this,'pbso_clone_page') );
          add_action('wp_ajax_so_choose_winner', array(&$this,'pbso_choose_winner') );

          add_action('wp_ajax_nopriv_so_add_conversion', array(&$this,'pbso_add_conversion') );
          add_action('wp_ajax_so_add_conversion', array(&$this,'pbso_add_conversion') );
          add_filter('manage_page_posts_columns' , array(&$this, 'pbso_add_sticky_column') );
          add_action('manage_page_posts_custom_column' , array(&$this, 'pbso_stats_column'), 10, 2 );
          add_filter('page_row_actions', array(&$this, 'pbso_row_actions'), 10, 2);
          add_action('template_redirect', array(&$this,'pbso_track_test') );
          add_action('add_meta_boxes', array(&$this,'pbso_register_meta_boxes'));
          add_action('wp_footer', array(&$this,'pbso_front_js'));
        }



    }
    
    
    function pbuilder_fetch_shopify_single(){
       $product_url = $_POST['shopify_url'];
       
       $product = $this->pbuilder_fetch_shopify_product($product_url);
    
       wp_send_json_success($product); 
       die();
    }
    
    function pbuilder_fetch_shopify_grid(){     
      $products_url = $_POST['shopify_url'];
      $base_url = parse_url($products_url);
      $fetch_product_page = wp_remote_get($products_url, array('sslverify' => false, 'timeout' => 120));

			if(!is_wp_error($fetch_product_page) && !empty($fetch_product_page['response']['code']) && $fetch_product_page['response']['code'] == 200 && strpos($fetch_product_page['body'],'/collections')!==false){
         $product_urls = array();
         $dom = new DOMDocument;
         libxml_use_internal_errors(true);
         $dom->loadHTML($fetch_product_page['body']);
         $xpath = new DOMXPath($dom);
         
         
         $get_products = $xpath->query('//a[@href]');          
         if ($get_products->length > 0) {            
            foreach($get_products as $product_url){
              $product_url_raw=$product_url->getAttribute('href');
              
              
              
              if(strpos($product_url_raw,'products/')>0){
                $found_url = '';
                if(strpos($product_url_raw,'http') === false){
                  $found_url = $base_url['scheme'].'://'.$base_url['host'].'/'.$product_url->getAttribute('href');
                } else {
                  $found_url = $product_url->getAttribute('href');
                }
                if(array_search($found_url,$product_urls) === false){
                  $product_urls[] = $found_url;
                }
              }
              
              
            }
         }
         wp_send_json_success($product_urls); 
      }
      
    }
    
    function pbuilder_fetch_shopify_grid_product(){     
      $product_url = $_POST['shopify_url'];
      			
      $product = $this->pbuilder_fetch_shopify_product($product_url);
    
      wp_send_json_success($product); 
      die();
    }
    
    function pbuilder_shopify_clean_text($text){
      return trim(preg_replace('/\s\s+/', ' ', preg_replace('/[\x00-\x1F\x7F-\xFF]/', ' ', $text)));
    }
    
    function pbuilder_fetch_shopify_product($product_url){
      $fetch_product_page = wp_remote_get($product_url, array('sslverify' => false, 'timeout' => 120));
			mb_internal_encoding("UTF-8");
      
      if(!is_wp_error($fetch_product_page) && !empty($fetch_product_page['response']['code']) && $fetch_product_page['response']['code'] == 200 && strpos($fetch_product_page['body'],'cdn.shopify')!==false){
         
         if(strpos($fetch_product_page['body'],'var meta = {')!==false){
            $product_info_meta1 = explode('var meta = {',$fetch_product_page['body']);
            $product_info_meta2 = explode('};',$product_info_meta1[1]);
            $product_json_info = json_decode('{'.$product_info_meta2[0].'}');
         }
         
         $product = array();
         $dom = new DOMDocument;
         libxml_use_internal_errors(true);
         $dom->loadHTML($fetch_product_page['body']);
         $xpath = new DOMXPath($dom);
         
         $get_title = $xpath->query('//*[@itemprop="name"]');          
         if ($get_title->length > 0) {
            if(strlen($get_title->item(0)->getAttribute('content'))>0){
              $product['name'] = trim(preg_replace('/\s\s+/', ' ', $get_title->item(0)->getAttribute('content')));
            } else {
              $product['name'] = trim(preg_replace('/\s\s+/', ' ', $get_title->item(0)->nodeValue));
            }
         } else if(isset($product_json_info->product)){
            $product['name'] = $product_json_info->product->variants[0]->name;
         } else {
            $product['name'] = 'Name not found';
         }
         
         $get_price = $xpath->query('//*[@itemprop="price"]');     
         if ($get_price->length > 0) {
            if(strlen($get_price->item(0)->getAttribute('content'))>0){
              $product['price'] = trim(preg_replace('/\s\s+/', ' ', $get_price->item(0)->getAttribute('content')));
            } else {
              $product['price'] = trim(preg_replace('/\s\s+/', ' ', $get_price->item(0)->nodeValue));
            }
         } else if(isset($product_json_info->product)){
            $product['price'] = number_format($product_json_info->product->variants[0]->price/100,2);
         } else {
            $product['price'] = '0.00';
         }
         
         $get_currency = $xpath->query('//*[@itemprop="priceCurrency"]');          
         if ($get_currency->length > 0) {
            if(strlen($get_currency->item(0)->getAttribute('content'))>0){
              $product['currency'] = trim(preg_replace('/\s\s+/', ' ', $get_currency->item(0)->getAttribute('content')));
            } else {
              $product['currency'] = '$';
            }
         } else {
            $product['currency'] = '$';
         }
         
         $get_description = $xpath->query('//*[@itemprop="description"]');          
         if ($get_description->length > 0) {
            $product['description'] = $this->pbuilder_shopify_clean_text($get_description->item(0)->nodeValue);
         } else {
            $product['description'] = '';
         }
         
         $get_images = $xpath->query('//img[@src]');          
         if ($get_images->length > 0) {
            
            $image_urls = array();
            foreach($get_images as $image){
              $image_path = $image->getAttribute('src');
              if(strpos($image_path,'cdn.shopify.com')>0 && strpos($image_path,'v=')>0){
                $image_urls[] =$image_path;
              }
            }
            $product['images'] = $image_urls;
         }
         
      }	else {      
        $product['name'] = 'Product not found';
        $product['price'] = '0.00';
        $product['currency'] = '$';
        $product['description'] = '';
        $product['images'] = array();
      }
      
      $product['url'] = $product_url;
        
      return $product;
    }
    
    
    
    function remove_content_before_excerpt_filter($content)
    {
        if (has_filter( 'the_content', array(&$this,'replace_content') ))
        {
            remove_filter( 'the_content', array(&$this,'replace_content'), 999 ); // if this filter got priority different from 10 (default), you need to specify it
        }
        return $content;
    }
            
    function add_content_after_excerpt_filter($content)
    {
        add_filter( 'the_content', array(&$this,'replace_content'), 999 ); // if this filter got priority different from 10 (default), you need to specify it
        return $content;
    }

    function check_evergreen_cookies(){
      global $post;
      if(isset($post->ID)){
        $evergreen_page_cookie_ids=get_post_meta($post->ID, 'evergreen_cookie_ids', true);
        if($evergreen_page_cookie_ids){
          $evergreen_cookie_ids = explode(',' , get_post_meta($post->ID, 'evergreen_cookie_ids', true));
        } else {
          $evergreen_cookie_ids=array();
        }


        foreach($evergreen_cookie_ids as $cookie_id){
          if(isset($_COOKIE["pbtimer_end_phpcookie".$cookie_id])){
            $evergreen_data=json_decode(stripslashes($_COOKIE["pbtimer_end_phpcookie".$cookie_id]));
            $timeout_seconds=$evergreen_data->unixend-time();


            if($timeout_seconds<0 && strlen($evergreen_data->timeouturl)>0 && !current_user_can('administrator')){
              header('Location: '.$evergreen_data->timeouturl);
                exit();
            } else {
              //echo "Time to redirect: ".$timeout_seconds;
              //echo "Redirect to: ".$evergreen_data->timeouturl;
            }
          }
        }
      }
    }


    function add_meta_boxes() {
		    global $post,$wpdb;

        $curtheme = get_template(); //
        if ($curtheme != "pbtheme") {
            add_meta_box('profitbuilder_templates', 'ProfitBuilder Templates', array(&$this, 'profitbuilder_templates'), 'page', 'side');
        }

    		$pb_enabled = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix . 'profit_builder_pages WHERE id = "' . $post->ID . '" AND page_status="publish" ORDER BY page_date DESC');
    		if($pb_enabled && $pb_enabled->id == $post->ID){
    			add_meta_box('profitbuilder_disable_pb', 'Purge Page from ProfitBuilder', array(&$this, 'profitbuilder_disable_pb'), 'page', 'side');
          add_meta_box('profitbuilder_disable_pb', 'Purge Page from ProfitBuilder', array(&$this, 'profitbuilder_disable_pb'), 'post', 'side');
          add_meta_box('profitbuilder_disable_pb', 'Purge Page from ProfitBuilder', array(&$this, 'profitbuilder_disable_pb'), 'product', 'side');
    		}

		    add_meta_box('profitbuilder_exit_redirect', 'ProfitBuilder Exit Redirect', array(&$this, 'profitbuilder_exit_redirect'), 'page', 'side');
    }

	function profitbuilder_disable_pb($object, $box) {
	  ?>
	  <div class="button" id="pbuilder_disable">Purge Page from ProfitBuilder</div><br /><br />
	  Disable ProfitBuilder and restore original page content.
	  <?php
    }

    function profitbuilder_templates($object, $box) {
        global $post;
        $page_template_current = !empty($post->profitbuilder_page_template) ? $post->profitbuilder_page_template : "";
        $page_templates = $this->get_templates();
        $curtheme = get_template(); //pbtheme
        ?>
        <p><strong><?php _e('Template') ?></strong></p>
        <label class="screen-reader-text" for="page_template"><?php _e('Page Template') ?></label>
        <select name="profitbuilder_page_template" id="profitbuilder_page_template" onchange="">
            <option value="" <?php if ($page_template == "") echo " selected='true' " ?>><?php _e('Theme Template'); ?></option>
            <?php
            ksort($page_templates);
            foreach (array_keys($page_templates) as $page_template) {
                $selected = selected($page_template_current, $page_templates[$page_template], false);
                echo "\n\t" . '<option value="' . $page_templates[$page_template] . '" ' . $selected . ' >' . $page_template . '</option>';
            }
            ?>
        </select>
        <p>
            <label for="pb_page_width"><?php _e("Page Width"); ?></label><br />
            <input type="text" class="widefat" name="pb_page_width" id="pb_page_width" value="<?php echo esc_attr(get_post_meta($object->ID, 'pb_page_width', true)); ?>" /><br />
            <label for="pb_page_width"><?php _e("Container width in px or %"); ?></label>
        </p>
        <div id="profitbuilder_page_background" style="display: none;">
            <p>
            <hr/>
            <?php _e('Override default page background'); ?>
        </p>
        <p>
            <label for="pb-page-bg"><?php _e("Select background type"); ?> :</label>
            <?php
            $feat_areas = array(
                'none' => __('None'),
                'bgimage' => __('Image'),
                'videoembed' => __('Video Embed'),
                'html5video' => __('HTML5 Video'),
            );
            $current = get_post_meta($object->ID, 'pb_page_bg', true);
            if ($current == '') {
                $current = 'none';
            }
            foreach ($feat_areas as $s => $v) :
                ?>
                <br />
                <input type="radio" name="pb-page-bg" id="pb-page-bg" value="<?php echo $s; ?>" <?php echo ( ( $s == $current ) ? 'checked' : '' ); ?>/> <?php echo $v; ?>
            <?php endforeach; ?>
        </p>
        <p>
            <label for="pb-page-image"><?php _e("Enter image URL."); ?></label>
            <br />
            <textarea class="widefat" type="text" name="pb-page-image" id="pb-page-image"><?php echo esc_attr(get_post_meta($object->ID, 'pb_page_image', true)); ?></textarea><br />
            <a href="media-upload.php?post_id=0&pb_bg=true&amp;TB_iframe=1" class="thickbox button upload-button-select" title="Add Media">Select Image</a>
        </p>
        <p>
            <label for="pb-pagevideo-mp4"><?php _e("Enter video URL (MP4)."); ?></label>
            <br />
            <textarea class="widefat" type="text" name="pb-pagevideo-mp4" id="pb-pagevideo-mp4"><?php echo esc_attr(get_post_meta($object->ID, 'pb_pagevideo_mp4', true)); ?></textarea>
        </p>
        <p>
            <label for="pb-pagevideo-ogv"><?php _e("Enter video URL (OGV)."); ?></label>
            <br />
            <textarea class="widefat" type="text" name="pb-pagevideo-ogv" id="pb-pagevideo-ogv"><?php echo esc_attr(get_post_meta($object->ID, 'pb_pagevideo_ogv', true)); ?></textarea>
        </p>
        <p>
            <label for="pb-pagevideo-embed"><?php _e("Enter Youtube Video ID."); ?></label>
            <br />
            <input class="widefat" type="text" name="pb-pagevideo-embed" id="pb-pagevideo-embed" value="<?php echo esc_attr(get_post_meta($object->ID, 'pb_pagevideo_embed', true)); ?>" / >
        </p>
        <?php
        $mute = get_post_meta($object->ID, 'pb_pagevideo_embed_mute', true);
        ?>
        <p>
            <label for="pb-pagevideo-embed-mute"><input value="" type="checkbox" name="pb-pagevideo-embed-mute" id="pb-pagevideo-embed-mute" <?php if (1 == $mute) echo 'checked="checked"'; ?>> <?php _e("Mute Youtube Video"); ?></label>
        </p>
        <?php
        $loop = get_post_meta($object->ID, 'pb_pagevideo_embed_loop', true);
        ?>
        <p>
            <label for="pb-pagevideo-embed-loop"><input value="" type="checkbox" name="pb-pagevideo-embed-loop" id="pb-pagevideo-embed-loop" <?php if (1 == $loop) echo 'checked="checked"'; ?>> <?php _e("Loop Youtube Video"); ?></label>
        </p>
        <?php
        $hd = get_post_meta($object->ID, 'pb_pagevideo_embed_hd', true);
        ?>
        <p>
            <label for="pb-pagevideo-embed-hd"><input value="" type="checkbox" name="pb-pagevideo-embed-hd" id="pb-pagevideo-embed-hd" <?php if (1 == $hd) echo 'checked="checked"'; ?>> <?php _e("HD Youtube Video"); ?></label>
        </p>
        <p>
        <hr/>
        <?php _e('Retargeting Pixel for Page'); ?>
        </p>
        <p>
            <label for="pb_retargetpixel"><?php _e("Put your retargeting pixel code here"); ?></label>
            <br />
            <textarea class="widefat" type="text" name="pb_retargetpixel" id="pb_retargetpixel"><?php echo esc_attr(get_post_meta($object->ID, 'pb_retargetpixel', true)); ?></textarea>
        </p>
        </div>
        <script type="text/javascript">
            (function ($) {
                "use strict";
                $(document).ready(function () {
                    $("#profitbuilder_page_template").bind("change", function () {
                        if ($(this).val() != "")
                            $("#profitbuilder_page_background").show();
                        else
                            $("#profitbuilder_page_background").hide();
                    }).trigger("change");
                    window.send_to_editor2 = function (html) {
                        var image_url = jQuery('img', html).attr('src');
                        //console.log(image_url);
                        if (image_url == "" || image_url == undefined)
                            image_url = jQuery(html).attr('src');
                        jQuery('#pb-page-image').val(image_url);
                        tb_remove();
                    }
                });
            })(jQuery);
        </script>
        <?php


    }



    function profitbuilder_exit_redirect($object, $box) {
        $pb_redirect_time = esc_attr(get_post_meta($object->ID, 'pb_redirect_time', true));
        if ($pb_redirect_time == "")
            $pb_redirect_time = "200";
        ?>
        <div class="pb_exite_redirect">
            <p>
                <label for="pb_redirect_enable">
                    <input value="" type="checkbox" name="pb_redirect_enable" id="pb_redirect_enable" <?php if (get_post_meta($object->ID, 'pb_redirect_enable', true) == 1) echo 'checked="checked"'; ?> /><?php _e("Enable Exit Redirect on page"); ?></label>
            </p>
            <p>
                <label for="pb_redirect_url"><?php _e("Redirect to URL"); ?></label>
                <br />
                <input class="widefat" type="text" name="pb_redirect_url" id="pb_redirect_url" value="<?php echo esc_attr(get_post_meta($object->ID, 'pb_redirect_url', true)); ?>" />
            </p>
            <p>
                <label for="pb_redirect_time"><?php _e("Time to redirect"); ?></label>
                <br />
                <input class="widefat" type="text" name="pb_redirect_time" id="pb_redirect_time" value="<?php echo $pb_redirect_time; ?>" />
            </p>
            <p>
                <label for="pb_redirect_message"><?php _e("Redirect Browser Message"); ?></label>
                <br />
                <textarea class="widefat" name="pb_redirect_message" id="pb_redirect_message"><?php echo esc_attr(get_post_meta($object->ID, 'pb_redirect_message', true)); ?></textarea>
            </p>
        </div>
        <?php
    }

    function save_post($post_ID, $post_obj, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (isset($_POST['post_type']) && 'page' == $_POST['post_type'])
            if (!current_user_can('edit_page', $post_id))
                return;
            else
            if (!current_user_can('edit_post', $post_id))
                return;
        if ( @is_array($_POST['meta'])) {
            $Pkey = array();
            foreach ($_POST['meta'] as $Pkey => $Pvalue):
                if ($Pvalue['key'] == "profitbuilder_page_template") {
                    //	$pValue = isset($_POST['profitbuilder_page_template']) && $_POST['profitbuilder_page_template'] != "" ? $_POST['profitbuilder_page_template'] : $Pvalue['value'];
                    if (!update_post_meta($post_ID, 'profitbuilder_page_template', $Pvalue['value']))
                        add_post_meta($post_ID, 'profitbuilder_page_template', $Pvalue['value'], true);
                }
                if ($Pvalue['key'] == "pb_page_width") {
                    //	$pValue = isset($_POST['pb_page_width']) && $_POST['pb_page_width'] != "" ? $_POST['pb_page_width'] : $Pvalue['value'];
                    if (!update_post_meta($post_ID, 'pb_page_width', $Pvalue['value']))
                        add_post_meta($post_ID, 'pb_page_width', $Pvalue['value'], true);
                }
                if ($Pvalue['key'] == "pb-page-bg") {
                    //	$pValue = isset($_POST['pb-page-bg']) && $_POST['pb-page-bg'] != "" ? $_POST['pb-page-bg'] : $Pvalue['value'];
                    if (!update_post_meta($post_ID, 'pb_page_bg', $Pvalue['value']))
                        add_post_meta($post_ID, 'pb_page_bg', $Pvalue['value'], true);
                }
                if ($Pvalue['key'] == "pb-page-image") {
                    //	$pValue = isset($_POST['pb-page-image']) && $_POST['pb-page-image'] != "" ? $_POST['pb-page-image'] : $Pvalue['value'];
                    if (!update_post_meta($post_ID, 'pb_page_image', $Pvalue['value']))
                        add_post_meta($post_ID, 'pb_page_image', $Pvalue['value'], true);
                }
                if ($Pvalue['key'] == "pb-pagevideo-mp4") {
                    //	$pValue = isset($_POST['pb-pagevideo-mp4']) && $_POST['pb-pagevideo-mp4'] != "" ? $_POST['pb-pagevideo-mp4'] : $Pvalue['value'];
                    if (!update_post_meta($post_ID, 'pb_pagevideo_mp4', $Pvalue['value']))
                        add_post_meta($post_ID, 'pb_pagevideo_mp4', $Pvalue['value'], true);
                }
                if ($Pvalue['key'] == "pb-pagevideo-ogv") {
                    //	$pValue = isset($_POST['pb-pagevideo-ogv']) && $_POST['pb-pagevideo-ogv'] != "" ? $_POST['pb-pagevideo-ogv'] : $Pvalue['value'];
                    if (!update_post_meta($post_ID, 'pb_pagevideo_ogv', $Pvalue['value']))
                        add_post_meta($post_ID, 'pb_pagevideo_ogv', $Pvalue['value'], true);
                }
                if ($Pvalue['key'] == "pb-pagevideo-embed") {
                    //		$pValue = isset($_POST['pb-pagevideo-embed']) && $_POST['pb-pagevideo-embed'] != "" ? $_POST['pb-pagevideo-embed'] : $Pvalue['value'];
                    if (!update_post_meta($post_ID, 'pb_pagevideo_embed', $Pvalue['value']))
                        add_post_meta($post_ID, 'pb_pagevideo_embed', $Pvalue['value'], true);
                }
                if ($Pvalue['key'] == "pb-pagevideo-embed-mute") {
                    //		$pValue = isset($_POST['pb-pagevideo-embed-mute']) && $_POST['pb-pagevideo-embed-mute'] != "" ? $_POST['pb-pagevideo-embed-mute'] : $Pvalue['value'];
                    $value = $Pvalue['value'] > 0 ? $Pvalue['value'] : 0;
                    if (!update_post_meta($post_ID, 'pb_pagevideo_embed_mute', $value))
                        add_post_meta($post_ID, 'pb_pagevideo_embed_mute', $value, true);
                }
                if ($Pvalue['key'] == "pb-pagevideo-embed-loop") {
                    //	$pValue = isset($_POST['pb-pagevideo-embed-loop']) && $_POST['pb-pagevideo-embed-loop'] != "" ? $_POST['pb-pagevideo-embed-loop'] : $Pvalue['value'];
                    $value = $Pvalue['value'] > 0 ? $Pvalue['value'] : 0;
                    if (!update_post_meta($post_ID, 'pb_pagevideo_embed_loop', $value))
                        add_post_meta($post_ID, 'pb_pagevideo_embed_loop', $value, true);
                }
                if ($Pvalue['key'] == "pb-pagevideo-embed-hd") {
                    //	$pValue = isset($_POST['pb-pagevideo-embed-hd']) && $_POST['pb-pagevideo-embed-hd'] != "" ? $_POST['pb-pagevideo-embed-hd'] : $Pvalue['value'];
                    $value = $Pvalue['value'] > 0 ? $Pvalue['value'] : 0;
                    if (!update_post_meta($post_ID, 'pb_pagevideo_embed_hd', $value))
                        add_post_meta($post_ID, 'pb_pagevideo_embed_hd', $value, true);
                }
                if ($Pvalue['key'] == "pb_redirect_enable") {
                    $value = $Pvalue['value'] > 0 ? $Pvalue['value'] : 0;
                    if (!update_post_meta($post_ID, 'pb_redirect_enable', $value))
                        add_post_meta($post_ID, 'pb_redirect_enable', $value, true);
                }
                if ($Pvalue['key'] == "pb_retargetpixel") {
                    if (!update_post_meta($post_ID, 'pb_retargetpixel', $Pvalue['value']))
                        add_post_meta($post_ID, 'pb_retargetpixel', $Pvalue['value'], true);
                }
                if ($Pvalue['key'] == "pb_redirect_url") {
                    if (!update_post_meta($post_ID, 'pb_redirect_url', $Pvalue['value']))
                        add_post_meta($post_ID, 'pb_redirect_url', $Pvalue['value'], true);
                }
                if ($Pvalue['key'] == "pb_redirect_time") {
                    $value = !empty($Pvalue['value']) ? $Pvalue['value'] : 200;
                    if (!update_post_meta($post_ID, 'pb_redirect_time', $value))
                        add_post_meta($post_ID, 'pb_redirect_time', $value, true);
                }
                if ($Pvalue['key'] == "pb_redirect_message") {
                    if (!update_post_meta($post_ID, 'pb_redirect_message', $Pvalue['value']))
                        add_post_meta($post_ID, 'pb_redirect_message', $Pvalue['value'], true);
                }
            endforeach;
        }// end of if(is_array($_POST['meta']))
        //{
        // else code is old code written by Pashawer team I just put this into else if this method old developer used in any other function this should work for him.
        if (isset($_POST['profitbuilder_page_template'])) {
            if (!update_post_meta($post_ID, 'profitbuilder_page_template', $_POST['profitbuilder_page_template']))
                add_post_meta($post_ID, 'profitbuilder_page_template', $_POST['profitbuilder_page_template'], true);
        }
        if (isset($_POST['pb_page_width'])) {
            if (!update_post_meta($post_ID, 'pb_page_width', $_POST['pb_page_width']))
                add_post_meta($post_ID, 'pb_page_width', $_POST['pb_page_width'], true);
        }
        if (isset($_POST['pb-page-bg'])) {
            if (!update_post_meta($post_ID, 'pb_page_bg', $_POST['pb-page-bg']))
                add_post_meta($post_ID, 'pb_page_bg', $_POST['pb-page-bg'], true);
        }
        if (isset($_POST['pb-page-image'])) {
            if (!update_post_meta($post_ID, 'pb_page_image', $_POST['pb-page-image']))
                add_post_meta($post_ID, 'pb_page_image', $_POST['pb-page-image'], true);
        }
        if (isset($_POST['pb-pagevideo-mp4'])) {
            if (!update_post_meta($post_ID, 'pb_pagevideo_mp4', $_POST['pb-pagevideo-mp4']))
                add_post_meta($post_ID, 'pb_pagevideo_mp4', $_POST['pb-pagevideo-mp4'], true);
        }
        if (isset($_POST['pb-pagevideo-ogv'])) {
            if (!update_post_meta($post_ID, 'pb_pagevideo_ogv', $_POST['pb-pagevideo-ogv']))
                add_post_meta($post_ID, 'pb_pagevideo_ogv', $_POST['pb-pagevideo-ogv'], true);
        }
        if (isset($_POST['pb-pagevideo-embed'])) {
            if (!update_post_meta($post_ID, 'pb_pagevideo_embed', $_POST['pb-pagevideo-embed']))
                add_post_meta($post_ID, 'pb_pagevideo_embed', $_POST['pb-pagevideo-embed'], true);
        }
        $value = isset($_POST['pb-pagevideo-embed-mute']) ? 1 : 0;
        if (!update_post_meta($post_ID, 'pb_pagevideo_embed_mute', $value))
            add_post_meta($post_ID, 'pb_pagevideo_embed_mute', $value, true);
        $value = isset($_POST['pb-pagevideo-embed-loop']) ? 1 : 0;
        if (!update_post_meta($post_ID, 'pb_pagevideo_embed_loop', $value))
            add_post_meta($post_ID, 'pb_pagevideo_embed_loop', $value, true);
        $value = isset($_POST['pb-pagevideo-embed-hd']) ? 1 : 0;
        if (!update_post_meta($post_ID, 'pb_pagevideo_embed_hd', $value))
            add_post_meta($post_ID, 'pb_pagevideo_embed_hd', $value, true);
        $value = isset($_POST['pb_redirect_enable']) ? 1 : 0;
        if (!update_post_meta($post_ID, 'pb_redirect_enable', $value))
            add_post_meta($post_ID, 'pb_redirect_enable', $value, true);
        if (isset($_POST['pb_retargetpixel'])) {
            if (!update_post_meta($post_ID, 'pb_retargetpixel', $_POST['pb_retargetpixel']))
                add_post_meta($post_ID, 'pb_retargetpixel', $_POST['pb_retargetpixel'], true);
        }
        if (isset($_POST['pb_redirect_url'])) {
            if (!update_post_meta($post_ID, 'pb_redirect_url', $_POST['pb_redirect_url']))
                add_post_meta($post_ID, 'pb_redirect_url', $_POST['pb_redirect_url'], true);
        }
        if (isset($_POST['pb_redirect_time'])) {
            $value = !empty($_POST['pb_redirect_time']) ? $_POST['pb_redirect_time'] : 200;
            if (!update_post_meta($post_ID, 'pb_redirect_time', $value))
                add_post_meta($post_ID, 'pb_redirect_time', $value, true);
        }
        if (isset($_POST['pb_redirect_message'])) {
            if (!update_post_meta($post_ID, 'pb_redirect_message', $_POST['pb_redirect_message']))
                add_post_meta($post_ID, 'pb_redirect_message', $_POST['pb_redirect_message'], true);
        }
        //	}
    }

    function theme_page_templates($page_templates, $obj, $post) {
        return $page_templates;
    }

    function stylesheet_uri($stylesheet_uri, $stylesheet_dir_uri) {
        //print_r($stylesheet_uri."<br />");
        //print_r($stylesheet_dir_uri."<br />");
        return '';
    }

    function template_include($template) {
        global $post;
        $curtheme = get_template();
        if ($curtheme != "pbtheme" && $post->profitbuilder_page_template != "") {
            require_once 'page-templates/Mobile_Detect.php';
            $detect = new Mobile_Detect;
            add_filter('stylesheet_uri', array(&$this, 'stylesheet_uri'), 10, 2);
            //wp_enqueue_style('normalize-css', IMSCPB_URL.'/css/normalize.css');
            wp_enqueue_style('pbuilder_normalize_css', $this->url . 'css/normalize.min.css');
            wp_enqueue_style('pb-main-css', IMSCPB_URL . '/css/pb_style.css');
            //wp_enqueue_script('pb-main-js', IMSCPB_URL.'/js/pb_main.js', array( 'jquery' ), '1.0', true);
            $is_mobile = ($detect->isMobile() || $detect->isTablet()) ? "yes" : "no";
            $page_bg = get_post_meta(get_the_ID(), 'pb_page_bg', true);
            if ($page_bg !== '' && $page_bg !== 'none') {
                $entry_fallback = 'none';
                switch ($page_bg) :
                    case 'videoembed' :
                        $entry = do_shortcode(get_post_meta(get_the_ID(), 'pb_pagevideo_embed', true));
                        $entry_mute = get_post_meta(get_the_ID(), 'pb_pagevideo_embed_mute', true);
                        $entry_loop = get_post_meta(get_the_ID(), 'pb_pagevideo_embed_loop', true);
                        $entry_hd = get_post_meta(get_the_ID(), 'pb_pagevideo_embed_hd', true);
                        $entry_fallback = get_post_meta(get_the_ID(), 'pb_page_image', true); //wp_get_attachment_url(get_post_thumbnail_id(get_the_ID()) );
                        //$page_bg = get_post_meta(get_the_ID(),'pb_page_image',true);
                        break;
                    case 'html5video' :
                        $entry = 'none';
                        $entry_mute = 'none';
                        $entry_loop = 'none';
                        $entry_hd = 'none';
                        $entry_fallback = get_post_meta(get_the_ID(), 'pb_page_image', true); //'none';
                        //$page_bg = get_post_meta(get_the_ID(),'pb_page_image',true);
                        break;
                //default :
                //die( __('Invalid options.', 'pb') );
                endswitch;
            }
            wp_localize_script('pb-main-js', 'pbuilder', array(
                'page_bg' => @$page_bg,
                'video_bg' => @$entry,
                'video_mute' => @$entry_mute,
                'video_loop' => @$entry_loop,
                'video_hd' => @$entry_hd,
                'video_fallback' => @$entry_fallback,
                'is_mobile_tablet' => @$is_mobile,
                    )
            );
            $template = IMSCPB_DIR . "/page-templates/" . $post->profitbuilder_page_template;
        }
        return $template;
    }

    function activate() {
        global $wpdb;


        $table_name = $wpdb->prefix . 'profit_builder_pages';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $pbuilder_pages_sql = "CREATE TABLE " . $table_name . " (
						  page_id INT NOT NULL AUTO_INCREMENT,
						  id mediumint(9) NOT NULL,
						  switch text NOT NULL,
						  layout text NOT NULL,
						  items MEDIUMTEXT NOT NULL COLLATE utf8_general_ci,
						  page_status varchar(20) DEFAULT 'publish',
						  page_date varchar(20) DEFAULT 0,
						  PRIMARY KEY (page_id)
						);";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($pbuilder_pages_sql);
        }
        $table_name = $wpdb->prefix . 'profit_builder_options';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $pbuilder_options_sql = "CREATE TABLE " . $table_name . " (
			              id mediumint(9) NOT NULL AUTO_INCREMENT,
						  name text NOT NULL,
						  value text NOT NULL,
						  PRIMARY KEY (id)
						);";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($pbuilder_options_sql);
        }
        $table_name = $wpdb->prefix . 'profit_builder_extensions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $pbuilder_options_sql = "CREATE TABLE " . $table_name . " (
			              id mediumint(9) NOT NULL AUTO_INCREMENT,
						  name text NOT NULL,
						  admin_controls_list_url text NOT NULL,
						  font_head_url text NOT NULL,
						  PRIMARY KEY (id)
						);";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($pbuilder_options_sql);
        }
        $table_name = $wpdb->prefix . 'profit_builder_copy_paste';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $pbuilder_options_sql = "CREATE TABLE " . $table_name . " (
			              `id` int(10) NOT NULL AUTO_INCREMENT,
                          `userid` int(10) DEFAULT NULL,
                          `copiedtype` varchar(50) DEFAULT NULL,
                          `copiedoptions` text,
                          `copiedtext` text,
                          `copieddate` varchar(255) DEFAULT NULL,
                          PRIMARY KEY (`id`)
                        );";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($pbuilder_options_sql);
        }
        $table_name = $wpdb->prefix . 'profit_builder_templates';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $pbuilder_options_sql = "CREATE TABLE " . $table_name . " (
			              `id` int(11) NOT NULL AUTO_INCREMENT,
                          `userid` int(11) DEFAULT NULL,
                          `temp_name` varchar(255) DEFAULT NULL,
                          `temp_desc` varchar(255) DEFAULT NULL,
                          `temp_cat` varchar(255) DEFAULT NULL,
                          `temp_ver` varchar(255) DEFAULT NULL,
                          `temp_date` varchar(255) DEFAULT NULL,
                          PRIMARY KEY (`id`)
                        );";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($pbuilder_options_sql);
        }

        $table_name = $wpdb->prefix . 'profit_builder_ab_stats';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $pbuilder_options_sql = "CREATE TABLE " . $table_name . " (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `date` date NOT NULL,
              `post_id` int(10) unsigned NOT NULL,
              `views` mediumint(8) unsigned NOT NULL DEFAULT '0',
              `views_unique` mediumint(8) unsigned NOT NULL DEFAULT '0',
              `conversions` mediumint(8) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE KEY `date` (`date`,`post_id`)
            );";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($pbuilder_options_sql);
        }


        $table_name = $wpdb->prefix . 'profit_builder_funnel_stats';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $pbuilder_options_sql = "CREATE TABLE " . $table_name . " (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `date` date NOT NULL,
              `post_id` int(10) unsigned NOT NULL,
              `views` mediumint(8) unsigned NOT NULL DEFAULT '0',
              `views_unique` mediumint(8) unsigned NOT NULL DEFAULT '0',
              `conversions` mediumint(8) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE KEY `date` (`date`,`post_id`)
            );";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($pbuilder_options_sql);
        }

    }


	  function upgrade_page_content() {
    		global $wpdb,$imscpb_lc;

        $imscpb_lc->VerifyLicense();

    		for($table_id = 1; $table_id < 10 ; $table_id ++){
    			$table_name = $wpdb->prefix . 'profit_builder_pages_backup_'.$table_id;
    			if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    				$pbuilder_pages_sql = "CREATE TABLE " . $table_name . " (
    							  page_id INT NOT NULL AUTO_INCREMENT,
    							  id mediumint(9) NOT NULL,
    							  switch text NOT NULL,
    							  layout text NOT NULL,
    							  items MEDIUMTEXT NOT NULL COLLATE utf8_general_ci,
    							  page_status varchar(20) DEFAULT 'publish',
    							  page_date varchar(20) DEFAULT 0,
    							  PRIMARY KEY (page_id)
    							);";
    				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($pbuilder_pages_sql);

    				$wpdb->get_var("INSERT INTO ". $table_name ." SELECT * FROM ". $wpdb->prefix . "profit_builder_pages");
    				break;
    			}
    		}


    		$table_name = $wpdb->prefix . 'profit_builder_pages';
    		$page_templates = $wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY page_id DESC');

    		foreach($page_templates as $page){
    			$items_decoded = json_decode(stripcslashes($page->items));

    			$items_decoded = $this->template_upgrade_v2($items_decoded);

    			$page->items = addslashes(json_encode($items_decoded));

    			$table_name = $wpdb->prefix . 'profit_builder_pages';

          $wpdb->update(
    				$table_name,
    				array('page_status' => 'publish', 'items' => $page->items),
    				array('page_id' => $page->page_id, 'page_status'=>'publish'),
    				array('%s','%s'),
    				array('%d')
    			);

    		}

    }
    
    function upgrade_page_content_218() {
    		global $wpdb,$imscpb_lc;

    		$table_name = $wpdb->prefix . 'profit_builder_pages';
    		$page_templates = $wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY page_id DESC');

    		foreach($page_templates as $page){
    			$items_decoded = json_decode(stripcslashes($page->items));
          
          foreach( $items_decoded->items as $itemid=>$item ){
            if( $item->slug == 'button' ){
              
              if(!isset($item->options->subtext)){
                $items_decoded->items[$itemid]->options->margin_padding='0|0|36|0|20|20|20|20';
                $items_decoded->items[$itemid]->options->border='false|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';
                $items_decoded->items[$itemid]->options->subtext='';
              } 
            }
          }
                    
          
    			$page->items = addslashes(json_encode($items_decoded));

    			$table_name = $wpdb->prefix . 'profit_builder_pages';

          $wpdb->update(
    				$table_name,
    				array('page_status' => 'publish', 'items' => $page->items),
    				array('page_id' => $page->page_id, 'page_status'=>'publish'),
    				array('%s','%s'),
    				array('%d')
    			);

    		}

    }

    function upgrade_single_page_content($page_id) {
    		global $wpdb;

        $table_name = $wpdb->prefix . 'profit_builder_pages_backup_1';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name){
          $page_templates = $wpdb->get_results('SELECT * FROM '.$table_name.' WHERE id='.$page_id);

      		foreach($page_templates as $page){
      			$items_decoded = json_decode(stripcslashes($page->items));

      			$items_decoded = $this->template_upgrade_v2($items_decoded);

      			$page->items = addslashes(json_encode($items_decoded));

      			$table_name = $wpdb->prefix . 'profit_builder_pages';



            $wpdb->update(
      				$table_name,
      				array('page_status' => 'revision'),
      				array('id' => $page->page_id,'page_status' => 'publish'),
      				array('%s'),
      				array('%d','%s')
      			);

            $wpdb->update(
      				$table_name,
      				array('page_status' => 'publish', 'items' => $page->items),
      				array('id' => $page->page_id,'page_status' => 'publish'),
      				array('%s','%s'),
      				array('%d','%s')
      			);

      		}
        } else {
          return "No Backup Table Found.";
        }

    }

    /**
     * Add the link to action list for post_row_actions
     */
    function add_liveedit_links($actions, $post) {
        $actions['livepbedit'] = '<a href="' . admin_url("admin-ajax.php?action=pbuilder_edit&p=" . $post->ID) . '">Live Edit</a>';
        $actions['pbclonepost'] = '<a href="' . admin_url("admin-ajax.php?action=pbuilder_clone_post&s=publish&p=" . $post->ID) . '">Clone Page</a>';
        $actions['pbdraftpost'] = '<a href="' . admin_url("admin-ajax.php?action=pbuilder_draft_post&s=draft&p=" . $post->ID) . '">Create Draft</a>';
        return $actions;
    }

    function post_submitbox_misc_actions() {
        if (isset($_GET['post'])) {
            $post_id = $_GET['post'];
            ?>
            <div class="misc-pub-section  misc-pub-pboptions">
                <a href="<?php echo admin_url("admin-ajax.php?action=pbuilder_clone_post&s=publish&p=" . $post_id); ?>" class="button button-primary button-large">Clone Page</a>
                <a href="<?php echo admin_url("admin-ajax.php?action=pbuilder_draft_post&s=draft&p=" . $post_id); ?>" class="button button-primary button-large">Create Draft</a>
            </div>
            <?php
        }
    }

    function refresh_post_names() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'profit_builder_pages';
        $rows = $wpdb->get_results('SELECT id,post_slug FROM ' . $table_name);
        if (count($rows) != 0) {/*
          $wpdb->update(
          $table_name,
          array(
          'switch' => ($switch ? $switch : $rows[0]->switch),
          'layout' => ($layout ? $layout : $rows[0]->layout),
          'items'=> ($items ? $items : $rows[0]->items)),
          array( 'id' => $id ),
          array(
          '%s',
          '%s',
          '%s'),
          array('%d')
          ); */
        }
    }

    function refresh_post_ids() {

    }

    function wp_activate() {
        do_action('pbuilder_activate');
        $templates = array();
		    //$this->upgrade_page_content();
		      //$this->get_templates_list("install.xml", true);
        /* foreach($templates->template as $template){
          //echo $template->temp_url;
          $this->template_install($template->temp_url);
          } */
    }

    function remove_shortcodes($shortcodes = false) {
        if (is_array($shortcodes)) {
            foreach ($shortcodes as $sh) {
                if (array_key_exists($sh, $this->shortcodes)) {
                    unset($this->shortcodes[$sh]);
                }
            }
        } else if (is_string($shortcodes)) {
            unset($this->shortcodes[$shortcodes]);
        } else
            $this->shortcodes = array();
    }

    function add_new_groups($sh) {
        if (is_array($sh)) {
            $this->groups = array_merge($this->groups, $sh);
        }
    }

    function add_new_admin_controls($sh) {
        if (is_array($sh)) {
            $this->admin_controls = array_merge($this->admin_controls, $sh);
        }
    }

    function add_new_menu_controls($sh) {
        if (is_array($sh)) {
            $this->menu_controls = array_merge($this->menu_controls, $sh);
        }
    }

    function add_new_row_controls($sh) {
        if (is_array($sh)) {
            $this->row_controls = array_merge($this->row_controls, $sh);
        }
    }

    function add_new_shortcodes($sh) {
        if (is_array($sh)) {
            $this->shortcodes = array_merge($this->shortcodes, $sh);
        }
    }

    function add_new_rows($sh) {
        if (is_array($sh)) {
            $this->rows = array_merge($this->rows, $sh);
        }
    }

    function add_new_icons($sh) {
        if (is_array($sh)) {
            $this->icons = array_merge($this->icons, $sh);
        }
    }

    /* function add_new_shortcodes($sh) {
      if(is_array($sh)) {
      $this->shortcodes = array_merge($this->shortcodes, $sh);
      }
      } */

    function refresh_variables() {
        $nav_menus = json_encode(get_registered_nav_menus());
        do_action('pbuilder_groups', $this);
        $this->menu_controls = $this->get_menu_controls();
        $this->shortcodes = str_replace('"wp_nav_menu_list"', $nav_menus, $this->shortcodes);
    }

    function admin_head() {
        if (array_key_exists('post', $_GET)) {
            $builder = $this->database($_GET['post'], true);
            echo '
            <script type="text/javascript">
            var pbuilderSwitch="' . $builder->switch . '";
            var pbuilderEnabled = "' . $this->pbuilderEnabled() . '";
            </script>';
        }
    }

    function admin_footer() {
        //global $pagenow;
//        echo "pagenow".$pagenow;
    }

    function get_templates() {
        $search_path = IMSCPB_DIR . "/page-templates/";
        $files = (array) $this->scandir($search_path, 'php', 1);
        $page_templates = array();
        foreach ($files as $file => $full_path) {
            if (!preg_match('|Template Name:(.*)$|mi', file_get_contents($full_path), $header))
                continue;
            $page_templates[$file] = _cleanup_header_comment($header[1]);
        }
        return array_flip($page_templates);
    }

    function scandir($path, $extensions = null, $depth = 0, $relative_path = '') {
        if (!is_dir($path))
            return false;
        if ($extensions) {
            $extensions = (array) $extensions;
            $_extensions = implode('|', $extensions);
        }
        $relative_path = trailingslashit($relative_path);
        if ('/' == $relative_path)
            $relative_path = '';
        $results = scandir($path);
        $files = array();
        foreach ($results as $result) {
            if ('.' == $result[0])
                continue;
            if (is_dir($path . '/' . $result)) {
                if (!$depth || 'CVS' == $result)
                    continue;
                $found = self::scandir($path . '/' . $result, $extensions, $depth - 1, $relative_path . $result);
                $files = array_merge_recursive($files, $found);
            } elseif (!$extensions || preg_match('~\.(' . $_extensions . ')$~', $result)) {
                $files[$relative_path . $result] = $path . '/' . $result;
            }
        }
        return $files;
    }

    function admin_bar() {
        if (is_admin()) {
            $current_screen = get_current_screen();
            $post = get_post();
            if ('post' == $current_screen->base && 'add' != $current_screen->action && ( $post_type_object = get_post_type_object($post->post_type) ) && current_user_can('read_post', $post->ID) && ( $post_type_object->public ) && ( $post_type_object->show_in_admin_bar )) {
                $this->admin_bar_links($post->ID);
            } elseif ('edit-tags' == $current_screen->base && isset($tag) && is_object($tag) && ( $tax = get_taxonomy($tag->taxonomy) ) && $tax->public) {
                $this->admin_bar_links($post->ID);
            }
        } else {
            if (!is_super_admin() || !is_admin_bar_showing() || !is_singular())
                return;
            $current_object = get_queried_object();
            if (!empty($current_object) && !empty($current_object->post_type) && ( $post_type_object = get_post_type_object($current_object->post_type) ) && current_user_can($post_type_object->cap->edit_post, $current_object->ID)) {
                $this->admin_bar_links($current_object->ID);
                return;
            }
            if (!get_post_type()) {
                echo '';
                return;
            }
            global $post;
            $this->admin_bar_links($post->ID);
        }
    }

    function admin_bar_links($id) {
        global $wp_admin_bar;
        $sw = $this->ajax_check($id);
        if (isset($sw) && $sw == 'on') {
            $wp_admin_bar->add_menu(
                    array('id' => 'pbuilder_edit',
                        'href' => admin_url() . 'admin-ajax.php?action=pbuilder_edit&p=' . $id,
                        'title' => '<span class="pbuilder_edit_icon"></span>',
                        'meta' => array('title' => __('Edit In Frontend', 'profit-builder'),)
                    )
            );
            $wp_admin_bar->add_menu(array(
                'parent' => 'edit',
                'id' => 'pbclonepost',
                'title' => 'Clone Page',
                'href' => admin_url("admin-ajax.php?action=pbuilder_clone_post&s=publish&p=" . $id),
            ));
            $wp_admin_bar->add_menu(array(
                'parent' => 'edit',
                'id' => 'pbdraftpost',
                'title' => 'Create Draft',
                'href' => admin_url("admin-ajax.php?action=pbuilder_draft_post&s=draft&p=" . $id),
            ));
        } else {
            $wp_admin_bar->add_menu(
                    array('id' => 'pbuilder_edit',
                        'href' => admin_url() . 'admin-ajax.php?action=pbuilder_edit&p=' . $id . '&sw=on',
                        'title' => '<span class="pbuilder_edit_icon"></span>',
                        'meta' => array('title' => __('Activate ProfitBuilder', 'profit-builder'))
                    )
            );
        }
    }

    function global_admin_includes() {
        wp_enqueue_script('jquery');
        wp_enqueue_style('font-awesome', $this->url . 'font/fawesome/font-awesome.css', array(), '4.2.0');

        wp_enqueue_style('pbuilder_admin_global', $this->url . 'css/admin_global.css', array(), '223');
        wp_enqueue_script('pbuilder_admin_global', $this->url . 'js/admin_global.js', array('jquery'), 1.0, true);
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        wp_enqueue_script('jquery-ui');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-timepicker-addon', $this->url . 'js/jquery-ui-timepicker-addon.js', array('jquery'), '1.0', true);

        wp_enqueue_script('color-box-min', plugin_dir_url(__FILE__) . 'js/jquery.colorbox-min.js');
        wp_enqueue_script('ouibounce', plugin_dir_url(__FILE__) . 'js/ouibounce.min.js');
        wp_enqueue_style('color-box-css', plugin_dir_url(__FILE__) . 'css/colorbox.css');

    }

    function admin_init() {
        //add_action('admin_notices', array(&$this, 'admin_update_notice'));
        add_action('admin_notices', array(&$this, 'check_folder_name'));
    }

    function check_folder_name() {
      if ('profit_builder' != basename(__DIR__)) {
        echo '<div class="error"><p><b>ProfitBuilder</b> is NOT installed properly and will not function as expected! Please only run it from the default folder - <i>profit_builder</i>. Currently, it is installed and run from <i>' . basename(__DIR__) . '</i> folder.</p></div>';
      }
    }

    function admin_menu() {
        global $imscpb_lc;
        $menu = add_menu_page('ProfitBuilder', 'ProfitBuilder', 'manage_options', 'profitbuilder', array(&$this, 'admin_page'), IMSCPB_URL . '/images/logoicon.png',4);
        if($imscpb_lc->GetLevelNo() == 2){
          $menu_funnels = add_submenu_page('profitbuilder','Funnels', 'Funnels', 'manage_options', 'profitbuilder_funnels', array(&$this, 'admin_funnels'));
          add_action('load-' . $menu_funnels, array(&$this, 'admin_menu_includes'));
        }

        $menu_templates = add_submenu_page('profitbuilder','Templates', 'Templates', 'manage_options', 'profitbuilder_templates', array(&$this, 'admin_templates'));
        add_submenu_page('profitbuilder','Settings', 'Settings', 'manage_options', 'profitbuilder', array(&$this, 'admin_page'));
        remove_submenu_page('profitbuilder','profitbuilder');

        add_action('load-' . $menu, array(&$this, 'admin_menu_includes'));
        add_action('load-' . $menu_templates, array(&$this, 'admin_menu_includes'));
    }

//add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' )


    function admin_menu_includes() {
        /* general includes */
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-color');
        wp_enqueue_script('jquery-ui');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-button');
        wp_enqueue_script('jquery-ui-progressbar');
        wp_enqueue_script('jquery-ui-datepicker');
        /* image includes */
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        /* custom scrollbar includes */
        wp_enqueue_script('pbuilder_mousewheel_js', $this->url . 'js/jquery.mousewheel.min.js');
        wp_enqueue_script('pbuilder_mCustomScrollbar_js', $this->url . 'js/jquery.mCustomScrollbar.min.js');
        wp_enqueue_style('pbuilder_mCustomScrollbar_css', $this->url . 'css/jquery.mCustomScrollbar.css');
        /* colorpicker includes */
        wp_enqueue_script('pbuilder_spectrum', $this->url . 'js/spectrum.js', array(), 1.0, true);
        wp_enqueue_style('pbuilder_spectrum_css', $this->url . 'css/spectrum.css');
        /* interface */
        wp_enqueue_style('pbuilder_admin_page_css', $this->url . 'css/admin_page.css', array(), '2.0.4');
        wp_enqueue_script('pbuilder_admin_page_js', $this->url . 'js/admin_page.js', array(), '2.0.4');
        //wp_enqueue_style('pbuilder_admin_page_css', $this->url . 'css/admin_page.css');
        //wp_enqueue_script('pbuilder_admin_page_js', $this->url . 'js/admin_page.js');
        /* plupload */
        wp_enqueue_style('pbuilder_plupload_ui_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/ui-darkness/jquery-ui.css');
        wp_enqueue_style('pbuilder_plupload_css', $this->url . 'js/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css');
        wp_enqueue_script('pbuilder_plupload_js', $this->url . 'js/plupload/plupload.full.min.js', array('jquery'), '1.0', true);
        wp_enqueue_script('pbuilder_plupload_ui_js', $this->url . 'js/plupload/jquery.ui.plupload/jquery.ui.plupload.js', array('jquery'), '1.0', true);
        //wp_enqueue_script('jquery-ui-timepicker-addon', $this->url . 'js/jquery-ui-timepicker-addon.js', array('jquery'),'1.0', true);
        /*
         * Code added by Asim Ashraf - DevBatch
         * DateTime: 28 Jan 2015
         * Edit Start
         */
        ///wp_enqueue_style('validationEngine', IMSCPB_URL . '/css/validationEngine.jquery.css', array());
        //wp_enqueue_script('validationEngine-en', IMSCPB_URL . '/js/jquery.validationEngine-en.js', array('jquery'), '0.1', true);
        wp_enqueue_script('validate', $this->url . '/js/form_validate.js');
        /*
         * Code added by Asim Ashraf - DevBatch
         * DateTime: 28 Jan 2015
         * Edit End
         */
    }

    function frontend_includes() {
        wp_enqueue_script('jquery');
        /* general includes */
        //wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-color');
        wp_enqueue_script('jquery-ui');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-sortable');
        //wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-datepicker');
        @wp_deregister_script('jquery-ui-draggable');
        wp_register_script('jquery-ui-draggable', $this->url . 'js/jquery.ui.draggable.min.js', array('jquery-ui-mouse'), false, 1);
        @wp_deregister_script('jquery-ui-mouse');
        wp_register_script('jquery-ui-mouse', $this->url . 'js/jquery.ui.mouse.min.js', array('jquery-ui-core', 'jquery-ui-widget'), false, 1);
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-mouse');
        //wp_enqueue_script('jquery-ui-draggable', $this->url . 'js/jquery.ui.draggable.min.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse'));
        /* interface includes */
        wp_enqueue_style('pbuilder_font-awesome_css', $this->url . 'css/font-awesome.css', array(), '4.2.0');
        wp_enqueue_style('pbuilder_fornt_css', $this->url . 'css/front.css', array(), '1.0.1');
        /* font includes */
        wp_enqueue_style('font-awesome', $this->url . 'font/fawesome/font-awesome.css', array(), '4.2.0');
        wp_enqueue_style('font-frb_awesome', $this->url . 'font/frb_fawesome/font-awesome.css', array(), '4.2.0');
        wp_enqueue_style('font-creative', $this->url . 'font/alternative/styles.css');
        wp_enqueue_style('font-alternative', $this->url . 'font/creative/styles.css');
        wp_enqueue_style('font-new_creative', $this->url . 'font/alternative_new/style.css');
        wp_enqueue_style('font-new_alternative', $this->url . 'font/creative_new/style.css');
        /* required includes */
        wp_enqueue_style('pbuilder_prettyphoto_css', $this->url . 'css/jquery.prettyphoto.css');
        wp_enqueue_script('pbuilder_prettyphoto_js', $this->url . 'js/jquery.prettyphoto.js', array('jquery'), '3.1.5', true);
        wp_enqueue_style('pbuilder_swiper_css', $this->url . 'css/idangerous.swiper.css');
        wp_enqueue_script('pbuilder_swiper_js', $this->url . 'js/idangerous.swiper.js', array('jquery'), '2.5', true);
        /* YTPlayer includes */
        wp_enqueue_style('pbuilder_YTPlayer_css', $this->url . 'css/mb.YTVPlayer.css');
        wp_enqueue_script('pbuilder_swfobject_js', $this->url . 'js/swfobject.js', array('jquery'), '2.2.0', true);
        wp_enqueue_script('pbuilder_YTPlayer_meta_js', $this->url . 'js/jquery.metadata.js', array('jquery'), '1.4.0', true);
        wp_enqueue_script('pbuilder_YTPlayer_js', $this->url . 'js/jquery.mb.YTPlayer.js', array('jquery'), '1.4.0', true);
        /* Timer Includes */
        wp_enqueue_style('pbuilder_flipclock_css', $this->url . 'css/timer2.css');
        wp_enqueue_script('pbuilder_flipclock_js', $this->url . 'js/timer.min.js', array('jquery'), '0.5.5', true);

        wp_enqueue_style('pbuilder_soon_css', $this->url . 'css/soon.min.css');
        wp_enqueue_script('pbuilder_soon_js', $this->url . 'js/soon.min.js', array('jquery'), '0.5.5', true);

        wp_enqueue_style('pbuilder_mb_comingsoon_css', $this->url . 'css/mb-comingsoon.css');
        //wp_enqueue_style('pbuilder_mb_comingsoon_iceberg_css', $this->url . 'css/mb-comingsoon-iceberg.css');
        wp_enqueue_script('pbuilder_mb_comingsoon_js', $this->url . 'js/jquery.mb-comingsoon.js', array('jquery'), '0.5.5', true);
        /* shrotcode includes */
        wp_enqueue_style('pbuilder_animate_css', $this->url . 'css/animate.css');
        wp_enqueue_style('pbuilder_contact_ui_css', $this->url . 'css/contact-ui.css');
        wp_enqueue_style('pbuilder_shortcode_css', $this->url . 'css/shortcodes.css',  array(), '230');
        wp_enqueue_script('pbuilder_typed_js', $this->url . 'js/typed.min.js', array('jquery'), '1.0.1', true);
        
        
        wp_enqueue_script('pbuilder_shortcode_js', $this->url . 'js/shortcodes.js', array('jquery', 'pbuilder_swiper_js', 'pbuilder_prettyphoto_js'), '1.0.1', true);
        wp_localize_script('pbuilder_shortcode_js', 'ajaxurl', admin_url('admin-ajax.php')); /* 	defining ajaxurl in frontend		 */
        /* charts */
        wp_enqueue_script('pbuilder_easypiechart_js', $this->url . 'js/jquery.easypiechart.min.js', array('jquery'), '2.1.3', true);
        wp_enqueue_script('pbuilder_chart_js', $this->url . 'js/chart.js', array('jquery'), '1.0', true);
        wp_enqueue_script('pbuilder_raphael_js', $this->url . 'js/raphael.2.1.0.min.js', array('jquery'), '2.1.0', true);
        wp_enqueue_script('pbuilder_justgage_js', $this->url . 'js/justgage.1.0.1.js', array('jquery'), '1.0.1', true);
        /* gallery	 */
        wp_enqueue_script('pbuilder_isotope_js', $this->url . 'js/isotope.pkgd.min.js', array('jquery'), '2.0.0', true);
        
        
        
        /* social	 */
        wp_enqueue_style('pbuilder_pbs_css', $this->url . 'css/pbs.css');
        //wp_enqueue_style('pbuilder_normalize_css', $this->url . 'css/normalize.min.css');
        wp_enqueue_script('pbuilder_pbs_js', $this->url . 'js/pbs.min.js', array('jquery'), '1.0.1', true);
        wp_enqueue_script('pbuilder_modernize_js', $this->url . 'js/modernizr-2.6.2-respond-1.1.0.min.js', array('jquery'), '2.6.2', true);
        /* Flowplayer */
        wp_enqueue_style('pbuilder_fp_css', $this->url . 'css/minimalist.css');
        wp_enqueue_script('pbuilder_fp_js', $this->url . 'js/flowplayer.min.js', array('jquery'), '2.5.4', true);
        wp_enqueue_script('pb-main-js', $this->url . '/js/pb_main.js', array('jquery'), '1.0.5', true);
        wp_enqueue_style('progressbar-css', $this->url . '/css/progressbar.css');

        //wp_enqueue_style('validationEngine', IMSCPB_URL . '/css/validationEngine.jquery.css', array());
        //wp_enqueue_script('validationEngine-en', IMSCPB_URL . '/js/jquery.validationEngine-en.js', array('jquery'), '0.1', true);
        wp_enqueue_script('validate', $this->url . '/js/form_validate.js', array('jquery'), '1.0.5');
        wp_register_style('GDwidgetStylesheet', $this->url . 'css/style.css');
        wp_enqueue_style('GDwidgetStylesheet');

        wp_enqueue_script('color-box-min', plugin_dir_url(__FILE__) . 'js/jquery.colorbox-min.js');
        wp_enqueue_script('ouibounce', plugin_dir_url(__FILE__) . 'js/ouibounce.min.js');
        wp_enqueue_style('color-box-css', plugin_dir_url(__FILE__) . 'css/colorbox.css');

    }

    function edit_page_includes() {
        wp_enqueue_script('color-box-min', plugin_dir_url(__FILE__) . 'js/jquery.colorbox-min.js');
        wp_enqueue_script('color-box', plugin_dir_url(__FILE__) . 'js/jquery.colorbox.js');
        wp_enqueue_style('color-box-css', plugin_dir_url(__FILE__) . 'css/colorbox.css');
        wp_enqueue_script(array('jquery', 'editor', 'thickbox', 'media-upload'));
        wp_enqueue_script('jquery-color');
        wp_enqueue_script('jquery-ui');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-button');
        wp_enqueue_script('jquery-ui-progressbar');
        /* admin css */
        wp_enqueue_style('colors');
        wp_enqueue_style('ie');
        wp_enqueue_script('utils');
        /* image includes */
        //	wp_enqueue_style('thickbox');
        //	wp_enqueue_script('thickbox');
        wp_enqueue_media();
        /* custom scrollbar includes */
        wp_enqueue_script('pbuilder_mousewheel_js', $this->url . 'js/jquery.mousewheel.min.js');
        wp_enqueue_script('pbuilder_mCustomScrollbar_js', $this->url . 'js/jquery.mCustomScrollbar.min.js');
        wp_enqueue_style('pbuilder_mCustomScrollbar_css', $this->url . 'css/jquery.mCustomScrollbar.css');
        /* colorpicker includes */
        wp_enqueue_script('pbuilder_spectrum', $this->url . 'js/spectrum.js', array(), 1.0, true);
        wp_enqueue_style('pbuilder_spectrum_css', $this->url . 'css/spectrum.css');
        /* interface includes */
        wp_enqueue_style('pbuilder_font-awesome_css', $this->url . 'css/font-awesome.css', array(), '4.2.0');
        wp_enqueue_style('pbuilder_fornt_css', $this->url . 'css/front.css',  array(), '230');
        wp_enqueue_script('pbuilder_front_js', $this->url . 'js/front.js', array('jquery'), '230', true);
        /* plupload */
        wp_enqueue_style('pbuilder_plupload_ui_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/ui-darkness/jquery-ui.css');
        wp_enqueue_style('pbuilder_plupload_css', $this->url . 'js/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css');
        wp_enqueue_script('pbuilder_plupload_js', $this->url . 'js/plupload/plupload.full.min.js', array('jquery'), '1.0', true);
        wp_enqueue_script('pbuilder_plupload_ui_js', $this->url . 'js/plupload/jquery.ui.plupload/jquery.ui.plupload.js', array('jquery'), '1.0', true);
        /* font includes */
        wp_enqueue_style('font-awesome', $this->url . 'font/fawesome/font-awesome.css', array(), '4.2.0');
        wp_enqueue_style('font-frb_awesome', $this->url . 'font/frb_fawesome/font-awesome.css', array(), '4.2.0');
        wp_enqueue_style('font-creative', $this->url . 'font/alternative/styles.css');
        wp_enqueue_style('font-alternative', $this->url . 'font/creative/styles.css');
        wp_enqueue_style('font-new_creative', $this->url . 'font/alternative_new/style.css');
        wp_enqueue_style('font-new_alternative', $this->url . 'font/creative_new/style.css');

        wp_register_style('GDwidgetStylesheet', $this->url . 'css/style.css');
        wp_enqueue_style('GDwidgetStylesheet');
    }

    function admin_print_styles() {
        $content_block_font_size = $this->options(" WHERE name = 'content_block_all_font_size'");
        $content_font_size = "13px";
        if (!empty($content_block_font_size[0]->value)) {
            $content_font_size = str_replace(" ", "", $content_block_font_size[0]->value);
        }
      		if (is_admin() && isset($_GET['action']) && $_GET['action'] == 'pbuilder_edit' && isset($_GET['p'])) {
      			echo '<base target="_parent" />';
      		}
        echo '<style> .pbuilder_textarea{ font-size:' . $content_font_size . ' !Important;} </style>';
        //wp_add_inline_style( 'inline-custom-style', $Style );
    }

	function front_print_styles(){
		echo '<base target="_parent" />';
	}

    function admin_page() {
        require_once($this->path . '/pages/admin_page.php');
    }

	 function admin_templates() {
        require_once($this->path . '/pages/admin_page_templates.php');
    }

    function admin_funnels() {
        require_once($this->path . '/pages/admin_funnels.php');
    }

    function get_admin_controls() {
        $output = array();
        require($this->path . '/functions/admin_control_list.php');
        $extensionData = $this->frb_get_extensions();
        if ($extensionData != false) {
            foreach ($extensionData as $extKey => $extData) {
                if (file_exists($extensionData[$extKey]['admin_controls_list_url'])) {
                    $extensionOutput = require($extensionData[$extKey]['admin_controls_list_url']);
                }
                if (is_array($extensionOutput)) {
                    foreach ($extensionOutput as $key => $section) {
                        if (array_key_exists($key, $output)) {
                            $output[$key]['options'] = array_merge($output[$key]['options'], $extensionOutput[$key]['options']);
                        } else {
                            $output[$key] = $extensionOutput[$key];
                        }
                    }
                }
            }
        }
        $optionsDB = $this->option();
        foreach ($output as $skey => $section) {
            $controls = $section['options'];
            if (is_array($controls)) {
                foreach ($controls as $ckey => $control) {
                    if ($control['type'] == 'collapsible') {
                        foreach ($control['options'] as $okey => $option) {
                            if (array_key_exists('name', $option)) {
                                $exists = false;
                                foreach ($optionsDB as $ind => $opt) {
                                    if (is_object($opt) && $opt->name == $option['name']) {
                                        $exists = true;
                                        $output[$skey]['options'][$ckey]['options'][$okey]['std'] = $optionsDB[$ind];
                                        unset($optionsDB[$ind]);
                                        break;
                                    }
                                }
                                if (!$exists && array_key_exists('std', $option)) {
                                    $this->option($option['name'], $option['std']);
                                }
                            }
                        }
                    } else {
                        if (array_key_exists('name', $control)) {
                            $exists = false;
                            foreach ($optionsDB as $ind => $opt) {
                                if (is_object($opt) && $opt->name == $control['name']) {
                                    $exists = true;
                                    if (array_key_exists($skey, $output) && array_key_exists('otpions', $output[$skey]) && array_key_exists($ckey, $output[$skey]['options']))
                                        $output[$skey]['options'][$ckey]['std'] = $optionsDB[$ind];
                                    unset($optionsDB[$ind]);
                                    break;
                                }
                            }
                            if (!$exists && array_key_exists('std', $control)) {
                                $this->option($control['name'], $control['std']);
                            }
                        }
                    }
                }
            }
        }
        return $output;
    }

    function frb_get_extensions() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'profit_builder_extensions';
        $extensions = $wpdb->get_results('SELECT name,admin_controls_list_url,font_head_url FROM ' . $table_name, ARRAY_A);
        if (count($extensions) <= 0) {
            return false;
        } else {
            return $extensions;
        }
    }

    function get_admin_hideifs($options) {
        $hideifs = array();
        foreach ($options as $control) {
            if ($control['type'] == 'collapsible') {
                foreach ($control['options'] as $option) {
                    if (array_key_exists('hide_if', $option)) {
                        $hideifs[$option['name']] = $option['hide_if'];
                    }
                }
            } else {
                if (array_key_exists('hide_if', $control)) {
                    $hideifs[$control['name']] = $control['hide_if'];
                }
            }
        }
        return $hideifs;
    }

    function get_admin_control($arr) {
        global $builder_icons;
        $pbuilder_icons = $this->icons;
        require_once($this->path . '/functions/admin_controls.php');
        $ctrl = new pbuilderControl($arr);
        return $ctrl->html;
    }

    function get_menu_controls() {
        $output = '{}';
        require($this->path . '/functions/menu_controls.php');
        return $output;
    }

    function extract_row_controls($row) {
        $output = array('row' => array(), 'column' => array());
        $id = '';
        $class = '';
        $style = '';
        $colstyle = '';
        $rowback = '';
        $rowbackimage = '';
        $rowbackvideo = '';
        $rowbackrep = '';
		      $rowbacksize = 'default';
        $rowbackpos = '';
        $rowbackcolor = '';
        $rowborder = '';
        $shadow_h_shadow = '';
        $shadow_v_shadow = '';
        $shadow_blur = '';
        $shadow_color = '';
        $back_type = 'static';
        $back_color = '';
        $back_color2 = '';
        $gradient_type = 'linear';
        $timed_row = 'false';
        $timed_row_min = 0;
        $timed_row_sec = 0;
        $margin_padding = '0|0|0|0|10|20|10|20';
        $back_image_zoom = 'false';

        if (isset($row['options']) && isset($row['options']['border_round'])) $border_roundness = $row['options']['border_round'];
        if (isset($row['options']))
            foreach ($row['options'] as $key => $option) {
                switch ($key) {
                    case 'id':
                        $id = $option;
                        break;
                    case 'class':
                        $class .= $option . ' ';
                        break;
                    case 'full_width' :
                        if ($option == 'true')
                            $class .= 'pbuilder_row_full_width ';
                        break;
                    case 'timed_row' :
                        $timed_row = $option;
                        break;
                    case 'timed_row_min' :
                        $timed_row_min = $option;
                        break;
                    case 'timed_row_sec' :
                        $timed_row_sec = $option;
                        break;
                    case 'row_style' :
                        if ($option == 'normal') {

                        } else if ($option == 'sticktop') {
                            // echo "<div class='stick-top-div'></div>";
                            $class .= ' pbuilder_row_stick_top ';
                        } else if ($option == 'stickbottom') {
                            $class .= ' pbuilder_row_stick_bottom ';
                        }
                        break;
                    
                    case 'shadow_h_shadow':
                        $shadow_h_shadow = $option;
                        break;
                    case 'shadow_v_shadow':
                        $shadow_v_shadow = $option;
                        break;
                    case 'shadow_blur':
                        $shadow_blur = $option;
                        break;
                    case 'shadow_color':
                        $shadow_color = $option;
                        //$row.css('box-shadow','1px 1px 1px '+options[x]);
                        break;
                    case 'back_type' :

                        $back_type = $option;

                        if ($option == 'parallax' || $option == 'video_fixed') {
                            $rowbackpos = 'fixed';
                        }
                        if ($option == 'parallax_animated' || $option == 'video_parallax') {
                            $rowbackpos = 'parallax';
                        }
                        if ($option == 'video' || $option == 'video_fixed' || $option == 'video_parallax') {
                            if (isset($row['options']['back_video_source'])) {
                                $rowbackvideo = $row['options']['back_video_source'];
                            } else {
                                $rowbackvideo = 'youtube';
                            }
                        }
                        break;
                    /* case 'back_color' :
                      if($option != '')
                      $rowbackcolor = 'background-color:'.$option.';';
                      break; */


                    case 'back_color' :
                        if ($option != '')
                            $back_color = $option;
                        break;
                    case 'back_color2' :
                        if ($option != '')
                            $back_color2 = $option;
                        break;
                    case 'gradient_type' :
                        $gradient_type = $option != '' ? $option : 'linear';
                        break;
                    case 'back_image' :
                        if ($option != '')
                            $rowbackimage = 'background-image:url(' . $option . ');';
                        break;
					         case 'back_image_zoom' :
                        if ($option != 'false'){
                            $back_image_zoom = ' pbuilder-background-image-zoom ';
						} else {
							$back_image_zoom = '';
						}
                        break;
                    case 'back_repeat' :
                        if ($option == 'repeat') {
                            $rowbackrep = 'background-repeat:repeat;';
                        } elseif ($option == 'repeatx') {
                            $rowbackrep = 'background-repeat:repeat-x;background-position:center top; ';
                        } elseif ($option == 'stretched') {
                            $rowbackrep = '  -webkit-background-size: cover;  -moz-background-size: cover;  -o-background-size: cover;  background-size: cover;background-position:center top;';
                        }
                        break;


                }
            }

		if ($back_image_zoom == 'false'){
			$back_image_zoom='';
		}

		$margin_padding=explode('|',@$row['options']['margin_padding']);
		$margin_top=(int)@$margin_padding[0].'px';
		$margin_right=(int)@$margin_padding[1].'px';
		$margin_bottom=(int)@$margin_padding[2].'px';
		$margin_left=(int)@$margin_padding[3].'px';

		$padding_top=(int)@$margin_padding[4].'px';
		$padding_right=(int)@$margin_padding[5].'px';
		$padding_bottom=(int)@$margin_padding[6].'px';
		$padding_left=(int)@$margin_padding[7].'px';

		$margin_padding_css = '';
		if(@$margin_padding[0]>0) $margin_padding_css .= 'margin-top:'.$margin_top.';';
		if(@$margin_padding[1]>0) $margin_padding_css .= 'margin-right:'.$margin_right.';';
		if(@$margin_padding[2]>0) $margin_padding_css .= 'margin-bottom:'.$margin_bottom.';';
		if(@$margin_padding[3]>0) $margin_padding_css .= 'margin-left:'.$margin_left.';';

		$margin_padding_css .= 'padding:'.$padding_top.' '.$padding_right.' '.$padding_bottom.' '.$padding_left.';';

    
    if( !isset($row['options']['border']) && isset($row['options']['border_width']) && isset($row['options']['border_width']) && isset($row['options']['border_width'])) {
        $row['options']['border'] = 'false|'.(int)$row['options']['border_width'].'px|'.$row['options']['border_style'].'|'.$row['options']['border_color'].'|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';    
    }
    
    if(isset($row['options']['border'])){
      $border_properties=explode('|',$row['options']['border']);      
      
      $horizantal_border=0;
      $vertical_border=0;
      
      $border_css='';
      if($border_properties[0]!='true'){
        if((int)$border_properties[1]>0){
          $border_css.='border:'.(int)$border_properties[1].'px '.$border_properties[2].' '.$border_properties[3].';';
          $horizantal_border=$horizantal_border=(int)$border_properties[1];
        }
      } else {
        if((int)$border_properties[4]>0){
          $border_css.='border-top:'.(int)$border_properties[4].'px '.$border_properties[5].' '.$border_properties[6].';';
          $vertical_border=(int)$border_properties[4];
        }
        if((int)$border_properties[7]>0){
          $border_css.='border-right:'.(int)$border_properties[7].'px '.$border_properties[8].' '.$border_properties[9].';';
        }
        if((int)$border_properties[10]>0){
          $border_css.='border-bottom:'.(int)$border_properties[10].'px '.$border_properties[11].' '.$border_properties[12].';';
        }
        if((int)$border_properties[13]>0){
          $border_css.='border-left:'.(int)$border_properties[13].'px '.$border_properties[14].' '.$border_properties[15].';';
          $horizantal_border=(int)$border_properties[13];
        }
      }
      
      if(strlen($border_css)==0){
        $border_css='border:none;';
      } else {
        if((int)$border_roundness>0){
           $border_css.='border-radius:'.(int)$border_roundness.'px;'; 
        }
        $border_css.='margin-top:-'.$vertical_border.'px;';  
      }
      
      $style.=$border_css;
    } 
    
    
		if(strlen($shadow_h_shadow)>0 || strlen($shadow_v_shadow)>0 || strlen($shadow_blur)>0 || strlen($shadow_color)>0){
        	$style .= ' box-shadow: ' . $shadow_h_shadow . ' ' . $shadow_v_shadow . ' ' . $shadow_blur . ' ' . $shadow_color . '; ';
		}

        if ($back_type == 'static' && $back_color2 != "") {
            //if($back_color2 == "") $back_color2 = $back_color;
            $rowbackcolor .= ' background: -webkit-' . $gradient_type . '-gradient(' . $back_color . ', ' . $back_color2 . '); ';
            $rowbackcolor .= ' background: -o-' . $gradient_type . '-gradient(' . $back_color . ', ' . $back_color2 . '); ';
            $rowbackcolor .= ' background: -moz-' . $gradient_type . '-gradient(' . $back_color . ', ' . $back_color2 . '); ';
            $rowbackcolor .= ' background: ' . $gradient_type . '-gradient(' . $back_color . ', ' . $back_color2 . '); ';
        } else {
            if(strlen($back_color)>0){
				$rowbackcolor .= ' background-color: ' . $back_color . '; ';
			}
        }
        if ($rowbackvideo != '') {
            $loop = (!isset($row['options']['back_video_loop']) || $row['options']['back_video_loop'] != 'false');
            if ($rowbackvideo == 'youtube') {
                $randId = 'yt' . rand();
                $output['row']['back'] = '<div class="pbuilder_row_video pbuilder_row_background' . ($rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') . ($rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') . '">' .
                        (isset($row['options']['back_video_youtube_id']) ?
                                '<div id="' . $randId . '" class="YTPlayer" style="display:block; margin: auto; background: rgba(0,0,0,0.5)" data-property="{videoURL:\'http://youtu.be/' . $row['options']['back_video_youtube_id'] . '\',containment:\'self\',startAt:1,mute:true,autoPlay:true' . ($loop ? ',loop:true' : ',loop:false') . ',opacity:1,showControls:true,quality:\'hd720\'}"></div>' : '') .
                        '</div>';
            } else if ($rowbackvideo == 'vimeo') {
                $output['row']['back'] = '<div class="pbuilder_row_video pbuilder_row_video_vimeo pbuilder_row_background' . ($rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') . ($rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') . '">
				<iframe src="//player.vimeo.com/video/' . $row['options']['back_video_vimeo_id'] . '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;autoplay=1' . ($loop ? '&amp;loop=1' : '') . '" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
				</div>';
            } else {
                $output['row']['back'] = '<div class="pbuilder_row_video pbuilder_row_video_html5 pbuilder_row_background' . ($rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') . ($rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') . '">
				<video muted autoplay' . ($loop ? ' loop' : '') .
                        (isset($row['options']['back_video_html5_img']) && $row['options']['back_video_html5_img'] != '' ? ' poster="' . $row['options']['back_video_html5_img'] . '"' : '') . ' >
  				' . (isset($row['options']['back_video_html5_mp4']) && $row['options']['back_video_html5_mp4'] != '' ? '<source src="' . $row['options']['back_video_html5_mp4'] . '" type="video/mp4">' : '') . '
				' . (isset($row['options']['back_video_html5_webm']) && $row['options']['back_video_html5_webm'] != '' ? '<source src="' . $row['options']['back_video_html5_webm'] . '" type="video/webm">' : '') . '
				' . (isset($row['options']['back_video_html5_ogv']) && $row['options']['back_video_html5_ogv'] != '' ? '<source src="' . $row['options']['back_video_html5_ogv'] . '" type="video/ogg">' : '') . '
				</video>
				<script>
					(function($){
						$(document).load(function(){
							$("video")[0].play();
						});
					})(jQuery);
				</script>
				</div>';
            }
        } else if ($rowbackimage != '') {
            $output['row']['back'] = '<div class="pbuilder_row_background ' . $back_image_zoom . ($rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') . ($rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') . '"><div class="pbuilder_row_back_image" style="' . $rowbackimage . $rowbackcolor . $rowbackrep . $rowborder . '"></div></div>';
        } else if ($rowbackcolor) {
            $output['row']['back'] = '<div class="pbuilder_row_background" style="' . $rowbackcolor . $rowborder . '"></div>';
        } else {
            $output['row']['back'] = '';
        }
        if ($timed_row == "true") {
            $timed_row_min = $timed_row_min * 60 * 1000;
            $timed_row_sec = $timed_row_sec * 1000;
            $duration = $timed_row_min + $timed_row_sec;
            $class .= ' timed_row timed-row-' . $duration . ' ';
            //$output['row']['back'] .=
            ?> <!--<script type="text/javascript">jQuery(document).delay(duration).fadeIn();</script> -->
            <?php
            //$row.delay(duration).fadeIn();
        }
        $output['row']['back_full'] = $row['options']['back_full_width'];
        $output['row']['class'] = $class;
        $output['row']['style'] = $style;
		    $output['row']['margin_padding'] = $margin_padding_css;
        $output['row']['id'] = $id;
        return $output;
    }



	function extract_column_controls($column) {

        $output = array('column' => array());
        $id = '';
        $class = '';
        $style = '';
        $colstyle = '';
        $columnback = '';
        $columnbackimage = '';
        $columnbackvideo = '';
        $columnbackrep = '';
        $columnbackpos = '';
        $columnbackcolor = '';
        $columnborder = '';
        $shadow_h_shadow = '';
        $shadow_v_shadow = '';
        $shadow_blur = '';
        $shadow_color = '';
        $back_type = 'static';
        $back_color = '';
        $back_color2 = '';
        $gradient_type = 'linear';
        $timed_row = 'false';
        $timed_row_min = 0;
        $timed_row_sec = 0;
        
        if (isset($column['options']) && isset($column['options']['border_round'])) $border_roundness = $column['options']['border_round'];
        if (isset($column['options']))
            foreach ($column['options'] as $key => $option) {
                switch ($key) {
                    case 'id':
                        $id = $option;
                        break;
                    case 'class':
                        $class .= $option . ' ';
                        break;
                    case 'padding_top':
                        $style .= 'padding-top:' . ((int) $option) . 'px;';
                        break;
                    case 'padding_bot':
                        $style .= 'padding-bottom:' . ((int) $option) . 'px;';
                        break;
                    case 'full_width' :
                        if ($option == 'true')
                            $class .= 'pbuilder_row_full_width ';
                        break;
                    case 'timed_row' :
                        $timed_row = $option;
                        break;
                    case 'timed_row_min' :
                        $timed_row_min = $option;
                        break;
                    case 'timed_row_sec' :
                        $timed_row_sec = $option;
                        break;
                    case 'row_style' :
                        if ($option == 'normal') {

                        } else if ($option == 'sticktop') {
                            // echo "<div class='stick-top-div'></div>";
                            $class .= ' pbuilder_row_stick_top ';
                        } else if ($option == 'stickbottom') {
                            $class .= ' pbuilder_row_stick_bottom ';
                        }
                        break;
                    case 'border_color':
                        $border_style .= ' border-color:' . $option . "; ";
                        break;
                    case 'border_width':
                        $border_style .= ' border-width: ' . ((int) $option) . 'px; margin-left:-' . ((int) $option) . 'px; margin-top:-' . ((int) $option) . 'px;';
                        break;
                    case 'border_style':
                        $border_style .= ' border-style: ' . $option . '; ';
                        break;
                    case 'border_round':
                        $border_style .= ' border-radius: ' . $border_roundness . '; ';
                        break;
                    case 'shadow_h_shadow':
                        $shadow_h_shadow = $option;
                        break;
                    case 'shadow_v_shadow':
                        $shadow_v_shadow = $option;
                        break;
                    case 'shadow_blur':
                        $shadow_blur = $option;
                        break;
                    case 'shadow_color':
                        $shadow_color = $option;
                        //$column.css('box-shadow','1px 1px 1px '+options[x]);
                        break;
                    case 'back_type' :

                        $back_type = $option;

                        if ($option == 'parallax' || $option == 'video_fixed') {
                            $columnbackpos = 'fixed';
                        }
                        if ($option == 'parallax_animated' || $option == 'video_parallax') {
                            $columnbackpos = 'parallax';
                        }
                        if ($option == 'video' || $option == 'video_fixed' || $option == 'video_parallax') {
                            if (isset($column['options']['back_video_source'])) {
                                $columnbackvideo = $column['options']['back_video_source'];
                            } else {
                                $columnbackvideo = 'youtube';
                            }
                        }
                        break;
                    /* case 'back_color' :
                      if($option != '')
                      $columnbackcolor = 'background-color:'.$option.';';
                      break; */


                    case 'back_color' :
                        if ($option != '')
                            $back_color = $option;
                        break;
                    case 'back_color2' :
                        if ($option != '')
                            $back_color2 = $option;
                        break;
                    case 'gradient_type' :
                        $gradient_type = $option != '' ? $option : 'linear';
                        break;
                    case 'back_image' :
                        if ($option != '')
                            $columnbackimage = 'background-image:url(' . $option . ');';
                        break;
					          case 'back_image_zoom' :
                        if ($option != 'false')
                            $back_image_zoom = ' pbuilder-background-image-zoom ';
                        break;
					          case 'back_opacity' :
                        $back_opacity = $column['options']['back_opacity'];
                        break;
                    case 'back_repeat' :
                        if ($option == 'repeat') {
                            $columnbackrep = 'background-repeat:repeat;';
                        } elseif ($option == 'repeatx') {
                            $columnbackrep = 'background-repeat:repeat-x;background-position:center top; ';
                        } elseif ($option == 'stretched') {
                            $columnbackrep = '  -webkit-background-size: cover;  -moz-background-size: cover;  -o-background-size: cover;  background-size: cover;background-position:center top;';
                        }
                        break;
                    case 'margin_padding' :
              						$margin_padding=explode('|',$option);
              						$margin_top=(int)$margin_padding[0].'px';
              						$margin_right=(int)$margin_padding[1].'px';
              						$margin_bottom=(int)$margin_padding[2].'px';
              						$margin_left=(int)$margin_padding[3].'px';

              						$padding_top=(int)$margin_padding[4].'px';
              						$padding_right=(int)$margin_padding[5].'px';
              						$padding_bottom=(int)$margin_padding[6].'px';
              						$padding_left=(int)$margin_padding[7].'px';

              					  $margin_padding = '';
              					  if(@$margin_padding[0]>0) $margin_padding .= 'margin-top:'.$margin_top.';';
              						if(@$margin_padding[1]>0) $margin_padding .= 'margin-right:'.$margin_right.';';
              						if(@$margin_padding[2]>0) $margin_padding .= 'margin-bottom:'.$margin_bottom.';';
              						if(@$margin_padding[3]>0) $margin_padding .= 'margin-left:'.$margin_left.';';

              						$margin_padding .= 'padding:'.$padding_top.' '.$padding_right.' '.$padding_bottom.' '.$padding_left.';';

                        break;

                }
            }
          
          if(isset($column['options']['border'])){
            $border_properties=explode('|',$column['options']['border']);
            
            
            $horizantal_border=0;
            $vertical_border=0;
            
            $border_css='';
            if($border_properties[0]!='true'){
              if((int)$border_properties[1]>0){
                $border_css.='border:'.(int)$border_properties[1].'px '.$border_properties[2].' '.$border_properties[3].';';
                $horizantal_border=$vertical_border=(int)$border_properties[1];
              }
            } else {
              if((int)$border_properties[4]>0){
                $border_css.='border-top:'.(int)$border_properties[4].'px '.$border_properties[5].' '.$border_properties[6].';';
                $vertical_border=(int)$border_properties[4];
              }
              if((int)$border_properties[7]>0){
                $border_css.='border-right:'.(int)$border_properties[7].'px '.$border_properties[8].' '.$border_properties[9].';';
              }
              if((int)$border_properties[10]>0){
                $border_css.='border-bottom:'.(int)$border_properties[10].'px '.$border_properties[11].' '.$border_properties[12].';';
              }
              if((int)$border_properties[13]>0){
                $border_css.='border-left:'.(int)$border_properties[13].'px '.$border_properties[14].' '.$border_properties[15].';';
                $horizantal_border=(int)$border_properties[13];
              }
            }
            
            if(strlen($border_css)==0){
              $border_css='border:none;';
            } else {
              if((int)$border_roundness>0){
                 $border_css.='border-radius:'.(int)$border_roundness.'px;'; 
              }
              $border_css.='margin-left:-'.$horizantal_border.'px; margin-top:-'.$vertical_border.'px;';  
            }
            
            $border_style=$border_css;
          }
          
          
      		if(strlen($shadow_h_shadow)>0 || strlen($shadow_v_shadow)>0 || strlen($shadow_blur)>0 || strlen($shadow_color)>0){
              	$style .= ' box-shadow: ' . $shadow_h_shadow . ' ' . $shadow_v_shadow . ' ' . $shadow_blur . ' ' . $shadow_color . '; ';
      		}

          if ($back_type == 'static' && $back_color2 != "") {            
              $columnbackcolor .= ' background: -webkit-' . $gradient_type . '-gradient(' . $back_color . ', ' . $back_color2 . '); ';
              $columnbackcolor .= ' background: -o-' . $gradient_type . '-gradient(' . $back_color . ', ' . $back_color2 . '); ';
              $columnbackcolor .= ' background: -moz-' . $gradient_type . '-gradient(' . $back_color . ', ' . $back_color2 . '); ';
              $columnbackcolor .= ' background: ' . $gradient_type . '-gradient(' . $back_color . ', ' . $back_color2 . '); ';
          } else {
              $columnbackcolor .= ' background-color: ' . $back_color . '; ';
          }
          if ($columnbackvideo != '') {
              $loop = (!isset($column['options']['back_video_loop']) || $column['options']['back_video_loop'] != 'false');
              if ($columnbackvideo == 'youtube') {
                  $randId = 'yt' . rand();
                  $output['column']['back'] = '<div class="pbuilder_row_video style="'.$border_style.'" pbuilder_column_background' . ($columnbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') . ($columnbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') . '">' .
                          (isset($column['options']['back_video_youtube_id']) ?
                                  '<div id="' . $randId . '" class="YTPlayer" style="display:block; margin: auto; background: rgba(0,0,0,0.5)" data-property="{videoURL:\'http://youtu.be/' . $column['options']['back_video_youtube_id'] . '\',containment:\'self\',startAt:1,mute:true,autoPlay:true' . ($loop ? ',loop:true' : ',loop:false') . ',opacity:1,showControls:true,quality:\'hd720\'}"></div>' : '') .
                          '</div>';
              } else if ($columnbackvideo == 'vimeo') {
                  $output['column']['back'] = '<div  style="'.$border_style.'" class="pbuilder_row_video pbuilder_row_video_vimeo pbuilder_column_background' . ($columnbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') . ($columnbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') . '">
          <iframe src="//player.vimeo.com/video/' . $column['options']['back_video_vimeo_id'] . '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;autoplay=1' . ($loop ? '&amp;loop=1' : '') . '" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
          </div>';
              } else {
                  $output['column']['back'] = '<div  style="'.$border_style.'" class="pbuilder_row_video pbuilder_row_video_html5 pbuilder_column_background' . ($columnbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') . ($columnbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') . '">
          <video muted autoplay' . ($loop ? ' loop' : '') .
                          (isset($column['options']['back_video_html5_img']) && $column['options']['back_video_html5_img'] != '' ? ' poster="' . $column['options']['back_video_html5_img'] . '"' : '') . ' >
            ' . (isset($column['options']['back_video_html5_mp4']) && $column['options']['back_video_html5_mp4'] != '' ? '<source src="' . $column['options']['back_video_html5_mp4'] . '" type="video/mp4">' : '') . '
          ' . (isset($column['options']['back_video_html5_webm']) && $column['options']['back_video_html5_webm'] != '' ? '<source src="' . $column['options']['back_video_html5_webm'] . '" type="video/webm">' : '') . '
          ' . (isset($column['options']['back_video_html5_ogv']) && $column['options']['back_video_html5_ogv'] != '' ? '<source src="' . $column['options']['back_video_html5_ogv'] . '" type="video/ogg">' : '') . '
          </video>
          <script>
            (function($){
              $(document).load(function(){
                $("video")[0].play();
              });
            })(jQuery);
          </script>
          </div>';
              }
          } else if ($columnbackimage != '') {
              $output['column']['back'] = '<div style="'.$border_style .'" class="pbuilder_column_background' . $back_image_zoom . ($columnbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') . ($columnbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') . '"><div class="pbuilder_row_back_image" style="'. $columnbackimage . $columnbackcolor . $columnbackrep . $columnborder . '"></div></div>';
          } else if ($columnbackcolor) {
              $output['column']['back'] = '<div class="pbuilder_column_background" style="'.@$border_style . $columnbackcolor . $columnborder . '"></div>';
          } else {
              $output['column']['back'] = '<div class="pbuilder_column_background" style="'.@$border_style.'"></div>';
          }
          if ($timed_row == "true") {
              $timed_row_min = $timed_row_min * 60 * 1000;
              $timed_row_sec = $timed_row_sec * 1000;
              $duration = $timed_row_min + $timed_row_sec;
              $class .= ' timed_row timed-row-' . $duration . ' ';
          }
          $output['column']['class'] = $class;
          $output['column']['style'] = $style.@$margin_padding;
          $output['column']['id'] = $id;
  
          return $output;
    }

    function get_row_controls() {
        $output = '{}';
        require_once($this->path . '/functions/row_control_list.php');
        return $output;
    }

    function get_column_controls() {
        $output = '{}';
        require_once($this->path . '/functions/column_control_list.php');
        return $output;
    }

    function get_shortcodes() {
        $output = array();
        require_once($this->path . '/functions/shortcode_list.php');
        return $output;
    }

    function get_icons() {
        $output = array();
        require_once($this->path . '/functions/icon_list.php');
        return $output;
    }

    function get_rows() {
        $output = array();
        require_once($this->path . '/functions/row_list.php');
        return $output;
    }

    function get_shortcode($get) {
		if (array_key_exists('f', $get)) {

			if(!isset($post) && isset($get['post_id']) && (int)$get['post_id']>0 ){
				global $post;
				$post=new stdclass;
				$post=get_post($get['post_id']);
			}
            $shortcode = '[' . $get['f'];
            $content = '';
            if (array_key_exists('options', $get)) {
                $optArray = $get['options'];
                foreach ($optArray as $name => $val) {
                    // check for sortable elements
                    if (!is_array($val)) {
                        if($name == 'shopify_products_data'){
                          $content = str_replace('&quot;', '"', $val);
                        } else if ($name == 'content')
                            $content = str_replace('&quot;', '"', $val);
                        else
                            $shortcode .= ' ' . $name . '="' . $val . '"';
                    }
                    else if (!empty($val)) {
                        $sortableOpts = Array();
                        $firstOpt = true;
                        foreach ($val['order'] as $pos => $id) {
                            if (!empty($val['items'][$id]) && is_array($val['items'][$id])) {
                                foreach ($val['items'][$id] as $opt => $oval) {
                                    if ($opt == 'content') {
                                        if ($firstOpt) {
                                            $firstOpt = false;
                                            $content .= str_replace('&quot;', '"', $oval);
                                        } else {
                                            $content .= '|' . str_replace('&quot;', '"', $oval);
                                        }
                                    } else
                                        $sortableOpts[$opt] = (array_key_exists($opt, $sortableOpts) ? $sortableOpts[$opt] . '|' . $oval : $oval);
                                }
                            }
                        }
                        foreach ($sortableOpts as $opt => $oval) {
                            if ($opt == 'content')
                                $content = str_replace('&quot;', '"', $oval);
                            else
                                $shortcode .= ' ' . $opt . '="' . $oval . '"';
                        }
                    }
                }
            }
            $shortcode .= ']' . $content . '[/' . $get['f'] . ']';
            $contents = do_shortcode($shortcode);
            
            return $contents;
        }
    }

    function get_google_fonts($json = false) {
        $current_date = getdate(date("U"));
        $current_date = $current_date['weekday'] . $current_date['month'] . $current_date['mday'] . $current_date['year'];
        if (!get_option('pbuilder_admin_webfonts')) {
            //$file_get = wp_remote_fopen("https://d1ug6aqcpxo8y6.cloudfront.net/resources/fonts.txt");
            $file_get = file_get_contents(IMSCPB_DIR . "/font/fonts.txt");
            if (strlen($file_get) > 100) {
                add_option('pbuilder_admin_webfonts', $file_get);
                add_option('pbuilder_admin_webfonts_date', $current_date);
            }
        }
        if (get_option('pbuilder_admin_webfonts_date') != $current_date || get_option('pbuilder_admin_webfonts_date') == '') {
            //$file_get = wp_remote_fopen("https://d1ug6aqcpxo8y6.cloudfront.net/resources/fonts.txt");
            $file_get = file_get_contents(IMSCPB_DIR . "/font/fonts.txt");
            if (strlen($file_get) > 100) {
                update_option('pbuilder_admin_webfonts', $file_get); //wp_remote_fopen("https://d1ug6aqcpxo8y6.cloudfront.net/resources/fonts.txt"));
                update_option('pbuilder_admin_webfonts_date', $current_date);
            }
        }
        $fontsjson = get_option('pbuilder_admin_webfonts');
        $decode = json_decode($fontsjson, true);
        if (!is_array($decode) || $fontsjson == '' || !isset($fontsjson)) {
            $fontFailList = '';
            require($this->path . '/functions/font_list.php');
            $fontsjson = $fontFailList;
            $decode = json_decode($fontsjson, true);
        }
        $webfonts = array();
        $webfonts['default'] = 'Default';
        foreach ($this->standard_fonts as $key) {
            $item_family = $key;
            $item_family_trunc = str_replace(' ', '+', $item_family);
            $webfonts[$item_family_trunc] = str_replace('+', ' ', $item_family);
            ;
        }
        foreach ($decode['items'] as $key => $value) {
            $item_family = $decode['items'][$key]['family'];
            $item_family_trunc = str_replace(' ', '+', $item_family);
            $webfonts[$item_family_trunc] = $item_family;
        }
        if ($json)
            return $fontsjson;
        return $webfonts;
    }

    function get_font_variants($optionName = false, $variants = false) {
        if(!is_array(@$this->fontsjsondecoded)){
			if(!@$this->fontsjson) $this->fontsjson = get_option('pbuilder_admin_webfonts');

			$this->fontsjsondecoded = json_decode($this->fontsjson, true);
			if (!is_array($this->fontsjsondecoded) || $this->fontsjson == '' || !isset($this->fontsjson)) {
				$fontFailList = '';
				require($this->path . '/functions/font_list.php');
				$this->fontsjson = $fontFailList;
				$this->fontsjsondecoded = json_decode($this->fontsjson, true);
			}
		}


		if ($optionName == false) {

            $vars = array();
            foreach ($this->standard_fonts as $key) {
                $item_family = $key;
                $item_family_trunc = str_replace(' ', '+', $item_family);
                $vars[$item_family_trunc] = $this->standard_fonts_variants;
            }
            foreach ($this->fontsjsondecoded['items'] as $key => $value) {
                $vars[$value['family']] = $value['variants'];
            }
            return $vars;
        } else {
            $font = str_replace('+', ' ', $this->option($optionName)->value);
            if ($font == 'default' || $font == '')
                return array('default' => 'Default');
            else if ($variants != false && is_array($variants)) {
                if (array_key_exists($font, $variants)) {
                    return $variants[$font];
                } else {
                    return array('regular');
                }
            } else {

                foreach ($this->standard_fonts as $key) {
                    $item_family = $key;
                    $item_family_trunc = str_replace(' ', '+', $item_family);
                    if ($item_family_trunc == $font) {
                        return $this->standard_fonts_variants;
                    }
                }
                foreach ($this->fontsjsondecoded['items'] as $key => $value) {
                    if ($value['family'] == $font) {
                        $vars = array();
                        foreach ($value['variants'] as $fvar) {
                            $vars[$fvar] = $fvar;
                        }
                        return $vars;
                    }
                }
            }
        }
    }

    function get_font_head() {
        $output = '';
        require($this->path . '/functions/font_head.php');
        return $output;
    }

    function get_head_css() {
        $output = '';
        require_once($this->path . '/functions/head_css.php');
        return $output;
    }

    function strip_html_tags($text) {
        $text = preg_replace(
                array(
            // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
            // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
                ), array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
                ), $text);
        return strip_tags($text);
    }

    function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array($r, $g, $b);
        return implode(",", $rgb); // returns the rgb values separated by commas
        //return $rgb; // returns an array with the rgb values
    }

    function excerpt_filter($output) {
        global $post;
        if (empty($output) && !empty($post->post_content)) {
            $text = $this->strip_html_tags(strip_shortcodes($post->post_content));
            $excerpt_length = apply_filters('excerpt_length', 55);
            $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
            $text = wp_trim_words($text, $excerpt_length, $excerpt_more);
            return $text;
        } 
        return $output;
    }



    function replace_content($content, $pid = 0) {
        if ($content == '//builder-false') {
            $id = $pid;
            $locked = false;
        } else {
            global $post;
            $id = $post->ID;
            $content = $this->replace_funnel_placeholders($content,$id);
            $locked = post_password_required();
        }
        $builder = $this->database($id, true);
        $output = '';

        if ($content == '//builder-false' || ($builder->switch == 'on' && !$locked && is_singular())) {
            require($this->path . '/pages/front_html.php');
            $output = $this->replace_funnel_placeholders($output,$id);
            return $output;
        } else {
            return $content;
        }
    }

    // DEPRECATED
    function get_html($builder) {

        $html = '';
        $output = '
		<div id="pbuilder_wrapper"' . ($builder->items == '{}' ? ' class="empty"' : '') . '>';
        $sidebar = false;
        if ($builder->items != '{}') {
            $items = json_decode(stripslashes($builder->items), true);
            if (array_key_exists('sidebar', $items) && array_key_exists('active', $items['sidebar']) && array_key_exists('items', $items['sidebar']) && array_key_exists('type', $items['sidebar']) && $items['sidebar']['active'] == true) {
                $sidebar = $items['sidebar']['type'];
                $html = '<div class="pbuilder_sidebar pbuilder_' . $items['sidebar']['type'] . ' pbuilder_row" data-rowid="sidebar"><div class="pbuilder_column">';
                if (is_array($items['sidebar']['items'])) {
                    foreach ($items['sidebar']['items'] as $sh) {
                        if (!is_null($items['items'][$sh])) {
                            $html .= '<div class="pbuilder_module" data-shortcode="' . $items['items'][$sh]['slug'] . '" data-modid="' . $sh . '">';
                            $html .= $this->get_shortcode($items['items'][$sh]);
                            $html .= '</div>';
                        }
                    }
                }
                $html .= '</div><div style="clear:both;"></div></div>';
            }
        }
        $output .= $html . '
			<div id="pbuilder_content_wrapper"' . ($sidebar != false ? ' class="pbuilder_content_' . $sidebar . '"' : '') . '>
				<div id="pbuilder_content">
		';
        if ($builder->items != '{}') {
            $rows = $this->rows;

			for ($rowId = 0; $rowId < $items['rowCount']; $rowId++) {
                if (array_key_exists($rowId, $items['rowOrder']))
                    $row = $items['rowOrder'][$rowId];
                else
                    $row = null;
                if (!is_null($row)) {
                    $current = $items['rows'][$row];
                    $html = $rows[$current['type']]['html'];
                    $html = str_replace('%1$s', $row, $html);
                    $html = str_replace('%2$s', '', $html);
                    foreach ($current['columns'] as $colId => $shortcodes) {
                        $columnInterface = '';
                        foreach ($shortcodes as $sh) {
                            if (!is_null($items['items'][$sh])) {
                                $columnInterface .= '<div class="pbuilder_module" data-shortcode="' . $items['items'][$sh]['slug'] . '" data-modid="' . $sh . '">';
                                $columnInterface .= $this->get_shortcode($items['items'][$sh]);
                                $columnInterface .= '</div>';
                            }
                        }
                        $html = str_replace('%' . ($colId + 3) . '$s', $columnInterface, $html);
                    }
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
        return $output;
    }

    function refresh_shortcode_list() {
        $pbuilder_sidebars = array();
        $pbuilder_sidebar_std = '';
        foreach ($GLOBALS['wp_registered_sidebars'] as $sidebar) {
            if ($pbuilder_sidebar_std == '')
                $pbuilder_sidebar_std = $sidebar['id'];
            $pbuilder_sidebars[$sidebar['id']] = ucwords($sidebar['name']);
        }
        if (array_key_exists('sidebar', $this->shortcodes))
            $this->shortcodes['sidebar']['options']['group_sidebar']['options']['name']['options'] = $pbuilder_sidebars;
        $this->shortcodes['sidebar']['options']['group_sidebar']['options']['name']['std'] = $pbuilder_sidebar_std;
        $shortcode_arr = $this->shortcodes;
        $shortcode_arr['rowcontrols'] = array('options' => $this->row_controls);
        $shortcode_arr['columncontrols'] = array('options' => $this->column_controls);




        foreach ($shortcode_arr as $name => $array) {
			if (!isset($this->hideifs['children'][$name]))
                $this->hideifs['children'][$name] = array();
            if (!isset($this->hideifs['parents'][$name]))
                $this->hideifs['parents'][$name] = array();
				if (!isset($this->hideifs['parents'][$name]))
                $this->hideifs['groups'][$name] = array();
            foreach ($array['options'] as $opt => $optarray) {
                $this->set_hideifs($name, $array, $opt, $optarray);
            }
        }

    }

    function set_hideifs($name, $array, $opt, $optarray) {


		if (isset($optarray['type']) && $optarray['type'] == 'sortable') {
			if($name=='button'){
				update_option('pb_sc_test_1'.$name,$this->hideifs['parents']['button']);
			}
            foreach ($optarray['options'] as $soname => $soarray) {
                if (isset($soarray['hide_if'])) {
                    if (!isset($this->hideifs['children'][$name]))
                        $this->hideifs['children'][$name] = array();
                    if (!isset($this->hideifs['children'][$name][$opt]))
                        $this->hideifs['children'][$name][$opt] = array();
                    $this->hideifs['children'][$name][$opt][$soname] = $soarray['hide_if'];
                    foreach ($soarray['hide_if'] as $hide => $hidear) {
                        if (!isset($this->hideifs['parents'][$name][$hide]))
                            $this->hideifs['parents'][$name][$hide] = array();
                        if (array_keys($hidear) !== range(0, count($hidear) - 1)) {
                            foreach ($hidear as $sohide => $sohidear) {
                                if (!isset($this->hideifs['parents'][$name][$hide][$sohide]))
                                    $this->hideifs['parents'][$name][$hide][$sohide] = array();
                                if (!isset($this->hideifs['parents'][$name][$hide][$sohide][$opt]))
                                    $this->hideifs['parents'][$name][$hide][$sohide][$opt] = array();
                                $this->hideifs['parents'][$name][$hide][$sohide][$opt][$soname] = $sohidear;
                            }
                        }
                        else {
                            if (!isset($this->hideifs['parents'][$name][$hide][$opt]))
                                $this->hideifs['parents'][$name][$hide][$opt] = array();
                            $this->hideifs['parents'][$name][$hide][$opt][$soname] = $hidear;
                        }
                    }
                }
            }
        }
        else if (isset($optarray['type']) && $optarray['type'] == 'collapsible') {

			foreach($optarray['hide_if'] as $option => $hidear){

				$this->hideifs['groups'][$name][$option][$opt] = $hidear;
			}

			if (isset($optarray['options'])) {
                foreach ($optarray['options'] as $soname => $soarray) {
                    $this->set_hideifs($name, $array, $soname, $soarray);
                }
            }


        } else {

            if (isset($optarray['hide_if'])) {
                $this->hideifs['children'][$name][$opt] = $optarray['hide_if'];
                foreach ($optarray['hide_if'] as $hide => $hidear) {
                    if (!isset($this->hideifs['parents'][$name][$hide]))
                        $this->hideifs['parents'][$name][$hide] = array();
                    $this->hideifs['parents'][$name][$hide][$opt] = $hidear;

                }
            }
			if($name=='button'){
				update_option('pb_sc_test_3'.$name,$this->hideifs['parents']['button']);
			}
        }


    }

    function wp_head() {
        global $post;
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $this->yoast = is_plugin_active('wordpress-seo/wp-seo.php');
        $output = $this->get_font_head();
        $output .= $this->get_head_css();
        $fb_app_id = $this->option("fb_app_id");
        if ($fb_app_id->value != "")
            $output .= '<meta property="fb:app_id" content="' . $fb_app_id->value . '" />';
        echo $output;
        $pb_redirect_enable = get_post_meta(@$post->ID, 'pb_redirect_enable', true);
        if ($pb_redirect_enable == 1) {
            $pb_redirect_url = get_post_meta($post->ID, 'pb_redirect_url', true);
            $pb_redirect_message = get_post_meta($post->ID, 'pb_redirect_message', true);
            $pb_redirect_time = get_post_meta($post->ID, 'pb_redirect_time', true);
            wp_enqueue_script('pbuilder_exit_redirect', $this->url . 'js/exit_redirect.js', array('jquery'), '1.0', true);
            wp_localize_script('pbuilder_exit_redirect', 'pb_exit_redirect', array(
                'enabled' => $pb_redirect_enable,
                'url' => $pb_redirect_url,
                'message' => $pb_redirect_message,
                'time' => $pb_redirect_time,
                    )
            );
        }
    }

    function ajax_shortcode() {
        if (array_key_exists('options', $_POST)) {
            $_POST['options'] = json_decode(stripslashes($_POST['options']), true);
        }
        do_action('profit_builder_ajax_shortcode');
        echo $this->get_shortcode($_POST);
        die();
    }

    function ajax_save() {
        if (array_key_exists('json', $_POST)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'profit_builder_pages';
            $id = (int) $_POST['id'];


			$old_page=(array)$wpdb->get_row('SELECT * FROM '.$table_name.' WHERE id="'.$id.'" ORDER BY page_date DESC LIMIT 1');
			//print_r($old_page);
			if($old_page){
				$wpdb->update(
						$table_name,
						array('page_status' => 'revision'),
						array('page_id' => $old_page['page_id']),
						array('%s'),
						array('%d')
				);
			} else {
				$old_page['switch']='on';
				$old_page['layout']='full_width';
			}

			$wpdb->insert(
				$table_name,
				array(
				   'id' => $id,
				   'switch' => $old_page['switch'],
				   'layout' => $old_page['layout'],
				   'items' => $_POST['json'],
				   'page_date' => time()
				),
				array('%d','%s','%s','%s','%s')
			);

			//Cleanup Old Revisions
			$wpdb->query('DELETE FROM '.$table_name.' WHERE id='.$id.' AND page_id NOT IN(SELECT page_id FROM (SELECT page_id FROM '.$table_name.' WHERE id='.$id.' ORDER BY page_date DESC LIMIT 10) AS latest_revisions)');

            if ($this->option('save_overwrite')->value == 'true') {
                $my_post = array();
                $my_post['ID'] = $id;
                $my_post['post_content'] = $this->replace_content('//builder-false', $id);
                wp_update_post($my_post);
            }
            echo 'success';
        }
        die();
    }

	function ajax_save_internal($post_id, $json) {
        if ($json) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'profit_builder_pages';
            $id = (int) $post_id;


			$old_page=(array)$wpdb->get_row('SELECT * FROM '.$table_name.' WHERE id="'.$id.'" ORDER BY page_date DESC LIMIT 1');

			if($old_page){
				$wpdb->update(
						$table_name,
						array('page_status' => 'revision'),
						array('page_id' => $old_page['page_id']),
						array('%s'),
						array('%d')
				);
			} else {
				$old_page['switch']='on';
				$old_page['layout']='full_width';
			}

			$wpdb->insert(
				$table_name,
				array(
				   'id' => $id,
				   'switch' => $old_page['switch'],
				   'layout' => $old_page['layout'],
				   'items' => $json,
				   'page_date' => time()
				),
				array('%d','%s','%s','%s','%s')
			);

			//Cleanup Old Revisions
			$wpdb->query('DELETE FROM '.$table_name.' WHERE id='.$id.' AND page_id NOT IN(SELECT page_id FROM (SELECT page_id FROM '.$table_name.' WHERE id='.$id.' ORDER BY page_date DESC LIMIT 10) AS latest_revisions)');

            if ($this->option('save_overwrite')->value == 'true') {
                $my_post = array();
                $my_post['ID'] = $id;
                $my_post['post_content'] = $this->replace_content('//builder-false', $id);
                wp_update_post($my_post);
            }
        }
    }

    function ajax_get_lfpflow_fields(){
        $flow_components = get_post_meta($_GET['flow_id'],'flow_components');
        $fields=array();
        foreach($flow_components[0][0]['vars'] as $field_id=>$field){
          $fields[$field_id]=$field_id;
          foreach ($field as $id => $req) {
            if($req=='required'){
              $fields[$field_id]=$fields[$field_id].' <strong>(required)</strong>';
            }
          }
        }
        if(count($fields)>0){
          echo '<ul class="pbuilder_lfpflow_fields">';
          echo '<li><strong>Flow fields:</strong></li>';
          foreach($fields as $field){
            echo '<li>'.$field.'</li>';
          }
          echo '</ul>';
        }
        die();
    }

	function ajax_revisions() {
    		global $wpdb;
    		$table_name = $wpdb->prefix . 'profit_builder_pages';
            $id = (int) $_GET['id'];

    		$revisions_raw = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE id="' . $id . '" ORDER BY page_date DESC');
    		$revisions = array();
    		foreach($revisions_raw as $revision_id => $revision_raw_data){

    		    $revisions[] = array('page_id'=>$revision_raw_data->page_id, 'page_date'=>date('m/d/Y H:i', $revision_raw_data->page_date));

    		}
    		wp_send_json_success($revisions);
        die();
    }





	function ajax_load_revision() {
		global $wpdb;
        $page_id = (int) $_GET['page_id'];

		if (isset($page_id)) {
			$table_name = $wpdb->prefix . 'profit_builder_pages';
        	$builder = $wpdb->get_row('SELECT * FROM ' . $table_name . ' WHERE page_id=' . $page_id);
			require($this->path . '/pages/front_html.php');
		    echo $builder->items . '|+break+response+|' . $output;
        }
        die();
    }

	function ajax_funnels() {
		global $wpdb;

		$page_id=(int)$_GET['id'];

		if(!$funnels=get_option('profit_builder_funnels')){
			$funnels=array();
		}


		$this->show_funnels($funnels);

		die();
	}

	function ajax_add_funnel() {
		global $wpdb;

		$page_id=(int)$_GET['id'];
		$funnel_name = $_GET['funnel_name'];

		if(!$funnels=get_option('profit_builder_funnels')){
			$funnels=array();
		}

		$funnels[]=array('name'=>$funnel_name,'pages'=>array());

		update_option('profit_builder_funnels',$funnels);

		$this->show_funnels($funnels);

		die();
	}

  function replace_funnel_placeholders($content,$post_id){
    if(!$funnel_page_id=get_post_meta($post_id,'so_split_test',true)){
      $funnel_page_id=$post_id;
    }

    $funnel_id = get_post_meta($funnel_page_id,'profit_builder_funnel',true);

    if($funnel_id !== false){
      if($funnels=get_option('profit_builder_funnels')){

        if(array_key_exists($funnel_id,$funnels)){

          $funnel_page_order = array_keys($funnels[$funnel_id]['pages']);
          $current_page_position=array_search($funnel_page_id,$funnel_page_order);

          // Update placeholder links
          if($current_page_position>0 && $funnel_page_order[$current_page_position-1]>0){
            $prev_page_url = get_permalink($funnel_page_order[$current_page_position-1]);
            $content = str_replace('[PBFUNNEL_PREV]',$prev_page_url,$content);
          }

          if($current_page_position<count($funnel_page_order) && $funnel_page_order[$current_page_position+1]>0){
            $next_page_url = get_permalink($funnel_page_order[$current_page_position+1]);
            $content= str_replace('[PBFUNNEL_NEXT]',$next_page_url,$content);
          }

        }
      }
    }

    return $content;
  }

	function show_funnels($funnels,$page_id=false){
		$pbso_live_editor=false;
		if(isset($_GET['pbso_live_editor']) && $_GET['pbso_live_editor']=='true'){
			$pbso_live_editor=true;
		}

    if(isset($_GET['id'])){
      $page_id=$_GET['id'];
    }

		echo '<div style="overflow: auto;">';
		echo '<div id="pb_new_funnel_show_form" class="button button-primary">Create New Funnel</div>';
		echo '<div id="pb_new_funnel_form" style="display:none">Funnel name: <input type="text" name="new_funnel_name" id="new_funnel_name" value="" /> <div id="pb_new_funnel_add" class="button button-primary">Create Funnel</div></div>';
		echo '</div>';

		if(count($funnels)>0){
		  echo '<br /><br /><table class="so_pages_table">';
		  echo '<thead><tr>
			  <td>Funnel</td>
			  <td colspan="2"></td>
			  </tr>
			  </thead>';
			  foreach($funnels as $funnel_id=>$funnel){
				  echo '<tr '.(array_key_exists($page_id,$funnel['pages'])?'class="pb_page_in_funnel"':'').'>';
				  echo '<td>'.$funnel['name'].'</td>';
				  echo '<td><div data-funnel="'.$funnel_id.'" class="pb_funnel_show_pages button button-primary">View Funnel<span class="pbso-button-spinner-wrapper"><i class="fa fa-refresh fa-spin fa-1x fa-fw margin-bottom"></i></span></div></td>';
				  echo '<td><div data-funnel="'.$funnel_id.'" class="pb_funnel_delete button">Delete Funnel<span class="pbso-button-spinner-wrapper"><i class="fa fa-refresh fa-spin fa-1x fa-fw margin-bottom"></i></span></div></td>';
				  echo '</tr>';
			  }
		  echo '</table>';
		} else {
		    echo "<h3>No funnels found</h3>";
		}

		if($pbso_live_editor){
			echo '<div class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</div>';
		}
	}


	function ajax_show_funnel_pages(){

		$funnel_id=$_GET['funnel_id'];
		$page_id=$_GET['page_id'];
		if(!$funnels=get_option('profit_builder_funnels')){
			$funnels=array();
		}

		$this->show_funnel_pages($funnels,$funnel_id,$page_id);

		die();

	}

	function ajax_funnel_delete(){
		global $wpdb;

		$funnel_id=$_GET['funnel_id'];
		if($funnels=get_option('profit_builder_funnels')){
			if(array_key_exists($funnel_id,$funnels)){
				unset($funnels[$funnel_id]);
				update_option('profit_builder_funnels',$funnels);
				$wpdb->query('DELETE FROM '.$wpdb->postmeta.' WHERE meta_key="profit_builder_funnel" AND meta_value="'.$funnel_id.'"');
			}
		}

		$this->show_funnels($funnels);

		die();
	}


	function show_funnel_pages($funnels,$funnel_id,$page_id=0,$message=false){
		$pbso_live_editor=false;

    if(isset($_GET['pbso_live_editor']) && $_GET['pbso_live_editor']=='true'){
			$pbso_live_editor=true;
		}

		if($page_ab_parent = get_post_meta($page_id,'so_split_test',true)){
			$page_id=$page_ab_parent;
		}

		echo '<div style="overflow: auto;">';
		echo '<div id="pb_funnel_back" class="button button-primary"><span class="dashicons dashicons-arrow-left-alt2"></span> Back to funnels</div>';

		if(false === array_search($page_id,$funnels[$funnel_id]['pages'])){
			echo ' <div id="pb_funnel_add_current_page" data-funnel="'.$funnel_id.'" class="button button-primary"><span class="dashicons dashicons-plus-alt"></span> Add current page to funnel</div>';
		}

		echo '</div>';

		if($message){
			echo "<h3>".$message."</h3>";
		}

		if(array_key_exists($funnel_id,$funnels)){
			echo '<h2>Pages in '.$funnels[$funnel_id]['name'].' funnel</h2>';

			if(count($funnels[$funnel_id]['pages'])>0){
				echo '<p>You can drag each page title to rearange the order of the pages in the funnel.</p><br />';

				echo '<ul id="funnel_pages">';
        $first_page=true;
        foreach($funnels[$funnel_id]['pages'] as $funnel_page => $funnel_page_data){
					$check_if_page_is_ab = get_post_meta($funnel_page,'so_split_test_settings',true);
					echo '<li id="'.$funnel_page.'"><span class="pbuilder_funnel_page_drag"><span class="dashicons dashicons-sort"></span></span><span><a href="'.get_permalink($funnel_page).'" target="_blank">'.$this->truncit(get_the_title($funnel_page),40).($check_if_page_is_ab?'(A/B Test)':'').'</a>';
          if(!$pbso_live_editor && !$first_page) echo ' <a href="'.get_permalink($funnel_page).'?bypass='.md5($funnel_id).'" target="_blank"><i class="fa fa-unlock" aria-hidden="true"></i></a>';
          echo '</span><span class="pbuilder_funnel_remove_page button" data-funnel="'.$funnel_id.'" data-page="'.$funnel_page.'" style="float: right;">Remove page <span class="pbso-button-spinner-wrapper"><i class="fa fa-refresh fa-spin fa-1x fa-fw margin-bottom"></i></span></span></li>';
          $first_page=false;
				}
				echo '</ul>';
			} else {
				echo 'There are no pages in this funnel.';
			}
		} else {
			echo "Funnel not found";
		}

		echo '<div style="display:none;" id="pb_funnel_update" data-funnel="'.$funnel_id.'" class="pbuilder_gradient pbuilder_button button-primary button left">Save <span class="pbso-button-spinner-wrapper"><i class="fa fa-refresh fa-spin fa-1x fa-fw margin-bottom"></i></span></div>';

		if($pbso_live_editor){
			echo '<div class="pbuilder_gradient pbuilder_button button-primary button pbuilder_popup_close right">Close</div>';
		}
	}

	function funnel_cleanup(){

	}

	function ajax_funnel_add_page(){
		$funnel_id=$_GET['funnel_id'];
		$page_id=$_GET['id'];

		//Check if page is part of AB Test
		$page_ab_parent = get_post_meta($page_id,'so_split_test',true);

		if($funnels=get_option('profit_builder_funnels')){
			if(array_key_exists($funnel_id,$funnels)){
				if($page_ab_parent){
					$message = "A/B Test added to funnel";
					$funnels[$funnel_id]['pages'][$page_ab_parent] = array('views'=>array(),'unique_views'=>array(),'conversions'=>array());
					$page_id = $page_ab_parent;
				} else {
					$message = "Page added to funnel";
					$funnels[$funnel_id]['pages'][$page_id] = array('views'=>array(),'unique_views'=>array(),'conversions'=>array());
				}
				update_post_meta($page_id,'profit_builder_funnel',$funnel_id);
			} else {
				$message = "Funnel does not exist";
			}
		} else {
			$message = "No Funnels Found";
		}

		update_option('profit_builder_funnels',$funnels);

		$this->show_funnel_pages($funnels,$funnel_id,$page_id,$message);

		die();
	}

	function ajax_funnel_update_pages(){
		if(!$funnels=get_option('profit_builder_funnels')){
      $funnels=array();
    }

    $funnel_id=$_GET['funnel_id'];

    if(!array_key_exists($funnel_id,$funnels)){
      die();
    }

    $page_order = $_GET['funnel_pages'];

    $pages_new=array();
    foreach($page_order as $page_id){
       $pages_new[$page_id]=$funnels[$funnel_id]['pages'][$page_id];
    }
    $funnels[$funnel_id]['pages'] = $pages_new;

    update_option('profit_builder_funnels', $funnels);

		die();
	}

	function ajax_funnel_remove_page(){
		$funnel_id=$_GET['funnel_id'];
		$page_id=$_GET['page_id'];


		if($funnels=get_option('profit_builder_funnels')){
			if(array_key_exists($funnel_id,$funnels)){
				unset($funnels[$funnel_id]['pages'][$page_id]);
			}
		}

		update_option('profit_builder_funnels',$funnels);

		$this->show_funnel_pages($funnels,$funnel_id,$page_id,false);
		die();
	}


  function get_funnel_stats($post_id,$days_history=20){
    global $wpdb;
    $funnel_stats = $wpdb->get_results($wpdb->prepare('SELECT date, views, views_unique, conversions FROM ' . $wpdb->prefix . 'profit_builder_funnel_stats WHERE post_id = %d AND date > %s', array($post_id,date('Y-m-d', (current_time('timestamp') - DAY_IN_SECONDS * $days_history)))), ARRAY_A);

    $days = array();
    $totals = array('views' => 0, 'views_unique' => 0, 'conversions' => 0);
    for ($i = $days_history; $i >= 0; $i--) {
      $date = date('Y-m-d', (current_time('timestamp') - DAY_IN_SECONDS * $i));
      $days[$date] = 0;
    }

    foreach ($funnel_stats as $day) {
      $days[$day['date']] = $day['conversions'];
      $totals['views']+=$day['views'];
      $totals['views_unique']+=$day['views_unique'];
      $totals['conversions']+=$day['conversions'];
    }

    return array('days'=>$days,'totals'=>$totals);
  }

  function get_ab_test_stats($post_id,$days_history=20){
    global $wpdb;
    $ab_stats = $wpdb->get_results($wpdb->prepare('SELECT date, views, views_unique, conversions FROM ' . $wpdb->prefix . 'profit_builder_ab_stats WHERE post_id = %d AND date > %s', array($post_id,date('Y-m-d', (current_time('timestamp') - DAY_IN_SECONDS * $days_history)))), ARRAY_A);

    $days = array();
    $totals = array('views' => 0, 'views_unique' => 0, 'conversions' => 0);
    for ($i = $days_history; $i >= 0; $i--) {
      $date = date('Y-m-d', (current_time('timestamp') - DAY_IN_SECONDS * $i));
      $days[$date] = 0;
    }

    foreach ($ab_stats as $day) {
      $days[$day['date']] = $day['conversions'];
      $totals['views']+=$day['views'];
      $totals['views_unique']+=$day['views_unique'];
      $totals['conversions']+=$day['conversions'];
    }

    return array('days'=>$days,'totals'=>$totals);
  }

  function add_funnel_view($post_id){
    global $wpdb;
    $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "profit_builder_funnel_stats (date, post_id, views)
                       VALUES (%s, %d, %d)
                       ON DUPLICATE KEY UPDATE views = views + 1", array(current_time('mysql'), $post_id, 1)));
  }

  function add_funnel_unique_view($post_id){
    global $wpdb;
    $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "profit_builder_funnel_stats (date, post_id, views_unique)
                       VALUES (%s, %d, %d)
                       ON DUPLICATE KEY UPDATE views_unique = views_unique + 1", array(current_time('mysql'), $post_id, 1)));
  }

  function add_funnel_conversion($post_id){
    global $wpdb;
    $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "profit_builder_funnel_stats (date, post_id, conversions)
                       VALUES (%s, %d, %d)
                       ON DUPLICATE KEY UPDATE conversions = conversions + 1", array(current_time('mysql'), $post_id, 1)));
  }


  function add_ab_view($post_id){
    global $wpdb;
    $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "profit_builder_ab_stats (date, post_id, views)
                       VALUES (%s, %d, %d)
                       ON DUPLICATE KEY UPDATE views = views + 1", array(current_time('mysql'), $post_id, 1)));
  }

  function add_ab_unique_view($post_id){
    global $wpdb;
    $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "profit_builder_ab_stats (date, post_id, views_unique)
                       VALUES (%s, %d, %d)
                       ON DUPLICATE KEY UPDATE views_unique = views_unique + 1", array(current_time('mysql'), $post_id, 1)));
  }

  function add_ab_conversion($post_id){
    global $wpdb;
    $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "profit_builder_ab_stats (date, post_id, conversions)
                       VALUES (%s, %d, %d)
                       ON DUPLICATE KEY UPDATE conversions = conversions + 1", array(current_time('mysql'), $post_id, 1)));
  }



	function funnels_show_meta_box() {
		global $wpdb,$post;

    if(isset($_GET['id'])){
      $page_id=$_GET['id'];
    }

		if(!$funnels=get_option('profit_builder_funnels')){
			$funnels=array();
		}

    if($so_split_test=get_post_meta($post->ID,'so_split_test',true)){
      $funnel_page_id=$so_split_test;
	  } else {
      $funnel_page_id=$post->ID;
    }
    $funnel_id = get_post_meta($funnel_page_id,'profit_builder_funnel',true);
    if($funnel_id !== false){
      if($funnels=get_option('profit_builder_funnels')){
        if(array_key_exists($funnel_id,$funnels)){
          $funnel_page_order = array_keys($funnels[$funnel_id]['pages']);
          $current_page_position=array_search($funnel_page_id,$funnel_page_order);
          echo '<div class="notice notice-success"><p>This page is step <strong>'.($current_page_position+1).'/'.count($funnels[$funnel_id]['pages']).'</strong> of <strong>'.$funnels[$funnel_id]['name'].'</strong> funnel.</p></div>';
        }
      }
    }


		echo '<div class="pbuilder_funnel_content">';
		$this->show_funnels($funnels,$post->ID);
		echo '</div>';
		?>
        <script>

		var post_id='<?php echo $post->ID; ?>';
		jQuery(document).on('click','.pbuilder_show_funnel',function (e) {
            e.preventDefault();

			pbuilder_close_shortcode_menu();

            var html = '<div class="pbuilder_popup pbuilder_popup_abtests pbuilder_controls_wrapper">\
			<div class="pbuilder_popup_funnel_title">Funnel Editor</div>\
			<div class="pbuilder_popup_content pbuilder_funnel_content"></div>';
			html += '</div>';

			jQuery('#pbuilder_body').prepend(html);

			var data = {
                action: 'pbuilder_page_funnels',
				id: post_id
            }
            jQuery.get(ajaxurl, data, function (response) {
				jQuery('.pbuilder_funnel_content').html(response);
			});
        });

		jQuery('#pb_new_funnel_show_form').on('click',function (e) {
			jQuery('#pb_new_funnel_form').show();
		});


		jQuery(document).on('click','#pb_new_funnel_add',function (e) {
			var data = {
                action: 'pbuilder_add_funnel',
				id: post_id,
				funnel_name:jQuery('#new_funnel_name').val()
            }

			jQuery.get(ajaxurl, data, function (response) {
				revisions_html=response;
				jQuery('.pbuilder_funnel_content').html(revisions_html);
			});
		});

		jQuery(document).on('click','.pb_funnel_show_pages',function (e) {
			var funnel_id=jQuery(this).data('funnel');

			var data = {
          action: 'pbuilder_funnel_pages',
          id: post_id,
          funnel_id:funnel_id
      }

			jQuery.get(ajaxurl, data, function (response) {
				revisions_html=response;
				jQuery('.pbuilder_funnel_content').html(revisions_html);

        console.log('Apply sortable');

				jQuery("#funnel_pages").sortable({
					stop: function( event, ui ) {
						jQuery('#pb_funnel_update').show();
					}
				});

        jQuery("#funnel_pages").disableSelection();
			});
		});

		jQuery(document).on('click','#pb_funnel_update',function (e) {
			if(jQuery(this).attr('disabled')=='disabled'){
				return;
			}

			//jQuery(this,'.pbso-button-spinner-wrapper').show();
			jQuery(this).children('.pbso-button-spinner-wrapper').show();


			jQuery('#pb_funnel_update').attr('disabled','disabled');

			var funnel_pages = jQuery("#funnel_pages").sortable("toArray");
			console.log(funnel_pages);
			var funnel_id=jQuery(this).data('funnel');


			var data = {
          action: 'pbuilder_funnel_update',
          funnel_pages: funnel_pages,
          funnel_id:funnel_id
      }

			jQuery.get(ajaxurl, data, function (response) {
				jQuery('#pb_funnel_update').hide();
				jQuery('#pb_funnel_update').children('.pbso-button-spinner-wrapper').hide();
        jQuery('#pb_funnel_update').attr('disabled','');
			});
		});


		jQuery(document).on('click','#pb_funnel_back',function(e){
			var data = {
          action: 'pbuilder_page_funnels',
          id: post_id
      }

      jQuery.get(ajaxurl, data, function (response) {
				revisions_html=response;
				jQuery('.pbuilder_funnel_content').html(revisions_html);
			});

		});

    jQuery(document).on('click','.pb_funnel_delete',function(e){
			var funnel_id=$(this).data('funnel');
		  console.log('Remove funnel');
				var data = {
			  action: 'pbuilder_funnel_delete',
			  id: post_id,
			  funnel_id:funnel_id,
			  pbso_live_editor:'true'
		  }

			$('.pbuilder_funnel_content').html(pbuilder_popup_loader);
			jQuery.get(ajaxurl, data, function (response) {
				revisions_html=response;
				jQuery('.pbuilder_funnel_content').html(revisions_html);
			});
		});

		jQuery(document).on('click','#pb_funnel_add_current_page',function(e){
			var data = {
          action: 'pbuilder_funnel_add_page',
          id: post_id,
          funnel_id:jQuery(this).data('funnel')
      }
      jQuery.get(ajaxurl, data, function (response) {
				revisions_html=response;
				jQuery('.pbuilder_funnel_content').html(revisions_html);
			});
		});

    jQuery(document).on('click','.pbuilder_funnel_remove_page',function(e){
			if(jQuery(this).attr('disabled')=='disabled'){
				return;
			}
      console.log('Remove page');
			jQuery(this).children('.pbso-button-spinner-wrapper').show();
			jQuery('.pbuilder_funnel_remove_page').attr('disabled','disabled');

			var funnel_id=jQuery(this).data('funnel');
			var page_id=jQuery(this).data('page');

			var data = {
          action: 'pbuilder_funnel_remove_page',
          page_id: page_id,
          funnel_id:funnel_id
      }

			jQuery.get(ajaxurl, data, function (response) {
				jQuery('.pbuilder_funnel_content').html(response);
			});
		});

		</script>
        <?php
	}

  function numberAbbreviation($number) {
      if($number == 0) return 0;
      $abbrevs = array(12 => "T", 9 => "B", 6 => "M", 3 => "K", 0 => "");
      foreach($abbrevs as $exponent => $abbrev) {
          if($number >= pow(10, $exponent)) {
           $display_num = $number / pow(10, $exponent);
           $decimals = ($exponent >= 3 && round($display_num) < 100) ? 1 : 0;
              return number_format($display_num,$decimals) . $abbrev;
          }
      }
  }

  function truncit($string, $amount) {
     if(strlen($string) > $amount){
      $string = trim(substr($string, 0, $amount))."...";
     }
     return $string;
  }

	function ajax_abtest() {
		  global $wpdb;

		  $so_test_page_id=(int)$_GET['id'];
		  $so_split_test=get_post_meta($so_test_page_id,'so_split_test',true);

		  if($so_split_test){
			  $is_parent_page=false;
			  $so_test_page_id=$so_split_test;
			  $parent_page_data=get_post($so_test_page_id);

			  $so_split_test_settings = get_post_meta($so_test_page_id,'so_split_test_settings',true);
			  echo '<div><p>This page is part of the split test <a href="'.get_edit_post_link($parent_page_data->ID).'">'.$parent_page_data->post_title.'</a> started on '.date(get_option('date_format') . ' @ ' . get_option('time_format'),$so_split_test_settings['start_time']).'</p></div>';

			  /*if(strpos($post->post_content,'so-track')==false && strpos($post->post_content,'data-track')==false){
				echo '<div id="so-conversion-link-warning">There are no tagged links in your content to track conversions.</div>';
			  }*/

			  echo 'To track conversions either add the "so-track" class or data-track="1" attribute to any A tag. Example:';
			  echo '<pre>';
			  echo htmlspecialchars('<a href="http://www.google.com" data-track="1">Google</a>').'<br />';
			  echo htmlspecialchars('<a href="http://www.google.com" class="so-track">Google</a>');
			  echo '</pre>';
		  } else {
			  $is_parent_page=true;
			  $so_split_test_settings = get_post_meta($so_test_page_id,'so_split_test_settings',true);
			  if($so_split_test_settings){
				echo '<p>Please use variations (child pages) to edit the content. This page serves only to configure the A/B test.</p>';
				echo '<p>Test started on '. date(get_option('date_format') . ' @ ' . get_option('time_format') ,$so_split_test_settings['start_time']).'</p><br><br>';
			  }
		  }

		  if(!$so_split_test_settings){
			echo '<p>By enabling A/B testing your content will not be lost. It will simply be cloned into a child page (variation) of this page.</p>';
			echo '<div id="so_enable_split_test" class="button button-primary">I want to A/B test this page</div>';
		  }

		  echo '<input type="hidden" name="so-post" value="' . $so_test_page_id . '" />';
		  if($is_parent_page){
			  echo '<div id="so_split_test_settings" '.($so_split_test_settings?'':' style="display:none;"').'>';

				  if(!$so_split_test_settings){
					  echo '<div class="so-switch-field so-switch-field-wide">
						<div class="so-switch-title">Pages in test: </div>

						<input type="radio" id="so-slit-pages-2" name="so-slit-pages" value="2" checked="checked" />
						<label for="so-slit-pages-2">2</label>

						<input type="radio" id="so-slit-pages-3" name="so-slit-pages" value="3" />
						<label for="so-slit-pages-3">3</label>

						<input type="radio" id="so-slit-pages-4" name="so-slit-pages" value="4" />
						<label for="so-slit-pages-4">4</label>
					  </div>';
				  }

				  echo '<span class="so_label">Test Ends After:</span><input type="number" name="so-test-end-value" style="width:50px;" value="'.(isset($so_split_test_settings['end_value'])?$so_split_test_settings['end_value']:'7').'" />
				  <select name="so-test-end-type" id="so-test-end-type">
				  <option value="days" '.(isset($so_split_test_settings['end_type'])&&$so_split_test_settings['end_type']=='days'?'selected="selected"':'').'>Days</option>
				  <option value="views" '.(isset($so_split_test_settings['end_type'])&&$so_split_test_settings['end_type']=='views'?'selected="selected"':'').'>Total Views</option>
				  <option value="conversions" '.(isset($so_split_test_settings['end_type'])&&$so_split_test_settings['end_type']=='conversions'?'selected="selected"':'').'>Total Conversions</option>
				  </select>
				  <br />';

				  echo '<span class="so_label">Winner is the variation with:</span><select name="so-test-end-winner">
				  <option value="conversions" '.(isset($so_split_test_settings['end_winner'])&&$so_split_test_settings['end_winner']=='conversions'?'selected="selected"':'').'>Most Conversions</option>
				  <option value="rate" '.(isset($so_split_test_settings['end_winner'])&&$so_split_test_settings['end_winner']=='views'?'selected="selected"':'').'>Highest Conversion Rate</option>
				  </select>
				  <br /><br />';



				  if(!$so_split_test_settings){
					echo '<div id="so_enable_split_test_submit" class="button button-primary">Create A/B Test</div>';
				  } else {
					echo '<div id="so_update_split_test_submit" class="button button-primary">Update A/B Test Settings</div>';
					echo '<div id="so_reset_split_test_submit" class="button">Reset Stats</div>';

				  }
				echo '</div>';
			}

			echo '<div id="so_split_test_info" '.($is_parent_page?'':'style="width:100%;"').'>';

				if($so_split_test_settings && count($so_split_test_settings['pages'])>0){
				  echo '<table class="so_pages_table">';
				  echo '<thead><tr>
					  <td>Variation</td>
					  <td align="center">Views</td>
					  <td align="center">Unique Visits</td>
					  <td align="center">Conversions</td>
					  <td align="right" colspan="3"><div id="so_add_new_page" class="button button-primary">Add New Variation <span class="pbso-button-spinner-wrapper"><i class="fa fa-refresh fa-spin fa-1x fa-fw margin-bottom"></i></span></div></td>
					  </tr>
					  </thead>';
				  foreach($so_split_test_settings['pages'] as $so_page_id){
					  $page_info=get_post($so_page_id);
            $page_stats = $this->get_ab_test_stats($so_page_id);
            $total_views+=$page_stats['totals']['views'];
            $unique_views+=$page_stats['totals']['views_unique'];
            $total_conversions+=$page_stats['totals']['conversions'];

					  if($total_conversions>0 && $total_views>0){
						  $conversion_rate=round($total_conversions/$total_views*100);
					  } else {
						  $conversion_rate=0;
					  }
					  echo '<tr ';
					  if($so_page_id==$so_split_test_settings['winner']){
						  echo 'class="so_pages_table_row_winner"';
					  } else if($so_page_id==(int)$_GET['id']){
						  echo 'class="so_pages_table_row"';
					  }
					  echo '>';
					  if($so_page_id==(int)$_GET['id']){
						echo '<td><strong>Currently Editing: '.$page_info->post_title.'</strong></td>';
					  } else {
						echo '<td><a href="'.admin_url('admin-ajax.php').'?action=pbuilder_edit&p='.$so_page_id.'">'.$page_info->post_title.'</a></td>';
					  }
					  echo '<td align="center">'.$this->numberAbbreviation($total_views).'</td>
						<td align="center">'.$this->numberAbbreviation($unique_views).'</td>
						<td align="center">'.$conversion_rate.'% ('.$this->numberAbbreviation($total_conversions).')</td>';
						if($so_split_test_settings['winner']!=$so_page_id){
						  echo '<td style="width:100px;"><div id="so_winner_'.$so_page_id.'" class="so_choose_winner button button-primary">Set as winner <span class="pbso-button-spinner-wrapper"><i class="fa fa-refresh fa-spin fa-1x fa-fw margin-bottom"></i></span></div></td>';
						  echo '<td style="width:100px;"><div id="so_clone_'.$so_page_id.'" class="so_clone_page button button-primary">Clone Variation <span class="pbso-button-spinner-wrapper"><i class="fa fa-refresh fa-spin fa-1x fa-fw margin-bottom"></i></span></div></td>';
						  echo '<td style="width:100px;"><div id="so_delete_'.$so_page_id.'" class="so_delete_page button">Delete Variation <span class="pbso-button-spinner-wrapper"><i class="fa fa-refresh fa-spin fa-1x fa-fw margin-bottom"></i></span></div></td>';
						} else {
						  echo '<td></td><td></td>';
						}
					  echo '</tr>';
				  }
				  echo '</table>';
				  echo '';
				}
			echo '</div>';


			echo '<div class="clearfix"></div>';
			die();
    }

    function ajax_pbuilder_lfpfields(){
         $push_through_flow_id = $_GET['flowid'];
         $flow_components = leadflow_flow_common::get_flow_components($push_through_flow_id);

         foreach($flow_components as $flow_component){
           if($flow_component['_flow-component-type'] == 'start'){
             $flow_data = $flow_component;
             $flow_email_field = str_replace('%%','',$flow_component['primary_email_field']);
           }
         }

/*
fields = [];
fields['name']=[];
fields['name']['is_name']=true;
fields['name']['name']='name';
fields['name']['type']='text';
fields['name']['val']='';

fields['email']=[];
fields['email']['is_email']=true;
fields['email']['name']='email';
fields['email']['type']='text';
fields['email']['val']='';
*/

         $fields=array();
         foreach($flow_data['vars'] as $field => $values){
           $fields[$field]=array('name'=>$field,'type'=>'text','val'=>'','label'=>$field);
           if(strpos($field,'name')!==false){
             $fields[$field]['is_name']=true;
           }
           if($flow_email_field == $field){
             $fields[$field]['is_email']=true;
           }
         }
         echo json_encode(array('fields'=>$fields,'flowurl'=>get_permalink($push_through_flow_id)));
         die();
    }

    function ajax_pages() {
        $pages = $this->database(false, true, false, false, false, true);

        $templates_array=array();
        $templates = $this->option('templates');
        $pb_templates = json_decode($templates->value);
        $templates_new = $this->get_templates_remote(0);
        $templates_downloadable = array();

        foreach($templates_new as $template_new){
          $templates_downloadable[$template_new->name]=$template_new->imgname;
        }

        foreach($pb_templates as $template_id=>$template_name){
          if(array_key_exists($template_name, $templates_downloadable)){
            $templates_array[$template_id]=array('name'=>$template_name,'thumb'=>$templates_downloadable[$template_name]);
          } else {
            $templates_array[$template_id]=array('name'=>$template_name,'thumb'=>'');
          }
        }

        if ($templates->value == '') {
            $this->option('templates', '{}');
        }


        $pages = (array) $pages;
        $obj = array();
        if (count($pages) > 0) {
            foreach ($pages as $id => $page) {
                $page->title = get_the_title($page->id);
                if ($page->title == '')
                    $page->title = '(no-title : id=' . $page->id . ')';
                //$page->items = stripslashes($page->items);
                if (!is_null(get_post((int) $page->id)))
                    $obj[$page->id] = $page;
            }
        }
        echo '{"pages" : ' . json_encode($obj) . ', "templates" : ' . json_encode($templates_array) . '}';
        die();
    }

    function ajax_import() {
        if (!$_FILES["file"]["error"]) {
            global $current_user;
            $upload = wp_upload_dir();
            $pb_upload_url = $upload['baseurl'] . "/profit_builder/user_upload_" . $current_user->ID . "/";
            $pb_upload_dir = $upload['basedir'] . "/profit_builder/user_upload_" . $current_user->ID . "/";
            $template_url = $pb_upload_url . "templates/";
            $template_dir = $pb_upload_dir . "templates/";
            @rmdir($template_dir);
            if (!@file_exists($template_dir))
                @mkdir($template_dir, 0777, true);
            $tmp_name = $_FILES["file"]["tmp_name"];
            $zip_name = $template_dir . $_FILES["file"]["name"];
            if (move_uploaded_file($tmp_name, $zip_name)) {
                $template = $this->install_template($zip_name);
                echo json_encode($template);
                /* $zip = new ZipArchive();
                  if($zip->open($zip_name) === TRUE) {
                  if($zip->getArchiveComment() == "ProfitBuilder Template"){
                  $unzip_dir = $template_dir."unziped/";
                  @rmdir($unzip_dir);
                  if(!@file_exists($unzip_dir))
                  @mkdir($unzip_dir, 0777, true);
                  $zip->extractTo($unzip_dir);
                  $zip->close();
                  $content = file_get_contents($unzip_dir."content.txt");
                  $settings = explode("\r\n",file_get_contents($unzip_dir."settings.txt"));
                  $image_names = explode("\r\n",file_get_contents($unzip_dir."image_names.txt"));
                  @unlink($unzip_dir."content.txt");
                  @unlink($unzip_dir."settings.txt");
                  @unlink($unzip_dir."image_names.txt");
                  foreach($image_names as $image_name){
                  if(!empty($image_name)){
                  //echo substr($image_name, 0, strripos($image_name, "."));
                  $index = 1;
                  $ext = substr($image_name, strripos($image_name, "."));
                  $imagename = substr($image_name, 0, strripos($image_name, "."));
                  $image_name_new = $image_name;
                  while(@file_exists($upload['path']."/".$image_name_new)){
                  $image_name_new = $imagename."_".$index.$ext;
                  $index++;
                  }
                  @copy($unzip_dir.$image_name, $upload['path']."/".$image_name_new);
                  $filename = $upload['path']."/".$image_name_new;
                  $fileurl = $upload['url'].'/'.basename($filename);
                  $filetype = wp_check_filetype($filename, null );
                  $attachment = array(
                  'guid'           => $fileurl,
                  'post_mime_type' => $filetype['type'],
                  'post_title'     => preg_replace('/\.[^.]+$/','',basename($filename)),
                  'post_content'   => '',
                  'post_status'    => 'inherit'
                  );
                  $attachment_id = wp_insert_attachment($attachment, $filename, 0);
                  if (!is_wp_error($attachment_id)) {
                  require_once(ABSPATH."wp-admin".'/includes/image.php');
                  $attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename);
                  wp_update_attachment_metadata($attachment_id, $attachment_data);
                  $image_url_old = "%siteurl%/".$image_name;
                  $content = preg_replace("[".$image_url_old."]", $fileurl, $content);
                  @unlink($unzip_dir.$image_name);
                  }
                  }
                  }
                  foreach($settings as $setting){
                  if(!empty($setting)){
                  if(substr_count($setting, "name:")>0)
                  $template_name = str_replace("name:", "", $setting);
                  else if(substr_count($setting, "layout:")>0)
                  $template_layout = str_replace("layout:", "", $setting);
                  }
                  }
                  $this->save_templates(array($template_name => addslashes($content)));
                  //echo $content;
                  }
                  } */
            }
        }
        die();
    }

    function ajax_export() {
        $templates = $this->option('templates');
        if ($templates->value == '')
            $this->option('templates', '{}');
        echo '{"templates" : ' . $templates->value . '}';

		die();
    }

    function ajax_export_template() {
        $data = array("result" => "failed");
        if (isset($_GET['id'])) {
            $page = $this->database($_GET['id'], true);
            if ($page) {
                global $current_user;
                $site_url = get_site_url();
                $site_url = substr($site_url, strpos($site_url, ":") + 3);
                $site_url = str_replace("www.", "", $site_url);
                $site_url = trim($site_url, "/");
                $site_url = str_replace("-", "\-", $site_url);


                $content = str_replace('***NEWLINE***','\n',stripslashes(str_replace('\n','***NEWLINE***',$page->items)));

                $meta_keys = array();
                $meta_keys[] = 'pbtheme_revolution';
                $meta_keys[] = 'pbtheme_page_width';
                $meta_keys[] = 'pbtheme_padding';
                $meta_keys[] = 'pbtheme_breadcrumbs';
                $meta_keys[] = 'pbtheme_hidemenu';
                $meta_keys[] = 'pbtheme_hidetopbar';
                $meta_keys[] = 'pbtheme_hidefooter';
                $meta_keys[] = 'pbtheme_hide_featarea';
                $meta_keys[] = 'pbtheme_hide_title';
                $meta_keys[] = 'pbtheme_hidefooter';
                $meta_keys[] = 'pbtheme_hide_meta';
                $meta_keys[] = 'pbtheme_hide_tags';
                $meta_keys[] = 'pbtheme_hide_author';
                $meta_keys[] = 'pbtheme_hide_share';
                $meta_keys[] = 'pbtheme_hide_navigation';
                $meta_keys[] = 'pbtheme_hide_sidebar';
                $meta_keys[] = 'pbtheme_video_override';
                $meta_keys[] = 'pbtheme_video_override_ogg';
                $meta_keys[] = 'pbtheme_video_override_site';
                $meta_keys[] = 'pbtheme_audio_override';
                $meta_keys[] = 'pbtheme_audio_override_ogg';
                $meta_keys[] = 'pbtheme_audio_override_site';
                $meta_keys[] = 'pbtheme_gallery_override';
                $meta_keys[] = 'pbtheme_link_override';
                $meta_keys[] = 'pbtheme_page_bg';
                $meta_keys[] = 'pbtheme_page_image';
                $meta_keys[] = 'pbtheme_pagevideo_mp4';
                $meta_keys[] = 'pbtheme_pagevideo_ogv';
                $meta_keys[] = 'pbtheme_pagevideo_embed';
                $meta_keys[] = 'pbtheme_retargetpixel';
                $meta_keys[] = 'pbtheme_pagevideo_embed_mute';
                $meta_keys[] = 'pbtheme_pagevideo_embed_loop';
                $meta_keys[] = 'pbtheme_pagevideo_embed_hd';
                $meta_keys[] = 'pbtheme_page_vimeo_video';
                $meta_keys[] = 'pbtheme_page_vimeo_video_mute';
                $meta_keys[] = 'pbtheme_page_vimeo_video_loop';



                $pbtheme_settings = array();

                foreach ($meta_keys as $meta_key) {
                  if($meta_value = get_post_meta($_GET['id'], $meta_key, true)){
                    $pbtheme_settings[$meta_key] = stripslashes($meta_value);
                  }
                }


                $items_decoded = json_decode($content);

                $items_decoded->pbtheme_settings = $pbtheme_settings;
                $content = json_encode($items_decoded);


                $pattern = '![http|https]+://[a-zA-Z0-9\-\.]+[' . $site_url . ']+\/[^?#]+\.(?:jpe?g|png|gif)!Ui';
                preg_match_all($pattern, $content, $matches);
                $site_dir = str_replace('\\', '/', dirname(__FILE__));
                while (!@file_exists($site_dir . "/wp-config.php"))
                    $site_dir = substr($site_dir, 0, strripos($site_dir, "/"));
                $template_name = "template" . $_GET['id'];
                $upload = wp_upload_dir();
                $pb_upload_url = $upload['baseurl'] . "/profit_builder/user_upload_" . $current_user->ID . "/";
                $pb_upload_dir = $upload['basedir'] . "/profit_builder/user_upload_" . $current_user->ID . "/";
                $template_url = $pb_upload_url . $template_name . "/";
                $template_dir = $pb_upload_dir . $template_name . "/";
                @rmdir($template_dir);
                if (!@file_exists($template_dir))
                    @mkdir($template_dir, 0777, true);
                if (isset($matches[0]) && is_array($matches[0])) {
                    $imageno = 1;
                    $images = array();
                    $images_url = array();
                    foreach ($matches[0] as $match) {
                        if (!in_array($match, $images_url)) {
                            $images_url[] = $match;
                            $image_url = $match;
                            $image_dir = $site_dir . substr($image_url, strpos($image_url, $site_url) + strlen($site_url));
                            if (@file_exists($image_dir)) {
                                $ext = substr($image_dir, strripos($image_dir, "."));
                                $image_dir_new = $template_dir . "image" . $imageno . $ext;
                                if (@copy($image_dir, $image_dir_new)) {
                                    $image_name = "image" . $imageno . $ext;
                                    $images[$image_name] = $image_dir_new;
                                    $imageno++;
                                    $image_name = "%siteurl%/" . $image_name;
                                    $content = preg_replace("[" . $image_url . "]", $image_name, $content);
                                }
                            }
                        }
                    }







                    file_put_contents($template_dir . "content.txt", $content);
                    $settings = '';
                    $templates = $this->option('templates');
                    if ($templates->value != '') {
                        $templates = json_decode($templates->value, true);
                        $name = @$templates[$_GET['id']];

						if (empty($name)) {
                          $tmp = get_post($_GET['id']);
                          $name = $tmp->post_title;
                        }

                        $settings .= "name:" . $name . "\r\n";
                        $zip_name = $name;
                    }
                    $settings .= "layout:" . $page->layout . "\r\n";
                    file_put_contents($template_dir . "settings.txt", $settings);
                    $image_names = '';
                    require_once 'functions/pclzip.lib.php';
                    $zip_name = str_replace(" ", "_", $zip_name);
                    $zip_name = preg_replace('/[^A-Za-z0-9\_]/', '', $zip_name);
                    $zip_name = str_replace("__", "_", $zip_name);
                    $zip_name = strtolower($zip_name . ".zip");
                    $zipname = $pb_upload_dir . $zip_name;
                    unlink($zipname);
                    $zip = new PclZip($zipname);
                    if ($zip) {
                        //echo $template_dir . "content.txt";
                        //  $addFilesArray = array($template_dir . "content.txt", $template_dir . "settings.txt", $template_dir . "image_names.txt");
                        // $addfileList = implode(',', $addFilesArray);
                        //$zip->create($addfileList, PCLZIP_OPT_REMOVE_PATH, $template_dir);
                        // if (is_string($addfileList)) {
                        //    $zip->create($addfileList, PCLZIP_OPT_REMOVE_PATH, $template_dir);
                        // }
                        //	$AddfilesList = $template_dir."content.txt".','.$template_dir."settings.txt".','.$template_dir."image_names.txt";
                        //	$zip->create($AddfilesList, PCLZIP_OPT_REMOVE_PATH, $template_dir);
                        //	$zip->add($template_dir . "content.txt", PCLZIP_OPT_REMOVE_PATH, $template_dir);
                        //    $zip->add($template_dir . "settings.txt", PCLZIP_OPT_REMOVE_PATH, $template_dir);
                        foreach ($images as $image_name => $image) {
                            //    $zip->add($image, PCLZIP_OPT_REMOVE_PATH, $template_dir);
                            $image_names .= $image_name . "\r\n";
                        }
                        file_put_contents($template_dir . "image_names.txt", $image_names);
                        //    $zip->add($template_dir . "image_names.txt", PCLZIP_OPT_REMOVE_PATH, $template_dir);
                        //$zip->setArchiveComment("ProfitBuilder Template");
                        //$zip->close();
                        $zip->add($template_dir, PCLZIP_OPT_REMOVE_PATH, $template_dir);
                        $data['result'] = "success";
                        $data['fileurl'] = $pb_upload_url . $zip_name;
                    }
                    /* $zip = new ZipArchive();
                      $zip_name = str_replace(" ", "_", $zip_name);
                      $zip_name = preg_replace('/[^A-Za-z0-9\_]/', '', $zip_name);
                      $zip_name = str_replace("__", "_", $zip_name);
                      $zip_name = strtolower($zip_name.".zip");
                      $zipname = $pb_upload_dir.$zip_name;
                      if ($zip->open($zipname, ZIPARCHIVE::CREATE)==TRUE) {
                      $zip->addFile($template_dir."content.txt","content.txt");
                      $zip->addFile($template_dir."settings.txt","settings.txt");
                      foreach($images as $image_name=>$image){
                      $zip->addFile($image,$image_name);
                      $image_names .= $image_name."\r\n";
                      }
                      file_put_contents($template_dir."image_names.txt", $image_names);
                      $zip->addFile($template_dir."image_names.txt","image_names.txt");
                      $zip->setArchiveComment("ProfitBuilder Template");
                      $zip->close();
                      $data['result'] = "success";
                      $data['fileurl'] = $pb_upload_url.$zip_name;
                      } */
                }
            }
        }
        print_r(json_encode($data));
        if (defined('DOING_AJAX') && DOING_AJAX) {
          die();
        }
    }

    function ajax_remove_template() {
        $data = array("result" => "failed");
        if (isset($_POST['id'])) {
            global $wpdb, $current_user;
            $templates = $this->option('templates');
            if ($templates->value != '') {
                $templates = json_decode($templates->value, true);
                $temp_name = $templates[$_POST['id']];
                $table_name = $wpdb->prefix . 'profit_builder_templates';
                $wpdb->delete($table_name, array("temp_name" => $temp_name));
                $this->remove_templates(array($_POST['id']));
                $data["result"] = "success";
            }
        }
        print_r(json_encode($data));
        die();
    }

    function ajax_page_content() {
        if (isset($_GET['id'])) {
            $page = $this->database($_GET['id'], true);
            $html = $this->replace_content('//builder-false', $_GET['id']);
            $items_decoded = json_decode(stripcslashes($page->items));

      			$items_decoded = $this->template_upgrade_v2($items_decoded);

      			$page->items = addslashes(json_encode($items_decoded));
            
            $pbtheme_defaults = array();
            $pbtheme_defaults['pbtheme_revolution']='';
            $pbtheme_defaults['pbtheme_page_width']='';
            $pbtheme_defaults['pbtheme_padding']=0;
            $pbtheme_defaults['pbtheme_breadcrumbs']=0;
            $pbtheme_defaults['pbtheme_hidemenu']=0;
            $pbtheme_defaults['pbtheme_hidetopbar']=0;
            $pbtheme_defaults['pbtheme_hidefooter']=0;
            $pbtheme_defaults['pbtheme_hide_featarea']=0;
            $pbtheme_defaults['pbtheme_hide_title']=0;
            $pbtheme_defaults['pbtheme_hidefooter']=0;
            $pbtheme_defaults['pbtheme_hide_meta']=0;
            $pbtheme_defaults['pbtheme_hide_tags']=0;
            $pbtheme_defaults['pbtheme_hide_author']=0;
            $pbtheme_defaults['pbtheme_hide_share']=0;
            $pbtheme_defaults['pbtheme_hide_navigation']=0;
            $pbtheme_defaults['pbtheme_hide_sidebar']=0;
            $pbtheme_defaults['pbtheme_video_override']='';
            $pbtheme_defaults['pbtheme_video_override_ogg']='';
            $pbtheme_defaults['pbtheme_video_override_site']='';
            $pbtheme_defaults['pbtheme_audio_override']='';
            $pbtheme_defaults['pbtheme_audio_override_ogg']='';
            $pbtheme_defaults['pbtheme_audio_override_site']='';
            $pbtheme_defaults['pbtheme_gallery_override']='';
            $pbtheme_defaults['pbtheme_link_override']='';
            $pbtheme_defaults['pbtheme_page_bg']='';
            $pbtheme_defaults['pbtheme_page_image']='';
            $pbtheme_defaults['pbtheme_pagevideo_mp4']='';
            $pbtheme_defaults['pbtheme_pagevideo_ogv']='';
            $pbtheme_defaults['pbtheme_pagevideo_embed']='';
            $pbtheme_defaults['pbtheme_retargetpixel']='';
            $pbtheme_defaults['pbtheme_pagevideo_embed_mute']=0;
            $pbtheme_defaults['pbtheme_pagevideo_embed_loop']=0;
            $pbtheme_defaults['pbtheme_pagevideo_embed_hd']=0;
            $pbtheme_defaults['pbtheme_page_vimeo_video']='';
            $pbtheme_defaults['pbtheme_page_vimeo_video_mute']='';
            $pbtheme_defaults['pbtheme_page_vimeo_video_loop']='';
            
            
      			if(isset($_GET['post_id']) && isset($items_decoded->pbtheme_settings)){
      				foreach($items_decoded->pbtheme_settings as $pbtheme_option => $value){
                $pbtheme_defaults[$pbtheme_option]=$value;
      				}
              
              foreach($pbtheme_defaults as $pbtheme_option => $value){
                update_post_meta((int)$_GET['post_id'], $pbtheme_option, $value);
              }

              update_post_meta((int)$_GET['post_id'], 'profitbuilder_page_template', 'full-width.php');
              update_post_meta((int)$_GET['post_id'], 'pb_page_width', $items_decoded->pbtheme_settings->pbtheme_page_width);
              update_post_meta((int)$_GET['post_id'], 'pb_page_bg', $items_decoded->pbtheme_settings->pbtheme_page_bg);
              update_post_meta((int)$_GET['post_id'], 'pb_page_image', $items_decoded->pbtheme_settings->pbtheme_page_image);
              update_post_meta((int)$_GET['post_id'], 'pb_pagevideo_mp4', $items_decoded->pbtheme_settings->pbtheme_pagevideo_mp4);
              update_post_meta((int)$_GET['post_id'], 'pb_pagevideo_ogv', $items_decoded->pbtheme_settings->pbtheme_pagevideo_ogv);
              update_post_meta((int)$_GET['post_id'], 'pb_pagevideo_embed', $items_decoded->pbtheme_settings->pbtheme_pagevideo_embed);
              update_post_meta((int)$_GET['post_id'], 'pb_pagevideo_embed_mute', $items_decoded->pbtheme_settings->pbtheme_pagevideo_embed_mute);
              update_post_meta((int)$_GET['post_id'], 'pb_pagevideo_embed_loop', $items_decoded->pbtheme_settings->pbtheme_pagevideo_embed_loop);
              update_post_meta((int)$_GET['post_id'], 'pb_pagevideo_embed_hd', $items_decoded->pbtheme_settings->pbtheme_pagevideo_embed_hd);
              update_post_meta((int)$_GET['post_id'], 'pb_retargetpixel', $items_decoded->pbtheme_settings->pbtheme_retargetpixel);
      			} else if(!isset($items_decoded->pbtheme_settings)){
              delete_post_meta((int)$_GET['post_id'], 'pb_page_bg');
              delete_post_meta((int)$_GET['post_id'], 'pbtheme_page_bg');
            }
      			echo $page->items . '|+break+response+|' . $html;
      			$this->ajax_save_internal((int)$_GET['post_id'],$page->items);
        }
        die();
    }


	function template_upgrade_v2($items_decoded){
		if(!isset($items_decoded->columns)){
				$items_decoded->columns = new stdClass();

				foreach($items_decoded->rowOrder as $rowid){
          if(!$rowid) continue;
					$items_decoded->columns->$rowid = new stdClass();

					$padding_top=0;
					$padding_bot=0;

					if(isset($items_decoded->rows[$rowid]->options->padding_top)){
					  $padding_top = (int)$items_decoded->rows[$rowid]->options->padding_top;
					}
					if(isset($items_decoded->rows[$rowid]->options->padding_top)){
					  $padding_bot = (int)$items_decoded->rows[$rowid]->options->padding_bot;
					}
					$items_decoded->rows[$rowid]->options->margin_padding = '0|0|0|0|'.$padding_top.'|0|'.$padding_bot.'|0';

					//print_r($row->options);

					foreach($items_decoded->rows[$rowid]->columns as $columnid=>$column){
						$items_decoded->columns->$rowid->$columnid = new stdClass();
						$items_decoded->columns->$rowid->$columnid->options = new stdClass();

            if(isset($items_decoded->rows[$rowid]->options->column_back)){
							$items_decoded->columns->$rowid->$columnid->options->back_color=$items_decoded->rows[$rowid]->options->column_back;
						}
						if(isset($items_decoded->rows[$rowid]->options->column_back_opacity)){
							$items_decoded->columns->$rowid->$columnid->options->back_opacity=$items_decoded->rows[$rowid]->options->column_back_opacity;
						} else {
							$items_decoded->columns->$rowid->$columnid->options->back_opacity='100%';
						}

            if(isset($items_decoded->rows[$rowid]->options->column_padding)){
							$column_padding=(int)$items_decoded->rows[$rowid]->options->column_padding;
							$items_decoded->columns->$rowid->$columnid->options->margin_padding='0|0|0|0|'.$column_padding.'|'.$column_padding.'|'.$column_padding.'|'.$column_padding;
						} else {
              $items_decoded->columns->$rowid->$columnid->options->margin_padding='0|0|0|0|0|0|0|0';
            }
					}
				}



				include(dirname(IMSCPB_FILE) . '/functions/shortcode_list.php');
				//Create new v2 settings
				foreach($items_decoded->items as $itemid=>$item){
					if(isset($output[$item->slug])){
						foreach($output[$item->slug]['options'] as $options_group_name=>$options_group){

							foreach($options_group['options'] as $option_id => $option){
								//If new option doesn't exist in old content - add it
								if(!isset($item->options->{$option_id})){
									$items_decoded->items[$itemid]->options->{$option_id} = $option['std'];
								}

                $items_decoded->items[$itemid]->options->margin_padding='0|0|36|0|0|0|0|0';
                $items_decoded->items[$itemid]->options->border='false|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';

								if( isset($items_decoded->items[$itemid]->options->margin_padding) ){
									$margin_padding_array=explode('|',$items_decoded->items[$itemid]->options->margin_padding);

									if(isset($items_decoded->items[$itemid]->options->bot_margin)){
										$margin_padding_array[2]=(int)$items_decoded->items[$itemid]->options->bot_margin;
									}

									if(isset($items_decoded->items[$itemid]->options->v_padding)){
										$margin_padding_array[4]=$margin_padding_array[6]=(int)$items_decoded->items[$itemid]->options->v_padding;
									}

									if(isset($items_decoded->items[$itemid]->options->h_padding)){
										$margin_padding_array[5]=$margin_padding_array[7]=(int)$items_decoded->items[$itemid]->options->h_padding;
									}

									$items_decoded->items[$itemid]->options->margin_padding=implode('|',$margin_padding_array);
								}



								if( $items_decoded->items[$itemid]->slug == 'timer' && $option_id == 'timer_type'){
                  $v1_timer_style=$items_decoded->items[$itemid]->options->timer_style;
                  $v1_timer_type=$items_decoded->items[$itemid]->options->timer_type;

                  if($v1_timer_type == 'flipclock'){
										$items_decoded->items[$itemid]->options->timer_style = 'flip';
										$items_decoded->items[$itemid]->options->flip_color = 'color-dark';
									} else if($v1_timer_type == 'lightfluid') {
										$items_decoded->items[$itemid]->options->timer_style = 'slot';
										$items_decoded->items[$itemid]->options->slot_animation = 'slide';
										$items_decoded->items[$itemid]->options->slot_animation_dir = 'up';
									}

                  if($v1_timer_style == 'EverGreen'){
										$items_decoded->items[$itemid]->options->timer_type = 'evergreen';
									} else if($v1_timer_style == 'Fixed'){
										$items_decoded->items[$itemid]->options->timer_type = 'fixed';
									}
								}

								if( $option_id == 'timer_type' && $items_decoded->items[$itemid]->slug == 'timer' && isset($v1_timer_style) ){
									if($v1_timer_style == 'flipclock'){
										$items_decoded->items[$itemid]->options->timer_style = 'flip';
										$items_decoded->items[$itemid]->options->flip_color = 'color-dark';
									} else if($v1_timer_style == 'lightfluid') {
										$items_decoded->items[$itemid]->options->timer_style = 'slot';
										$items_decoded->items[$itemid]->options->slot_animation = 'slide';
										$items_decoded->items[$itemid]->options->slot_animation_dir = 'up';
									}
								}


								if( $option_id == 'label_color' && $items_decoded->items[$itemid]->slug == 'timer' && isset($items_decoded->items[$itemid]->options->fluid_label_color) ){
									$items_decoded->items[$itemid]->options->label_color = $items_decoded->items[$itemid]->options->fluid_label_color;
								}

								if( $option_id == 'slot_digit_color' && $items_decoded->items[$itemid]->slug == 'timer' && isset($items_decoded->items[$itemid]->options->fluid_font_color) ){
									$items_decoded->items[$itemid]->options->slot_digit_color = $items_decoded->items[$itemid]->options->fluid_font_color;
								}



								//If new option doesn't exist in old content - add it
								if($option_id=='content'){
									$items_decoded->items[$itemid]->options->{$option_id} = str_replace('"','&quot;',$items_decoded->items[$itemid]->options->{$option_id});
								}
							}
						}
					}
				}
			}

		return $items_decoded;
	}


    function ajax_export_html() {
        if (isset($_GET['id'])) {
            $post_status = get_post_status($_GET['id']);
            $post = array();
            $post['ID'] = $_GET['id'];
            $post['post_status'] = 'publish';
            wp_update_post($post);
            $fullpage = wp_remote_get(get_home_url() . "?page_id=" . $_GET['id']);

            $post['post_status'] = $post_status;
            wp_update_post($post);
            $zipfile = $this->ajax_export_html_page($fullpage['body']);
        }
        die();
    }

    function ajax_export_html_page($html) {
        $data = array("result" => "failed");
        if (isset($_GET['id'])) {

            if ($html) {
                global $current_user;
                $site_url = get_site_url();
                $site_url = substr($site_url, strpos($site_url, ":") + 3);
                $site_url = str_replace("www.", "", $site_url);
                $site_url = trim($site_url, "/");
                $site_url = str_replace("-", "\-", $site_url);
                $content = $html;
                $pattern = '%\bhttps?:[^)\'\'"]+\.(?:jpg|jpeg|gif|png)(?![a-z/])%m';

                preg_match_all($pattern, $content, $matches);

                $site_dir = str_replace('\\', '/', dirname(__FILE__));
                while (!@file_exists($site_dir . "/wp-config.php"))
                    $site_dir = substr($site_dir, 0, strripos($site_dir, "/"));
                $template_name = str_replace(" ", "_", html_entity_decode(get_the_title($_GET['id']))) . "-" . $_GET['id'];
                $upload = wp_upload_dir();
                $pb_upload_url = $upload['baseurl'] . "/profit_builder/user_upload_" . $current_user->ID . "/";
                $pb_upload_dir = $upload['basedir'] . "/profit_builder/user_upload_" . $current_user->ID . "/";
                $template_url = $pb_upload_url . $template_name . "/";
                $template_dir = $pb_upload_dir . $template_name . "/";
                @rmdir($template_dir);
                if (!@file_exists($template_dir))
                    @mkdir($template_dir, 0777, true);
                if (isset($matches[0]) && is_array($matches[0])) {
                    $imageno = 1;
                    $images = array();
                    $images_url = array();

                    foreach ($matches[0] as $match) {
                        if (!in_array($match, $images_url)) {
                            $images_url[] = $match;

                            $image_url = stripcslashes($match);

                            $image_dir = $site_dir . substr($image_url, strpos($image_url, $site_url) + strlen($site_url));

                            if (@file_exists($image_dir)) {
                                $ext = substr($image_dir, strripos($image_dir, "."));
                                $imagename = explode("/", $image_dir);
                                $imagename = array_reverse($imagename);
                                $image_dir_new = $template_dir . $imagename[0];
                                if (@copy($image_dir, $image_dir_new)) {
                                    $image_name = $imagename[0];
                                    $images[$image_name] = $image_dir_new;
                                    $imageno++;
                                    $image_name = $image_name;
                                    $content = str_replace($match, $image_name, $content);
                                }
                            }
                        }
                    }
                    file_put_contents($template_dir . "content.html", $content);
                    $settings = '';
                    $zip_name = $template_name;
                    //$settings .= "layout:" . $page->layout . "\r\n";
                    //file_put_contents($template_dir . "settings.txt", $settings);
                    $image_names = '';
                    require_once 'functions/pclzip.lib.php';
                    $zip_name = str_replace(" ", "_", $zip_name);
                    $zip_name = preg_replace('/[^A-Za-z0-9\_]/', '', $zip_name);
                    $zip_name = str_replace("__", "_", $zip_name);
                    $zip_name = strtolower($zip_name . ".zip");
                    $zipname = $pb_upload_dir . $zip_name;
                    unlink($zipname);
                    $zip = new PclZip($zipname);
                    if ($zip) {

                        //$zip->create($addfileList, PCLZIP_OPT_REMOVE_PATH, $template_dir);

                        $zip->add($template_dir, PCLZIP_OPT_REMOVE_PATH, $template_dir);
//                        $zip->add($template_dir . "settings.txt", PCLZIP_OPT_REMOVE_PATH, $template_dir);
//                        foreach ($images as $image_name => $image) {
//                            $zip->add($image, PCLZIP_OPT_REMOVE_PATH, $template_dir);
//                            $image_names .= $image_name . "\r\n";
//                        }
//                        file_put_contents($template_dir . "image_names.txt", $image_names);
//                       // $zip->add($template_dir . "image_names.txt", PCLZIP_OPT_REMOVE_PATH, $template_dir);
//                        //$zip->setArchiveComment("ProfitBuilder Template");
//                        $zip->close();
                        $data['result'] = "success";
                        $data['fileurl'] = $pb_upload_url . $zip_name;
                    }
                }
            }
        }

        print_r(json_encode($data));
        die();
    }

    function ajax_contact_form() {
        $field_email = isset($_POST['defaults'][0]['value']) ? $_POST['defaults'][0]['value'] : 'false email';
        $field_name = isset($_POST['defaults'][1]['value']) ? $_POST['defaults'][1]['value'] : 'false name';
        $field_site = isset($_POST['defaults'][2]['value']) ? $_POST['defaults'][2]['value'] : 'false website';
        $field_custom = (isset($_POST['defaults'][3]['value']) && $_POST['defaults'][3]['name'] == 'custom') ? $_POST['defaults'][3]['value'] : 'false custom';
        $messageDataNum = $_POST['defaults'][3]['name'] == 'custom' ? 4 : 3;
        $field_message = isset($_POST['defaults'][$messageDataNum]['value']) ? $_POST['defaults'][$messageDataNum]['value'] : 'false message';
        $customname = isset($_POST['customname']) ? $_POST['customname'] : 'false info';
        $recipient_name = isset($_POST['recipient_name']) ? $_POST['recipient_name'] : 'false info';
        $recipient_email = isset($_POST['recipient_email']) ? $_POST['recipient_email'] : 'false info';
        $response = esc_attr($_POST['response']);
        $your_name = $recipient_name;
        $your_mail = $recipient_email;
        $your_message = $response . "<br /><br />" . 'This is an auto response, please do not reply.';

        $mail_to = $your_mail;
        $subject = 'Mail from ' . $field_name;
        $body_message = 'Name: ' . $field_name . "<br />";
        $body_message .= 'E-mail: ' . $field_email . "<br />";
        if ($messageDataNum == 4)
            $body_message .= $customname . ': ' . $field_custom . "<br />";
        $body_message .= 'Website: ' . $field_site . "<br />";
        $body_message .= '------------' . "<br /><br />" . stripslashes($field_message);


		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: '. $recipient_name .' <'.get_option('admin_email').'>' . "\r\n";

        $mail_status = mail($mail_to, $subject, $body_message, $headers);

        if ($your_mail != '' && $your_message != '') {
            $subject_v = $response;

			$headers_v = "MIME-Version: 1.0" . "\r\n";
			$headers_v .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers_v .= 'From: '. $recipient_name .' <'.get_option('admin_email').'>' . "\r\n";

            $message_v = $your_message . "<br />";
            mail($field_email, $subject_v, $message_v, $headers_v);
        }
        die();
    }

    function ajax_template_save() {
        if (isset($_POST['name']) && isset($_POST['items'])) {
			$_POST['items']=str_replace('@@@','null',$_POST['items']);
            $tmplArr = array($_POST['name'] => $_POST['items']);
			if(isset($_POST['post_id'])){
				$post_id = (int)$_POST['post_id'];
			} else {
				$post_id = false;
			}
            $this->save_templates(array($_POST['name'] => $_POST['items']),$post_id);
        }
        die();
    }

    function ajax_edit() {
        //header('X-Frame-Options: GOFORIT');
        if ($this->pbuilderEnabled() == 'true')
            require($this->path . '/pages/edit_page.php');
        die();
    }

    function ajax_admin_save() {
        if (array_key_exists('json', $_POST)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'profit_builder_options';
            $rows = $wpdb->get_results('SELECT * FROM ' . $table_name);
            foreach ($_POST['json'] as $option => $value) {
                if ($option == 'memory_limit')
                    $this->set_memory_limit($value);
                $exists = false;
                foreach ($rows as $row) {
                    if ($row->name == $option) {
                        if ($row->value != $value){
							if(!$value) $value="/*delete*/";
                            $this->option($option, $value, array($row));
						}
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $this->option($option, $value, '!exists');
                }
            }
        }
        echo 'success';
        die();
    }

    function ajax_admin_template_install() {
        $data = array("result" => "failed", "template" => "");
        if (array_key_exists('url', $_POST)) {
            global $wpdb;
            $url = urldecode($_POST['url']);
            $template_name = urldecode($_POST['template_name']);
            $template_version = urldecode($_POST['template_version']);
            $template_description = urldecode($_POST['template_description']);
            $template_category = urldecode($_POST['template_category']);
            $template_group = urldecode($_POST['template_group']);
            $template = $this->template_install($url, $template_name, $template_version, 'IMSC Template', $template_category, $template_group);
            $data['result'] = "success";
            $data['template'] = $template;
        }
        //echo 'success';
        echo json_encode($data);
        die();
    }

    function template_install($url, $template_name = '', $template_version = '1.0', $template_description = '', $template_category = '', $template_group = '') {
        global $wpdb;
        $template = array("tempid" => "", "tempname" => "");
		if (extension_loaded("curl")) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $bits = curl_exec($ch);
        } else {
            $bits = @file_get_contents($url);
        }

        $filename = substr($url, strripos($url, "/") + 1);
		$upload_dir = wp_upload_dir();
        if(false !== file_put_contents('../wp-content/uploads/'.$filename, $bits)){
			$template = $this->install_template('../wp-content/uploads/'.$filename, $template_name, $template_description, $template_group, $template_version);
		}

		return $template;
    }

    function ajax_check($id = false) {
        if ($id) {
            $builder = $this->database($id, true);
            return $builder->switch;
        } else if (array_key_exists('p', $_GET)) {
            $builder = $this->database($_GET['p'], true);
            echo $builder->switch;
        }
        die();
    }

    function ajax_switch($ret = false) {
        if (array_key_exists('p', $_GET) && array_key_exists('sw', $_GET)) {
            global $wpdb;
            $content = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = '" . $_GET['p'] . "'", 'ARRAY_A');

			update_post_meta($_GET['p'],'pb_original_content',$content['post_content']);
			if (array_key_exists('post_status', $content) && $content['post_status'] != 'auto-draft') {
                $this->database($_GET['p'], false, $_GET['sw']);
                if ($ret)
                    return;
                echo 'success';
            }
            else {
                echo 'You need to set the title and save post as draft first.';
            }
        } else {
            echo 'You need to set the title and save post as draft first.';
        }
        die();
    }

	function ajax_disablepb($ret = false) {
        if (array_key_exists('p', $_GET)) {
            global $wpdb;
            $original_content = get_post_meta($_GET['p'],'pb_original_content',true);
			if(strlen($original_content)>0){
				$restore_post_content = array('ID'=> $_GET['p'],'post_content' => get_post_meta($_GET['p'],'pb_original_content',true));
				$restore_post_content_result = wp_update_post( $restore_post_content );
				if(is_wp_error($restore_post_content_result)){
					echo 'An error occured while restoring the content.';
				} else {
					$wpdb->get_row('DELETE FROM '.$wpdb->prefix . 'profit_builder_pages'.' WHERE id = "' . $_GET['p'] . '"');
					echo 'success';
				}
			} else {
				echo "Original content not found or page was empty before enabling ProfitBuilder.";
			}
        } else {
            echo 'Could not disable ProfitBuilder';
        }
        die();
    }


    function ajax_copy() {
        global $wpdb, $current_user;
        $table_name = $wpdb->prefix . 'profit_builder_copy_paste';
        if (array_key_exists('copiedoptions', $_POST)) {
            $data = array(
                "userid" => $current_user->ID,
                "copiedtype" => $_POST['copiedtype'],
                "copiedoptions" => $_POST['copiedoptions'],
                "copiedtext" => $_POST['copiedtext'],
                "copieddate" => strtotime("now"),
            );
            $id = (int) $wpdb->get_var("select id from " . $table_name . " where userid=" . $current_user->ID . " and copiedtype='" . $_POST['copiedtype'] . "'");
            if ($id != 0) {
                if ($wpdb->update($table_name, $data, array("id" => $id)))
                    echo 'success';
            }else {
                if ($wpdb->insert($table_name, $data))
                    echo 'success';
            }
        }
        die();
    }

    function ajax_paste() {
        global $wpdb, $current_user;
        $table_name = $wpdb->prefix . 'profit_builder_copy_paste';
        if (array_key_exists('copiedtype', $_POST)) {
            $row = $wpdb->get_row("select * from " . $table_name . " where userid=" . $current_user->ID . " and copiedtype='" . $_POST['copiedtype'] . "'");
            if ($row) {
                $data = array(
                    "id" => $row->id,
                    "userid" => $row->userid,
                    "copiedtype" => $row->copiedtype,
                    "copiedoptions" => stripslashes($row->copiedoptions),
                    "copiedtext" => stripslashes($row->copiedtext),
                    "copieddate" => $row->copieddate,
                    "rowid" => $_POST['rowid'],
                    "modid" => $_POST['modid'],
                );
                print_r(json_encode($data));
            }
        }
        die();
    }

    function save_templates($arr = false, $post_id = false) {
        $templates = $this->option('templates');
        $template = array("tempid" => "", "tempname" => "");
        if ($templates->value == '') {
            $this->option('templates', '{}');
            $tmplObj = array();
        } else {
            $tmplObj = json_decode($templates->value, true);
        }

		if (is_array($arr))
            foreach ($arr as $name => $items) {
    				if($post_id){
    					$meta_keys = array();
    					$meta_keys[] = 'pbtheme_revolution';
    					$meta_keys[] = 'pbtheme_page_width';
    					$meta_keys[] = 'pbtheme_padding';
    					$meta_keys[] = 'pbtheme_breadcrumbs';
    					$meta_keys[] = 'pbtheme_hidemenu';
    					$meta_keys[] = 'pbtheme_hidetopbar';
    					$meta_keys[] = 'pbtheme_hidefooter';
    					$meta_keys[] = 'pbtheme_hide_featarea';
    					$meta_keys[] = 'pbtheme_hide_title';
    					$meta_keys[] = 'pbtheme_hidefooter';
    					$meta_keys[] = 'pbtheme_hide_meta';
    					$meta_keys[] = 'pbtheme_hide_tags';
    					$meta_keys[] = 'pbtheme_hide_author';
    					$meta_keys[] = 'pbtheme_hide_share';
    					$meta_keys[] = 'pbtheme_hide_navigation';
    					$meta_keys[] = 'pbtheme_hide_sidebar';
    					$meta_keys[] = 'pbtheme_video_override';
    					$meta_keys[] = 'pbtheme_video_override_ogg';
    					$meta_keys[] = 'pbtheme_video_override_site';
    					$meta_keys[] = 'pbtheme_audio_override';
    					$meta_keys[] = 'pbtheme_audio_override_ogg';
    					$meta_keys[] = 'pbtheme_audio_override_site';
    					$meta_keys[] = 'pbtheme_gallery_override';
    					$meta_keys[] = 'pbtheme_link_override';
    					$meta_keys[] = 'pbtheme_page_bg';
    					$meta_keys[] = 'pbtheme_page_image';
    					$meta_keys[] = 'pbtheme_pagevideo_mp4';
    					$meta_keys[] = 'pbtheme_pagevideo_ogv';
    					$meta_keys[] = 'pbtheme_pagevideo_embed';
    					$meta_keys[] = 'pbtheme_retargetpixel';
    					$meta_keys[] = 'pbtheme_pagevideo_embed_mute';
    					$meta_keys[] = 'pbtheme_pagevideo_embed_loop';
    					$meta_keys[] = 'pbtheme_pagevideo_embed_hd';
              $meta_keys[] = 'pbtheme_page_vimeo_video';
              $meta_keys[] = 'pbtheme_page_vimeo_video_mute';
              $meta_keys[] = 'pbtheme_page_vimeo_video_loop';

    					$pbtheme_settings = array();

    					foreach ($meta_keys as $meta_key) {
    						if($meta_value = get_post_meta($post_id, $meta_key, true)){
    							$pbtheme_settings[$meta_key] = $meta_value;
    						}
    					}
    					$items_decoded = json_decode(stripslashes($items));
    					$items_decoded->pbtheme_settings = $pbtheme_settings;
    					//print_r($items_decoded);
    					$items = json_encode($items_decoded);
    				}


                $tmplID = array_search($name, $tmplObj);
                if ($tmplID) {
                    $tmplID = (int) $tmplID;
                    $this->database($tmplID, false, false, false, $items);
                    $template["tempid"] = $tmplID;
                    $template["tempname"] = $name;
                } else {
                    $tmplID = 8000000;
                    while (isset($tmplObj['' + $tmplID]))
                        $tmplID++;
                    $this->database($tmplID, false, 'template', 'full width', $items);
                    $tmplObj['' + $tmplID] = $name;
                    $this->option('templates', json_encode($tmplObj));
                    $template["tempid"] = $tmplID;
                    $template["tempname"] = $name;
                }
            }
        return $template;
    }

    function remove_templates($arr = false) {
        $templates = $this->option('templates');
        if ($templates->value == '') {
            $this->option('templates', '{}');
            $tmplObj = array();
        } else {
            $tmplObj = json_decode($templates->value, true);
        }
        if (is_array($arr))
            foreach ($arr as $tmplID) {
                if ($tmplID) {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'profit_builder_pages';
                    $rows = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE id=' . $tmplID . ' AND page_status="publish"');
                    if (count($rows) != 0) {
                        $wpdb->delete($table_name, array("id" => $tmplID));
                        unset($tmplObj['' + $tmplID]);
                        $this->option('templates', json_encode($tmplObj));
                    }
                }
            }
    }

    function set_options($arr = false) {
        if (is_array($arr)) {
            $rows = $this->option();
            foreach ($arr as $key => $val) {
                foreach ($rows as $rkey => $row) {
                    if ($row->name == $key) {
                        $this->option($key, $val, array($row));
                        unset($rows[$rkey]);
                    }
                }
            }
        }
    }

    function option($name = false, $value = false, $rows = false) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'profit_builder_options';
        if (!$rows)
            $rows = $wpdb->get_results('SELECT * FROM ' . $table_name . ($name ? ' WHERE name=\'' . $name . '\'' : ''));
        if ($rows != '!exists' && count($rows) != 0) {
            if ($value) {
				if($value=="/*delete*/"){
					$wpdb->delete( $table_name, array( 'name' => $name ) );
				} else {
					$wpdb->update(
							$table_name, array(
						'value' => $value,
						'name' => $name), array('id' => $rows[0]->id), array(
						'%s',
						'%s'), array('%d')
					);
				}
            } else if (!$name) {
                return $rows;
            } else {
                return $rows[0];
            }
        } else {
            if ($value) {
                $wpdb->insert(
                        $table_name, array(
                    'name' => $name,
                    'value' => $value), array(
                    '%s',
                    '%s')
                );
            } else {
                $output = new stdClass();
                $output->value = '';
                return $output;
            }
        }
    }

    function options($where = false) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'profit_builder_options';
        $rows = $wpdb->get_results('SELECT * FROM ' . $table_name . ($where ? $where : ''));
        if (count($rows) == 0)
            $rows = array();
        return $rows;
    }

    function database($id = false, $get = false, $switch = false, $layout = false, $items = false, $no_content = false) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'profit_builder_pages';
        $rows = $wpdb->get_results('SELECT ' . ($no_content ? 'id' : '*') . ' FROM ' . $table_name . ($id !== false ? ' WHERE id=' . $id . ' AND page_status="publish" ORDER BY page_date DESC LIMIT 1' : ' WHERE page_status="publish" ORDER BY page_date DESC LIMIT 1'));
        if (count($rows) != 0) {
            if ($get) {
                if ($id !== false)
                    return $rows[0];
                else
                    return $rows;
            }
            else {
                if ((!$rows[0]->items || $rows[0]->items == "{}") && $this->option('save_overwrite')->value == 'false') {
                    $post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = '" . $id . "'");
                    if ($post && !empty($post->post_content)) {
                        $post_content = $post->post_content;
                        //$post_content = apply_filters( 'the_content', $post_content );
                        //$post_content = str_replace( ']]>', ']]&gt;', $post_content );
                        $post_content = str_replace('[', "%sqs%", $post_content);
                        $post_content = str_replace(']', "%sqe%", $post_content);
                        $post_content = str_replace("\"", "&quot;", $post_content);
                        $post_content = str_replace("\r\n", "", $post_content);
                        $post_content = str_replace("\r", "", $post_content);
                        $post_content = str_replace("\n", "", $post_content);
                        $post_content = addslashes($post_content);
                        $items = '{\"rows\":[{\"type\":0,\"columns\":[[0]]}],\"rowCount\":1,\"rowOrder\":[0],\"items\":[{\"f\":\"pbuilder_text\",\"slug\":\"text\",\"options\":{\"content\":\"' . $post_content . '\",\"autop\":\"true\",\"text_color\":\"#808080\",\"custom_font_size\":\"false\",\"font_size\":12,\"line_height\":15,\"align\":\"left\",\"bot_margin\":\"24 px\",\"animate\":\"none\",\"animation_delay\":0,\"animation_speed\":1000,\"animation_group\":\"\",\"pbuilder_scid\":\"0\",\"pbuilder_pgid\":660}}]}';
                    }
                }
                $wpdb->update(
                        $table_name, array(
                    'switch' => ($switch ? $switch : $rows[0]->switch),
                    'layout' => ($layout ? $layout : $rows[0]->layout),
                    'items' => ($items ? $items : $rows[0]->items)), array('id' => $id), array(
                    '%s',
                    '%s',
                    '%s'), array('%d')
                );
            }
        } else {
            if ($get) {
                $output = new stdClass();
                $output->items = '{}';
                $output->switch = 'off';
                $output->layout = 'full width';
                return $output;
            } else {
                if (!$items && $this->option('save_overwrite')->value == 'false') {
                    $post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = '" . $id . "'");
                    if ($post && !empty($post->post_content)) {
                        $post_content = $post->post_content;
                        //$post_content = apply_filters( 'the_content', $post_content );
                        //$post_content = str_replace( ']]>', ']]&gt;', $post_content );
                        $post_content = str_replace('[', "%sqs%", $post_content);
                        $post_content = str_replace(']', "%sqe%", $post_content);
                        $post_content = str_replace("\"", "&quot;", $post_content);
                        $post_content = str_replace("\r\n", "", $post_content);
                        $post_content = str_replace("\r", "", $post_content);
                        $post_content = str_replace("\n", "", $post_content);
                        $post_content = addslashes($post_content);
                        $items = '{\"rows\":[{\"type\":0,\"columns\":[[0]]}],\"rowCount\":1,\"rowOrder\":[0],\"items\":[{\"f\":\"pbuilder_text\",\"slug\":\"text\",\"options\":{\"content\":\"' . $post_content . '\",\"autop\":\"true\",\"text_color\":\"#808080\",\"custom_font_size\":\"false\",\"font_size\":12,\"line_height\":15,\"align\":\"left\",\"bot_margin\":\"24 px\",\"animate\":\"none\",\"animation_delay\":0,\"animation_speed\":1000,\"animation_group\":\"\",\"pbuilder_scid\":\"0\",\"pbuilder_pgid\":660}}]}';
                    }
                }
                $wpdb->insert(
                        $table_name, array(
                    'id' => $id,
                    'switch' => ($switch ? $switch : 'on'),
                    'layout' => ($layout ? $layout : 'full width'),
                    'items' => ($items ? $items : '{}')), array(
                    '%d',
                    '%s',
                    '%s',
                    '%s')
                );
            }
        }
    }

    function get_all_template_tags($force = false) {
      $cache = get_transient('pb_template_tags');
      if (!empty($cache) && $force == false) {
        return $cache;
      }

      $response = wp_remote_get('http://wpprofitbuilder.com/templates/?get_pb_template_tags');
      $response = wp_remote_retrieve_body($response);
      if (empty($response)) {
        return array();
      }
      $response = json_decode($response);
      if (empty($response) || !is_array($response)) {
        return array();
      }

      set_transient('pb_template_tags', $response, DAY_IN_SECONDS);
      return $response;
    } // get_all_template_tags

    function get_templates_remote($force = false) {
  	  global $imscpb_lc,$wpdb;

  	  $cache = get_transient('pb_templates');
      if (!empty($cache) && $force == false) {
        return $cache;
      }


      $response = wp_remote_get('http://license1.imsccheck.com/get_templates.php?email='.$imscpb_lc->get_decryptedoption($imscpb_lc->email).'&authkey='.$imscpb_lc->get_decryptedoption($imscpb_lc->authkey).'&domain='.$_SERVER['SERVER_NAME']);
      $response = wp_remote_retrieve_body($response);
      if (empty($response)) {
        return array();
      }
      $response = json_decode($response);
      if (empty($response) || !is_array($response)) {
        return array();
      }



      foreach($response as $template){
        if(strlen($template->zipname)==0){
          $wpdb->query("delete from " . $wpdb->prefix . "profit_builder_templates WHERE temp_name='" . $template->name . "' AND temp_desc='IMSC Template' ");
        }
      }

      set_transient('pb_templates', $response, DAY_IN_SECONDS);

      return $response;
    } // get_templates_remote

    function get_tag_filters_html() {
      $tags = $this->get_all_template_tags(false);
      $out = '';

      $out .= '<div id="filter_templates">

	  <h2 style="float:left;margin:0px;font-size: 20px;">Templates:</h2>';




	  $out .= '<div id="filter_import_export"><span class="dashicons dashicons-welcome-widgets-menus"></span> Import/Export</div>';
    
	  $out .= '<div id="filter-by-wrapper"><span>Filter by:</span> <div id="filter-by-selected">All Templates</div><span class="dashicons dashicons-arrow-down-alt2"></span>
		  <ul id="filter-by-tags">';
      $out .= '<li class="pb-filter-tag-wrapper">';
			$out .= '<input type="checkbox" id="pb-filter-toggle" value="1" class="pb-tag-toggle-all" checked>';
			$out .= '<label for="pb-filter-toggle">Show/Hide All</label>';
			$out .= '</li>';
		  foreach ($tags as $tag) {
      if($tag->slug == 'premium') continue;
			$out .= '<li class="pb-filter-tag-wrapper">';
			$out .= '<input type="checkbox" id="pb-filter-tag-' . $tag->slug . '" value="1" data-tag="pb-' . $tag->slug . '" class="pb-tag-filter" checked>';
			$out .= '<label for="pb-filter-tag-' . $tag->slug . '">' . $tag->name . ' (' . $tag->count . ')</label>';
			$out .= '</li>';
		  }
	  $out .= '</ul>';
	  $out .= '</div>';
    
    $out .= '<div id="filter_import_export" style="margin-right:20px;">show <strong>CLUB</strong> themes <input type="checkbox" id="pb-show-club-themes" value="1" checked></div>';


	  $out .= '<div id="tags_search_container">
            <input type="search" id="tags_search" placeholder="Search Templates" value="">
            </div>';

      $out .= '</div>';
      return $out;
    } // get_tag_filters_html

    function get_templates_list($templates_file = 'list.xml', $only_list = false) {

      $content = '';

      if(isset($_GET['refresh-templates'])){
      	delete_transient('pb_templates');
      }

        $templates_new = $this->get_templates_remote(0);

        global $wpdb, $current_user;
        $table_name = $wpdb->prefix . 'profit_builder_templates';

            ob_start();



            if (!$templates_new) {
                    echo '<li><span>Unable to download templates list. Please contact support.</span></li>';
            } else {

                        echo '<ul class="pbuilder_page_templates">';
                        foreach ($templates_new as $template) {
                            $template_name = $template->name;
                            $id = (int) $wpdb->get_var("select id from " . $table_name . " where temp_name='" . $template_name . "'");
                            $temp_ver = $wpdb->get_var("select temp_ver from " . $table_name . " where temp_name='" . $template_name . "'");
                            $isnew = false;
                            $datereleased = new DateTime($template->datereleased);
                            $datenow = new DateTime("now");
                            $diff = $datenow->diff($datereleased);
                            if ($diff instanceof DateInterval) {

                                if ($diff->y == 0 && $diff->m == 0 && $diff->d < 30)
                                    $isnew = true;
                            }
                            $tags = '';
                            foreach ($template->tags_css as $tag) {
                              $tags .= 'pb-' . $tag . ' ';
                            }
                            ?>
                                <li class="<?php echo $tags; ?> template_wrapper" style="display: none;">
                                    <?php if ($isnew) { ?>
                                        <img src="<?php echo IMSCPB_URL; ?>/images/new_template.png" class="new_template" />
                                    <?php } ?>
                                    <?php if ($template->premium) { ?>
                                        <img src="<?php echo IMSCPB_URL; ?>/images/pro_template.png" class="pro_template" />
                                    <?php } ?>
                                    <div class="template_center">

                                        <?php if(strlen($template->zipname)==0){ echo '<a target="_blank" href="http://wpprofitbuilder.com/members/templates-club-special/">'; } ?>
                                        <img src="<?php echo $template->imgname; ?>" class="template_img" />
                                        <?php if(strlen($template->zipname)==0){ echo '</a>';} ?>
                                    </div>
                                     <div class="template_top">
                                        <span class="template_name"><?php echo $template->name; ?></span>
                                    </div>
                                    <div class="template_bottom">
                                        <span class="template_label">Version&nbsp;<?php echo($temp_ver == '') ? 'Available' : 'Installed'; ?>:</span>
                                        <span class="template_span template_ver"><?php echo ($temp_ver == '') ? $template->version : $temp_ver; ?><?php if ($temp_ver != '' && $template->version != $temp_ver) echo '&nbsp;(Available ' . $template->version . ')'; ?></span><br />
                                        <span class="template_span">Tags: <?php echo implode($template->tags, ', '); ?></span>
                                        <div class="template_bottom_buttons">
                                        <?php if(strlen($template->zipname)>0){ ?>
                                        <a href="javascript:" class="pbuilder_gradient pbuilder_button template_button_install <?php if ($id != 0) echo ' template_installed '; ?>" data-zip="<?php echo $template->zipname; ?>" template-name="<?php echo $template->name; ?>" template-version="<?php echo $template->version; ?>" template-description="<?php echo isset($template->description) ? $template->description : ""; ?>" template-category="<?php echo isset($template->category) ? $template->category : ""; ?>" template-group="">
                                         <?php } else { ?>
                                           <a target="_blank" href="http://wpprofitbuilder.com/members/templates-club-special/" class="pbuilder_gradient pbuilder_button <?php if ($id != 0) echo ' template_installed '; ?>" data-zip="<?php echo $template->zipname; ?>" template-name="<?php echo $template->name; ?>" template-version="<?php echo $template->version; ?>" template-description="<?php echo isset($template->description) ? $template->description : ""; ?>" template-category="<?php echo isset($template->category) ? $template->category : ""; ?>" template-group="">

                                        <?php } ?>
                                            Install<?php if ($id != 0) echo 'ed'; ?>
                                            <?php if ($id != 0) { ?>
                                                <img src="<?php echo $this->url; ?>images/icons/check-green1.png" class="template_button_installed_check"  />
                                            <?php } ?>
                                        </a>
                                        <div class="pbuilder_save_loader"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>
                                        <?php if ($temp_ver != '' && $template->version != $temp_ver) { ?>
                                            <a href="javascript:" class="pbuilder_gradient pbuilder_button template_button_update" data-zip="<?php echo $template->zipname; ?>" template-name="<?php echo $template->name; ?>" template-version="<?php echo $template->version; ?>" template-description="<?php echo isset($template->description) ? $template->description : ""; ?>" template-category="<?php echo isset($template->category) ? $template->category : ""; ?>" template-group="<?php echo $template->group; ?>" style="<?php echo ($id == 0) ? ' display: none; ' : ''; ?>">Update</a><div class="pbuilder_save_loader"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>
                                        <?php } ?>
                                        <a class="pbuilder_gradient pbuilder_button" href="<?php echo $template->preview_url; ?>" target="_blank">Preview</a>
                                    </div>
                                    </div>
                                </li>
                            <?php
                        } // foreach templates
                        echo '</ul>';
                        echo '<br class="clear" />';
                        echo '<a class="button" href="'.admin_url('admin.php?page=profitbuilder_templates&refresh-templates=true').'">Refresh Templates</a>';
                        echo '<br class="clear" />';

                } // has templates

                $content .= ob_get_contents();
                ob_clean();
                return $content;

        }


        function install_template($zip_name, $template_name = '', $template_desc = '', $template_cat = '', $template_ver = '0.1') {
            set_time_limit(0);
            $template = array("tempid" => "", "tempname" => "");
            if (!empty($zip_name)) {
                global $current_user;
                $upload = wp_upload_dir();
                $pb_upload_url = $upload['baseurl'] . "/profit_builder/user_upload_" . $current_user->ID . "/";
                $pb_upload_dir = $upload['basedir'] . "/profit_builder/user_upload_" . $current_user->ID . "/";
                $template_url = $pb_upload_url . "templates/";
                $template_dir = $pb_upload_dir . "templates/";
                @rmdir($template_dir);
                if (!@file_exists($template_dir))
                    @mkdir($template_dir, 0777, true);
                //WP_Filesystem();
                //$unzipfile = unzip_file($zip_name, $unzip_dir);
                require_once 'functions/pclzip.lib.php';
                $archive = new PclZip($zip_name);
                if (@file_exists($zip_name)) {
                    require_once 'functions/pclzip.lib.php';
                    $zip = new PclZip($zip_name);
                    if ($zip) {
                        $unzip_dir = $template_dir . "unziped/";
                        @rmdir($unzip_dir);
                        if (!@file_exists($unzip_dir))
                            @mkdir($unzip_dir, 0777, true);
                        $zip->extract(PCLZIP_OPT_PATH, $unzip_dir);
                        $content = file_get_contents($unzip_dir . "content.txt");
                        $settings = explode("\r\n", file_get_contents($unzip_dir . "settings.txt"));
                        $image_names = explode("\r\n", file_get_contents($unzip_dir . "image_names.txt"));
                        @unlink($unzip_dir . "content.txt");
                        @unlink($unzip_dir . "settings.txt");
                        @unlink($unzip_dir . "image_names.txt");
                        foreach ($image_names as $image_name) {
                            if (!empty($image_name)) {
                                //echo substr($image_name, 0, strripos($image_name, "."));
                                $index = 1;
                                $ext = substr($image_name, strripos($image_name, "."));
                                $imagename = substr($image_name, 0, strripos($image_name, "."));
                                $image_name_new = $image_name;
                                while (@file_exists($upload['path'] . "/" . $image_name_new)) {
                                    $image_name_new = $imagename . "_" . $index . $ext;
                                    $index++;
                                }
                                @copy($unzip_dir . $image_name, $upload['path'] . "/" . $image_name_new);
                                $filename = $upload['path'] . "/" . $image_name_new;
                                $fileurl = $upload['url'] . '/' . basename($filename);
                                $filetype = wp_check_filetype($filename, null);
                                $attachment = array(
                                    'guid' => $fileurl,
                                    'post_mime_type' => $filetype['type'],
                                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                                    'post_content' => '',
                                    'post_status' => 'inherit'
                                );
                                $attachment_id = wp_insert_attachment($attachment, $filename, 0);
                                if (!is_wp_error($attachment_id)) {
                                    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                                    $attachment_data = wp_generate_attachment_metadata($attachment_id, $filename);
                                    wp_update_attachment_metadata($attachment_id, $attachment_data);
                                    $image_url_old = "%siteurl%/" . $image_name;
                                    $content = preg_replace("[" . $image_url_old . "]", $fileurl, $content);
                                    @unlink($unzip_dir . $image_name);
                                }
                            }
                        }
                        foreach ($settings as $setting) {
                            if (!empty($setting)) {
                                if (substr_count($setting, "name:") > 0) {
                                    if ($template_name == '')
                                        $template_name = str_replace("name:", "", $setting);
                                }else if (substr_count($setting, "layout:") > 0) {
                                    $template_layout = str_replace("layout:", "", $setting);
                                }
                            }
                        }
                        $template = $this->save_templates(array($template_name => addslashes($content)));
                        global $wpdb, $current_user;
                        $table_name = $wpdb->prefix . 'profit_builder_templates';
                        $data = array(
                            "userid" => $current_user->ID,
                            "temp_name" => $template_name,
                            "temp_desc" => $template_desc,
                            "temp_cat" => $template_cat,
                            "temp_ver" => $template_ver,
                            "temp_date" => strtotime("now"),
                        );
                        $id = (int) $wpdb->get_var("select id from " . $table_name . " where temp_name='" . $template_name . "'");
                        if ($id != 0)
                            $wpdb->update($table_name, $data, array("id" => $id));
                        else
                            $wpdb->insert($table_name, $data);
                    }

                }
            }
            return $template;
        }

        function get_languages() {
            $languages = array(
                'af_ZA' => 'Afrikaans',
                'sq_AL' => 'Albanian',
                'ar_AR' => 'Arabic',
                'hy_AM' => 'Armenian',
                'ay_BO' => 'Aymara',
                'az_AZ' => 'Azeri',
                'eu_ES' => 'Basque',
                'be_BY' => 'Belarusian',
                'bn_IN' => 'Bengali',
                'bs_BA' => 'Bosnian',
                'bg_BG' => 'Bulgarian',
                'ca_ES' => 'Catalan',
                'ck_US' => 'Cherokee',
                'hr_HR' => 'Croatian',
                'cs_CZ' => 'Czech',
                'da_DK' => 'Danish',
                'nl_BE' => 'Dutch (Belgi&euml;)',
                'nl_NL' => 'Dutch',
                'en_PI' => 'English (Pirate)',
                'en_GB' => 'English (UK)',
                'en_US' => 'English (US)',
                'en_UD' => 'English (Upside Down)',
                'eo_EO' => 'Esperanto',
                'et_EE' => 'Estonian',
                'fo_FO' => 'Faroese',
                'tl_PH' => 'Filipino',
                'fb_FI' => 'Finnish (test)',
                'fi_FI' => 'Finnish',
                'fr_CA' => 'French (Canada)',
                'fr_FR' => 'French (France)',
                'gl_ES' => 'Galician',
                'ka_GE' => 'Georgian',
                'de_DE' => 'German',
                'el_GR' => 'Greek',
                'gn_PY' => 'Guaran&iacute;',
                'gu_IN' => 'Gujarati',
                'he_IL' => 'Hebrew',
                'hi_IN' => 'Hindi',
                'hu_HU' => 'Hungarian',
                'is_IS' => 'Icelandic',
                'id_ID' => 'Indonesian',
                'ga_IE' => 'Irish',
                'it_IT' => 'Italian',
                'ja_JP' => 'Japanese',
                'jv_ID' => 'Javanese',
                'kn_IN' => 'Kannada',
                'kk_KZ' => 'Kazakh',
                'km_KH' => 'Khmer',
                'tl_ST' => 'Klingon',
                'ko_KR' => 'Korean',
                'ku_TR' => 'Kurdish',
                'la_VA' => 'Latin',
                'lv_LV' => 'Latvian',
                'fb_LT' => 'Leet Speak',
                'li_NL' => 'Limburgish',
                'lt_LT' => 'Lithuanian',
                'mk_MK' => 'Macedonian',
                'mg_MG' => 'Malagasy',
                'ms_MY' => 'Malay',
                'ml_IN' => 'Malayalam',
                'mt_MT' => 'Maltese',
                'mr_IN' => 'Marathi',
                'mn_MN' => 'Mongolian',
                'ne_NP' => 'Nepali',
                'se_NO' => 'Northern S&aacute;mi',
                'nb_NO' => 'Norwegian (bokmal)',
                'nn_NO' => 'Norwegian (nynorsk)',
                'ps_AF' => 'Pashto',
                'fa_IR' => 'Persian',
                'pl_PL' => 'Polish',
                'pt_BR' => 'Portuguese (Brazil)',
                'pt_PT' => 'Portuguese (Portugal)',
                'pa_IN' => 'Punjabi',
                'qu_PE' => 'Quechua',
                'ro_RO' => 'Romanian',
                'rm_CH' => 'Romansh',
                'ru_RU' => 'Russian',
                'sa_IN' => 'Sanskrit',
                'sr_RS' => 'Serbian',
                'zh_CN' => 'Simplified Chinese (China)',
                'sk_SK' => 'Slovak',
                'sl_SI' => 'Slovenian',
                'so_SO' => 'Somali',
                'es_CL' => 'Spanish (Chile)',
                'es_CO' => 'Spanish (Colombia)',
                'es_MX' => 'Spanish (Mexico)',
                'es_ES' => 'Spanish (Spain)',
                'es_VE' => 'Spanish (Venezuela)',
                'es_LA' => 'Spanish',
                'sw_KE' => 'Swahili',
                'sv_SE' => 'Swedish',
                'sy_SY' => 'Syriac',
                'tg_TJ' => 'Tajik',
                'ta_IN' => 'Tamil',
                'tt_RU' => 'Tatar',
                'te_IN' => 'Telugu',
                'th_TH' => 'Thai',
                'zh_HK' => 'Traditional Chinese (Hong Kong)',
                'zh_TW' => 'Traditional Chinese (Taiwan)',
                'tr_TR' => 'Turkish',
                'uk_UA' => 'Ukrainian',
                'ur_PK' => 'Urdu',
                'uz_UZ' => 'Uzbek',
                'vi_VN' => 'Vietnamese',
                'cy_GB' => 'Welsh',
                'xh_ZA' => 'Xhosa',
                'yi_DE' => 'Yiddish',
                'zu_ZA' => 'Zulu'
            );
            return $languages;
        }

        function ajax_pbuilder_clone_post() {
            $id = isset($_GET['p']) ? $_GET['p'] : $_POST['p'];
            $post = get_post($id);
            $status = isset($_GET['s']) ? $_GET['s'] : $_POST['s'];
            if (isset($post) && $post != null) {
                $new_id = $this->create_duplicate_post($post, $status);
                wp_redirect(admin_url('post.php?action=edit&post=' . $new_id));
            }
            die();
        }

        function create_duplicate_post($post, $status = '', $parent_id = '') {
            global $current_user;
            if ($post->post_type == 'revision')
                return;
            $new_post = array(
                'menu_order' => $post->menu_order,
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_author' => $current_user->ID,
                'post_content' => $post->post_content,
                'post_excerpt' => $post->post_excerpt,
                'post_mime_type' => $post->post_mime_type,
                'post_parent' => $new_post_parent = empty($parent_id) ? $post->post_parent : $parent_id,
                'post_password' => $post->post_password,
                'post_status' => $new_post_status = (empty($status)) ? $post->post_status : $status,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'post_date' => $new_post_date = $post->post_date,
                'post_date_gmt' => get_gmt_from_date($new_post_date),
            );
            $new_post_id = wp_insert_post($new_post);
            if ($new_post_status == 'publish' || $new_post_status == 'future') {
                $post_name = wp_unique_post_slug($post->post_name, $new_post_id, $new_post_status, $post->post_type, $new_post_parent);
                $new_post = array();
                $new_post['ID'] = $new_post_id;
                $new_post['post_name'] = $post_name;
                wp_update_post($new_post);
            }
            //if ($post->post_type == 'page' || (function_exists('is_post_type_hierarchical') && is_post_type_hierarchical( $post->post_type ))){
            $this->copy_post_taxonomies($new_post_id, $post);
            $this->copy_post_meta($new_post_id, $post);
            $this->copy_post_children($new_post_id, $post);
            $this->copy_post_pb_data($new_post_id, $post);
            //}
            //do_action( 'dp_duplicate_page', $new_post_id, $post );
            //else
            //do_action( 'dp_duplicate_post', $new_post_id, $post );
            return $new_post_id;
        }

        function copy_post_taxonomies($new_id, $post) {
            global $wpdb;
            if (isset($wpdb->terms)) {
                wp_set_object_terms($new_id, NULL, 'category');
                $taxonomies = get_object_taxonomies($post->post_type);
                foreach ($taxonomies as $taxonomy) {
                    $post_terms = wp_get_object_terms($post->ID, $taxonomy, array('orderby' => 'term_order'));
                    $terms = array();
                    for ($i = 0; $i < count($post_terms); $i++) {
                        $terms[] = $post_terms[$i]->slug;
                    }
                    wp_set_object_terms($new_id, $terms, $taxonomy);
                }
            }
        }

        function copy_post_meta($new_id, $post) {
            $meta_keys = get_post_custom_keys($post->ID);
            if (empty($meta_keys))
                return;
            foreach ($meta_keys as $meta_key) {
                $meta_values = get_post_custom_values($meta_key, $post->ID);
                foreach ($meta_values as $meta_value) {
                    $meta_value = maybe_unserialize($meta_value);
                    add_post_meta($new_id, $meta_key, $meta_value, true);
                }
            }
        }

        function copy_post_children($new_id, $post) {
            $children = get_posts(array('post_type' => 'any', 'numberposts' => -1, 'post_status' => 'any', 'post_parent' => $post->ID));
            foreach ($children as $child) {
                $this->create_duplicate_post($child, '', $new_id);
            }
        }

        function copy_post_pb_data($new_id, $post) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'profit_builder_pages';
            $sql = 'SELECT * FROM ' . $table_name . " where id=" . $post->ID. " AND page_status='publish'  ORDER BY page_date DESC";
            $row = $wpdb->get_row($sql);
            if ($row) {
                $data = array(
                    'id' => $new_id,
                    'switch' => $row->switch,
                    'layout' => $row->layout,
                    'items' => $row->items,
                    'page_status' => 'publish',
                );
                $wpdb->insert($table_name, $data);
            }
        }

        function admin_update_notice() {
            $option_name = 'external_updates-profit-builder';
            $state = get_site_option($option_name, null);
            if (!empty($state) && is_object($state) && isset($state->checkedVersion) && isset($state->update->version) && version_compare($state->checkedVersion, $state->update->version, '<')) {
                /* $logurl = wp_nonce_url(
                  add_query_arg(
                  array(
                  'tab' => 'plugin-information',
                  'plugin' => 'profit-builder',
                  'section' => 'changelog',
                  'TB_iframe' => 'true',
                  'width' => '772',
                  'height' => '405'
                  ),
                  is_network_admin() ? network_admin_url('plugin-install.php') : admin_url('plugin-install.php')
                  )
                  );
                  $updateurl = wp_nonce_url(
                  add_query_arg(
                  array(
                  'action' => 'upgrade-plugin',
                  'plugin' => 'profit_builder/profit_builder.php',
                  ),
                  is_network_admin() ? network_admin_url('update.php') : admin_url('update.php')
                  ),
                  'upgrade-plugin_profit_builder/profit_builder.php'
                  ); */
                $file = 'profit_builder/profit_builder.php';
                $slug = 'profit-builder';
                $details_url = self_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $slug . '&section=changelog&TB_iframe=true&width=600&height=800');
                $updateurl = wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file);
                echo '<div id="update-nag">';
                printf('<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.', 'ProfitBuilder', $state->update->version, $details_url, 'ProfitBuilder', $updateurl, ''
                );
                echo '</div>';
                //$res = apply_filters( 'upgrader_post_install', true, $args['hook_extra'], $this->result );
            }
        }

        function pb_bg_admin_print_footer_scripts() {
            global $pagenow;
            if ($pagenow == 'media-upload.php' && isset($_GET['pb_bg'])) {
                ?>
            <script type="text/javascript">
                var j = jQuery;
                var pb_bg = '<?php echo isset($_GET['pb_bg']) ? 'true' : 'false'; ?>';
                //j(document).ready(function(){
                j(".savesend .button").on('click', function (e) {
                    if (pb_bg == 'true') {
                        e.preventDefault();
                        var $this = j(this);
                        var image_url = $this.closest('tbody').find('.url .field .urlfield').val();
                        j(window.parent.document).find('#pb-page-image').val(image_url);
                        window.parent.tb_remove();
                    }
                });
                if (pb_bg == 'true')
                    j("#go_button").removeAttr('onclick');
                j("#go_button").on('click', function (e) {
                    if (pb_bg == 'true') {
                        e.preventDefault();
                        var $this = j(this);
                        var image_url = $this.closest('tbody').find('#src').val();
                        j(window.parent.document).find('#pb-page-image').val(image_url);
                        window.parent.tb_remove();
                    }
                });
                //});
            </script>
            <?php
        }
    }

    function pb_bg_admin_init() {
        global $pagenow;
        if ($pagenow == 'async-upload.php' && isset($_POST['fetch'])) {
            ?>
            <script type="text/javascript">
                //var j = jQuery;
                //var pb_bg = '<?php echo isset($_GET['pb_bg']) ? 'true' : 'false'; ?>';
                //j(document).ready(function(){
                j(".savesend .button").on('click', function (e) {
                    if (pb_bg == 'true') {
                        e.preventDefault();
                        var $this = j(this);
                        var image_url = $this.closest('tbody').find('.url .field .urlfield').val();
                        j(window.parent.document).find('#pb-page-image').val(image_url);
                        window.parent.tb_remove();
                    }
                });
                //});
            </script>
            <?php
        }
    }



    function check_leadpages() {
        global $post;
        $remove_parse_request = false;
        $content = '';
        $locked = false;
        $id = false;
        if (is_admin() && isset($_GET['action']) && $_GET['action'] == 'pbuilder_edit' && isset($_GET['p'])) {
            $id = $_GET['p'];
        } else if (!is_admin() && isset($_GET['page_id'])) {
            $id = $_GET['page_id'];
        } else if (!is_admin() && !isset($_GET['page_id'])) {
            $id = url_to_postid("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
        }
//        echo "ok123".print_r($post, true).url_to_postid( "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ).get_permalink();
//        global $leadpages_instance;
//                if($leadpages_instance) remove_action('parse_request', array(&$leadpages_instance, 'check_root'), 1);
        $output = '';
        if ($id && $post) {
            $content = $post->post_content;
            $locked = post_password_required($id);
            $builder = $this->database($id, true);
            $remove_parse_request = true;
            if ($content == '//builder-false' || ($builder->switch == 'on' && !$locked)) {
                global $leadpages_instance;
                if ($leadpages_instance)
                    remove_action('parse_request', array(&$leadpages_instance, 'check_root'), 1);
            }
        }
    }

    function imscpb_sort($items, $sortkey, $asc = true) {
        $unsorted = array();
        $sorted = array();
        foreach ($items as $key => $value) {
            $unsorted[$key] = strtolower($value[$sortkey]);
        }
        if ($asc)
            asort($unsorted);
        else
            arsort($unsorted);
        foreach ($unsorted as $key => $val) {
            $sorted[] = $items[$key];
        }
        return $sorted;
    }

    function pbuilderEnabled() {
        global $current_user;
        $role = array_shift($current_user->roles);
        $allowed_roles = array('administrator', 'editor', 'author', 'contributor');
        $pbuilderEnabled = 'false';
        if (in_array($role, $allowed_roles)) {
            $pbuilderEnabled = $this->option('enable_editor_for_' . $role);
            $pbuilderEnabled = empty($pbuilderEnabled->value) ? 'true' : $pbuilderEnabled->value;
        }
        return $pbuilderEnabled;
    }

    function wp_print_footer_scripts() {
        $force_cufon_override = $this->option('force_cufon_override');
        if ($force_cufon_override->value == 'true') {
            echo '<script type="text/javascript">if(typeof removecufon == "function") removecufon();</script>';
        }
    }

    function set_memory_limit($NewLimit = null) {
        if (!function_exists('ini_set'))
            return;
        if (!function_exists('ini_get'))
            return;
        $OldLimit = (int) ini_get('memory_limit');
        $NewLimit = (int) ($NewLimit ? $NewLimit : $this->option("memory_limit")->value);
        $NewLimit = $NewLimit < 96 ? 96 : $NewLimit;
        if ($NewLimit > $OldLimit && $NewLimit > 0 && is_admin()) {
            ini_set('memory_limit', $NewLimit . 'M');
        }
    }

    function ajax_admin_fonts() {
        header('Content-Type: application/javascript');
        echo '
        var fontsStd = ' . json_encode($this->standard_fonts) . ';
        var fontsVar = ' . json_encode($this->standard_fonts_variants) . ';
        var fontsObj = ' . $this->get_google_fonts(true) . ';
        ';
        die;
    }

	function pbso_stats_column( $column, $post_id ) {


    if($column!='so-stats'){
		  return;
	  }
	  $is_ab_test=false;
    $is_ab_parent=false;
	  $test_total_views=0;
	  $test_unique_views=0;
	  $test_total_conversion=0;
	  $page_stats=array();
	  $daily_unique_views=array();
	  $daily_conversions=array();
	  for($i=-20;$i<=0;$i++){
		$daily_unique_views[date('Y-m-d',time()+$i*DAY_IN_SECONDS)]=0;
		$daily_conversions[date('Y-m-d',time()+$i*DAY_IN_SECONDS)]=0;
	  }

	  if($so_split_test_settings=get_post_meta($post_id,'so_split_test_settings',true)){
		 $is_ab_test=true;
     $is_ab_parent=true;
		 foreach($so_split_test_settings['pages'] as $page_id){
      $page_stats = $this->get_ab_test_stats($page_id);
      $daily_conversions = $page_stats['days'];
      $test_total_views+=$page_stats['totals']['views'];
      $test_unique_views+=$page_stats['totals']['views_unique'];
      $test_total_conversion+=$page_stats['totals']['conversions'];

		 }
	  } else if($so_split_test=get_post_meta($post_id,'so_split_test',true)){
      $so_split_test_settings=get_post_meta($so_split_test,'so_split_test_settings',true);
      $is_ab_test=true;
		  $page_stats = $this->get_ab_test_stats($post_id);
      $daily_conversions = $page_stats['days'];
      $test_total_views=$page_stats['totals']['views'];
      $test_unique_views=$page_stats['totals']['views_unique'];
      $test_total_conversion=$page_stats['totals']['conversions'];
	  }

	  if($is_ab_test){
    /*
		 echo '<div class="so-test-info">';
		 if(max($daily_conversions)>0){
		   echo '<img title="Conversions '.key( array_slice( $daily_conversions, 0, 1, TRUE ) ).' to '.key( array_slice( $daily_conversions, -1, 1, TRUE ) ).'" src="http://chart.apis.google.com/chart?chs=90x30&cht=ls&chf=bg,s,FFFFFF00&chco=0077CC&chd=t:' . implode(',',$daily_conversions) . '&chds=' . min($daily_conversions).','.max($daily_conversions) . '" width="90" height="30">';
		 } else {
		   echo 'No conversions';
		 }
		 echo '</div>';

		 echo '<div class="so-test-info">';
		 if(max($daily_unique_views)>0){
		   echo '<img title="Unique Views '.key( array_slice( $daily_unique_views, 0, 1, TRUE ) ).' to '.key( array_slice( $daily_unique_views, -1, 1, TRUE ) ).'" src="http://chart.apis.google.com/chart?chs=90x30&cht=ls&chf=bg,s,FFFFFF00&chco=0077CC&chd=t:' . implode(',',$daily_unique_views) . '&chds=' . min($daily_unique_views).','.max($daily_unique_views) . '" width="90" height="30">';
		 } else {
		   echo 'No visits';
		 }
		 echo '</div>';

		 echo '<div class="so-test-info-text">';
		 if($test_total_conversion>0){
			echo round($test_total_conversion/$test_total_views*100);
		 } else {
			 echo '0';
		 }
		 echo '% conversion rate on '.$test_unique_views.' unique visits</h2>';
     */

     if(!$is_ab_parent){
       echo '<img src="'.IMSCPB_URL.'/images/icons/ab_child.png" style="vertical-align:top;" /> ';
     }


     $last_page = $so_split_test_settings['pages'][count($so_split_test_settings['pages'])-1];


     if(!isset($_GET['orderby'])){
       if(!$is_ab_parent && $so_split_test_settings['winner']>0 && $so_split_test_settings['winner'] == $post_id){
          echo '<style>#post-'.$post_id.'{background-color:#dbe7d3;}</style>';
       } else if(!$is_ab_parent && $so_split_test_settings['winner']>0){
         echo '<style>#post-'.$post_id.'{background-color:#fbebea;}</style>';
       }
     }

     $class="so_ab_test_stats";
     if($is_ab_parent){ $class="so_ab_test_stats_parent"; }

     echo '<div class="'.$class.'">';
     echo '<i class="pbuilder_tooltip pbso_total_views fa fa-users"></i> '.$this->numberAbbreviation($test_total_views);
     echo '<i class="pbuilder_tooltip pbso_unique_views fa fa-user"></i> '.$this->numberAbbreviation($test_unique_views);
     echo '<i class="pbuilder_tooltip pbso_conversions fa fa-envelope" aria-hidden="true"></i> '.$this->numberAbbreviation($test_total_conversion);
     echo '<i class="pbuilder_tooltip pbso_conversion_percent fa fa-check-square" aria-hidden="true"></i> '.($test_total_views>0?$this->numberAbbreviation(round($test_total_conversion/$test_total_views*100)):0).'%';
     echo '</div>';

     if(max($daily_conversions)>0){
		   echo '<img style=" display:inline-block; vertical-align: middle;" title="Conversions '.key( array_slice( $daily_conversions, 0, 1, TRUE ) ).' to '.key( array_slice( $daily_conversions, -1, 1, TRUE ) ).'" src="http://chart.apis.google.com/chart?chs=90x30&cht=ls&chf=bg,s,FFFFFF00&chco=0077CC&chm=B,e5f2fa,0,0,0&chd=t:' . implode(',',$daily_conversions) . '&chds=' . min($daily_conversions).','.max($daily_conversions) . '" width="90" height="30">';
		 } else {
		   echo 'No conversions';
		 }

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
      }';

      if(!isset($_GET['orderby'])){
          if($is_ab_parent){
            echo '#post-'.$post_id.'{border-top:4px solid #007ab5;} .wp-list-table{border-collapse: collapse;}';
          }

          else if($last_page == $post_id){
            echo '#post-'.$post_id.'{border-bottom:4px solid #007ab5;} .wp-list-table{border-collapse: collapse;}';
          }


      }
     echo '</style>';

     if($so_split_test_settings['winner'] == $post_id){
        echo '<img src="'.IMSCPB_URL.'/images/icons/ab_winner.png" style="vertical-align:top; margin-left:10px;" /> ';
     }
   }

	}

	function pbso_add_sticky_column( $columns ) {
	  $columns_new = array_slice($columns, 0, 2, true) +
	  array('so-stats' => 'PB A/B Test Stats') +
	  array_slice($columns, 2, count($columns) - 1, true) ;

	  return $columns_new;

	}

	function pbso_row_actions($actions, $post) {
    $pb_styles='';
    if($so_split_test=get_post_meta($post->ID,'so_split_test_settings',true)){
		  $pb_styles.='#post-'.$post->ID.' .row-title:after{content:" Test";color: #585858; background:url('.IMSCPB_URL.'/images/icons/ab.png) no-repeat; display: inline-block; width: 100px; padding-left:34px; height: 24px;color: #585858; margin-left: 8px;}';
	  }

    $pb_funnel = get_post_meta($post->ID,'profit_builder_funnel',true);
    if( $pb_funnel !== false && $pb_funnel!='' ){
      $pb_styles.='#post-'.$post->ID.' .row-title:before{content:""; display: inline-block;width: 24px;height: 24px;color: #585858;margin-top: 0;vertical-align: middle;} #post-'.$post->ID.' .column-title{position:relative;}';
      echo '<a style="position:absolute;top: 6px;left: 8px;" href="'.admin_url('admin.php?page=profitbuilder_funnels#funnel_'.$pb_funnel).'"><img src="'.IMSCPB_URL.'/images/icons/funnel.png" /></a>';
    }


	  if($so_split_test=get_post_meta($post->ID,'so_split_test',true)){
		  $so_split_test_settings=get_post_meta($so_split_test,'so_split_test_settings',true);
		  $page_id=array_search($post->ID,$so_split_test_settings['pages']);
		  $pb_styles.='#post-'.$post->ID.' .row-title:after{content:" '.(isset($_GET['order'])?get_the_title($so_split_test):'').' - Variation #'.($page_id+1).'";color: #585858;}';
	  }

    if(strlen($pb_styles)>0){
      $actions['edit'] = $actions['edit'].'<style>'.$pb_styles.'</style>';
    }
	  return $actions;
	}

	function pbso_front_js(){
	   global $post;
	   ?>
	   <script>
	   jQuery(document).ready(function(e) {
		 var so_continue_href='';
		 jQuery('a').each(function(index, element) {
			if(jQuery(this).hasClass('so-track') || jQuery(this).data('track')==1 ){
				jQuery(this).on('click',function(e){
				   //console.log('a click');
				   trackconversion();
				   so_continue_href=jQuery(this).attr('href');
				   return false;
				});
			}
		 });

		 function trackconversion(){
			  jQuery.ajax({
				type: 'POST',
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				data:{action:'so_add_conversion',so_page:<?php echo $post->ID; ?> },
				dataType: 'json'}
			  ).done(function(response) {
				location.href=so_continue_href;
			  });
		 }

	   });
	   </script>
	   <?php
	}

	function pbso_sort_stats_conversions($a, $b) {
	  return $b['total_conversions'] - $a['total_conversions'];
	}

	function pbso_sort_stats_conversion_rate($a, $b) {
	  return $b['conversion_rate'] - $a['conversion_rate'];
	}



	function pbso_check_for_winner($so_split_test_settings,$test_page){
	   $winner_id=false;
	   $test_ended=false;
	   $test_total_views=0;
	   $test_total_conversion=0;
	   $page_stats=array();

	   foreach($so_split_test_settings['pages'] as $page_id){
		  $total_views=get_post_meta($page_id,'so_split_test_views',true);
		  $total_views=array_sum($total_views);
		  $test_total_views+=$total_views;
		  $unique_views=get_post_meta($page_id,'so_split_test_unique_views',true);
		  $unique_views=array_sum($unique_views);
		  $total_conversions=get_post_meta($page_id,'so_split_test_conversions',true);
		  $total_conversions=array_sum($total_conversions);
		  $test_total_conversion+=$total_conversions;

		  $page_stats[$page_id]=array('total_views'=>$total_views,'unique_views'=>$unique_views,'total_conversions'=>$total_conversions,'conversion_rate'=>($total_conversions>0?$total_conversions/$total_views:0));
	   }


	   if( $so_split_test_settings['end_type']=='days' && time()>($so_split_test_settings['start_time']+$so_split_test_settings['end_value']*DAY_IN_SECONDS) ){
		  $test_ended=true;
	   } else if( $so_split_test_settings['end_type']=='views' && $test_total_views>=$so_split_test_settings['end_value'] ){
		  $test_ended=true;
	   } else if( $so_split_test_settings['end_type']=='conversions' && $test_total_conversion>=$so_split_test_settings['end_value'] ){
		  $test_ended=true;
	   }


	   if( $test_ended ){
		   if($so_split_test_settings['end_winner']=='rate'){
			  uasort($page_stats, array(&$this,'pbso_sort_stats_conversion_rate'));
			  $winner_id=current(array_keys($page_stats));
		   } else if($so_split_test_settings['end_winner']=='conversions'){
			  uasort($page_stats, array(&$this,'pbso_sort_stats_conversions'));
			  $winner_id=current(array_keys($page_stats));
		   }
		   $so_split_test_settings['winner']=$winner_id;
		   update_post_meta($test_page,'so_split_test_settings',$so_split_test_settings);
		   return $winner_id;
		}

	}

	function pbso_track_test(){
    global $wpdb,$post;
    $ab_debug=false;
    if($so_split_test=get_post_meta($post->ID,'so_split_test',true) && !current_user_can('manage_options')){
		  wp_redirect(get_permalink($so_split_test));
	  }
    $funnel_page_id = $post->ID;
    $so_split_test_settings = get_post_meta($post->ID,'so_split_test_settings',true);

    if($so_split_test_settings){
      if($so_split_test_settings['winner']){
         $winner_id=$so_split_test_settings['winner'];
      } else {
         $winner_id=$this->pbso_check_for_winner($so_split_test_settings,$post->ID);
      }
      if($winner_id){
        if(!isset($_COOKIE['so_test_page_'.$post->ID])){
          setcookie( 'so_test_page_'.$post->ID, $winner_id, time()+30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
        }
        $content_page=$winner_id;
        if($ab_debug) echo "Selected content page by winner is ".$content_page."<br />";
      } else if(!isset($_COOKIE['so_test_page_'.$post->ID]) || !in_array($_COOKIE['so_test_page_'.$post->ID],$so_split_test_settings['pages'])){
        $content_page=$so_split_test_settings['pages'][rand(0,count($so_split_test_settings['pages'])-1)];
        setcookie( 'so_test_page_'.$post->ID, $content_page, time()+30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
        $this->add_ab_unique_view($content_page);
      } else {
        $content_page=$_COOKIE['so_test_page_'.$post->ID];
        if($ab_debug) echo "Selected content page is ".$content_page."<br />";
      }
      $content_page_data=get_post($content_page);

      foreach($content_page_data as $id=>$value){
        $post->$id=$content_page_data->$id;
      }

      if(!$winner_id){
        $this->add_ab_view($content_page);
      }
	  }


	  //Funnel redirect
	  $funnel_id = get_post_meta($funnel_page_id,'profit_builder_funnel',true);
    //if($_SERVER['REMOTE_ADDR'] == '79.112.167.249' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') $funnel_debug=true;

    $funnel_debug=false;
    if($funnel_debug) echo 'Current page ID is '.$funnel_page_id.'<br />';


	  if($funnel_id !== false && !current_user_can('manage_options')){
      $date = date('Y-m-d',time());
      if($funnel_debug) echo 'Funnel ID'.$funnel_id.'<br />';
      if($funnels=get_option('profit_builder_funnels')){
        if(array_key_exists($funnel_id,$funnels)){
          if($funnel_debug) echo 'Funnel Found<br />';
          $funnel_redirect='';

          $funnel_page_order = array_keys($funnels[$funnel_id]['pages']);
          $current_page_position=array_search($funnel_page_id,$funnel_page_order);
          if($funnel_debug) echo "Current position |".$current_page_position."|<br />";
          //If page exists in funnel and is not the first page check if funnel is followed
          if($current_page_position !== false){

                //Check if a cookie exists
                if(isset($_COOKIE['pb_funnel_'.$funnel_id])){
                  $last_page_viewed = $_COOKIE['pb_funnel_'.$funnel_id];

                  if($funnel_debug) echo 'Last viewed page: '.$last_page_viewed;
                  if($funnel_debug) echo 'Current page: '.$current_page_position;

                  if( $current_page_position != 0 && $last_page_viewed != $current_page_position && $last_page_viewed != $current_page_position-1 ){
                    if($funnel_debug) echo "Check bypass<br />";
                    if(isset($_GET['bypass']) && md5($funnel_id) == $_GET['bypass']){
                      if($funnel_debug) echo "Bypassed<br />";
                      $this->add_funnel_view($funnel_page_order[$current_page_position]);
                      $this->add_funnel_unique_view($funnel_page_order[$current_page_position]);
                      setcookie( 'pb_funnel_'.$funnel_id, $current_page_position, time()+30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
                    } else {
                      if($funnel_debug) echo "Redirect to funnel start<br />";
                    }
                    $funnel_redirect = get_permalink($funnel_page_order[0]);
                  } else {
                    if($current_page_position>0){
                      $previous_page_id = $funnel_page_order[$current_page_position-1];
                    } else {
                      $previous_page_id = 0;
                    }

                    //N DUPLICATE KEY UPDATE

                    // Add a unique view for the current page if first time viewing it
                    if($last_page_viewed != $current_page_position){
                      $this->add_funnel_unique_view($funnel_page_id);
                      if($content_page) {
                        $this->add_funnel_unique_view($content_page);
                      }
                    }

                    // Add a view to the current page
                    $this->add_funnel_view($funnel_page_id);
                    if(isset($content_page) && $content_page){
                      $this->add_funnel_view($content_page);
                    }

                    // If user opened this page following the funnel we can add a conversion for the previous page in the funnel
                    if( $current_page_position != 0 && $last_page_viewed == $current_page_position-1){

                      // Check if previous page is an AB test to determine what variation to add the conversion for
                      $so_split_test_settings_previous = get_post_meta($previous_page_id,'so_split_test_settings',true);
                      if($so_split_test_settings_previous){
                        if($funnel_debug) echo "Previous page is AB. Adding conversion for ".$previous_page_id."<br />";
                        $content_page=$_COOKIE['so_test_page_'.$previous_page_id];
                        if($funnel_debug) echo "Selected AB Page is ".$content_page."<br />";
                        $this->add_funnel_conversion($content_page);
                      } else {
                        if($funnel_debug) echo "Previous page not AB. Adding conversion for ".$previous_page_id."<br />";
                        $this->add_funnel_conversion($previous_page_id);
                      }
                    }

                    // Cookie set that user visited current page and can move to the next
                    setcookie( 'pb_funnel_'.$funnel_id, $current_page_position, time()+30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
                  }
                } else {
                  $this->add_funnel_view($funnel_page_order[0]);
                  $this->add_funnel_unique_view($funnel_page_order[0]);

                  setcookie('pb_funnel_'.$funnel_id, 0, time()+30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );

                  if($funnel_page_id != $funnel_page_order[0]){
                    if(isset($_GET['bypass']) && md5($funnel_id) == $_GET['bypass']){
                      $this->add_funnel_view($funnel_page_order[$current_page_position]);
                      $this->add_funnel_unique_view($funnel_page_order[$current_page_position]);
                      if($funnel_debug) echo "Bypass. Set cookie to ".$current_page_position."<br />";
                      setcookie( 'pb_funnel_'.$funnel_id, $current_page_position, time()+30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
                    } else {
                      if($funnel_debug) echo "Not first page in funnel. Redirecting.";
                      $funnel_redirect = get_permalink($funnel_page_order[0]);
                    }
                  }
                }

          } else {
            // PAGE NO LONGER IN FUNNEL
            if($funnel_debug) echo 'Page is no longer in funnel!';
            //delete_post_meta($funnel_page_id,'profit_builder_funnel');
          }
        }
      }


      update_option('profit_builder_funnels',$funnels);
      if(strlen($funnel_redirect)>0){
        /// MAYBE TRACK DIRECT HITS HERE BEFORE REDIRECTING? ///
        if(!$funnel_debug) wp_redirect($funnel_redirect);
      }

      //if($funnel_debug){echo "<pre>";print_r($funnels);echo "</pre>";}

	  }

	}


	function pbso_show_meta_box() {
	  global $post;
	  $so_test_page_id=$post->ID;

	  $so_split_test=get_post_meta($post->ID,'so_split_test',true);

	  if($so_split_test){
		  $is_parent_page=false;
		  $so_test_page_id=$so_split_test;
		  $parent_page_data=get_post($so_test_page_id);

		  $so_split_test_settings = get_post_meta($so_test_page_id,'so_split_test_settings',true);
		  echo '<div class="notice notice-success"><p>This page is part of the split test <a href="'.get_edit_post_link($parent_page_data->ID).'">'.$parent_page_data->post_title.'</a> started on '.date(get_option('date_format') . ' @ ' . get_option('time_format'),$so_split_test_settings['start_time']).'</p></div>';

		  if(strpos($post->post_content,'so-track')==false && strpos($post->post_content,'data-track')==false){
			echo '<div id="so-conversion-link-warning" class="notice notice-error"><p>There are no <a href="#smartoptimizer-split">tagged links</a> in your content to track conversions. Please add at least one tracked link.</p></div>';
		  }

		  echo 'To track conversions either add the "so-track" class or data-track="1" attribute to any A tag. Example:';
		  echo '<pre>';
		  echo htmlspecialchars('<a href="http://www.google.com" data-track="1">Google</a>').'<br />';
		  echo htmlspecialchars('<a href="http://www.google.com" class="so-track">Google</a>');
		  echo '</pre>';
		  echo '<hr />';
	  } else {
		  $is_parent_page=true;
		  $so_split_test_settings = get_post_meta($so_test_page_id,'so_split_test_settings',true);
		  if($so_split_test_settings){
			echo '<p>Please use variations (child pages) to edit the content. This page serves only to configure the A/B test.</p>';
			echo '<div class="notice notice-success"><p>Test started on '. date(get_option('date_format') . ' @ ' . get_option('time_format') ,$so_split_test_settings['start_time']).'</p></div><br><br>';
		  }
	  }

	  if(!$so_split_test_settings){
		echo '<p>By enabling A/B testing your content will not be lost. It will simply be cloned into a child page (variation) of this page.</p>';
		echo '<div id="so_enable_split_test" class="button button-primary">I want to A/B test this page</div>';
	  }

	  echo '<input type="hidden" name="so-post" value="' . $so_test_page_id . '" />';
	  if($is_parent_page){
		  echo '<div id="so_split_test_settings" '.($so_split_test_settings?'':' style="display:none;"').'>';

			  if(!$so_split_test_settings){
				  echo '<div class="so-switch-field so-switch-field-wide">
					<div class="so-switch-title">Pages in test: </div>

					<input type="radio" id="so-slit-pages-2" name="so-slit-pages" value="2" checked="checked" />
					<label for="so-slit-pages-2">2</label>

					<input type="radio" id="so-slit-pages-3" name="so-slit-pages" value="3" />
					<label for="so-slit-pages-3">3</label>

					<input type="radio" id="so-slit-pages-4" name="so-slit-pages" value="4" />
					<label for="so-slit-pages-4">4</label>
				  </div>';
			  }

			  echo '<span class="so_label">Test Ends After:</span><input type="number" name="so-test-end-value" style="width:50px;" value="'.(isset($so_split_test_settings['end_value'])?$so_split_test_settings['end_value']:'7').'" />
			  <select name="so-test-end-type">
			  <option value="days" '.(isset($so_split_test_settings['end_type'])&&$so_split_test_settings['end_type']=='days'?'selected="selected"':'').'>Days</option>
			  <option value="views" '.(isset($so_split_test_settings['end_type'])&&$so_split_test_settings['end_type']=='views'?'selected="selected"':'').'>Total Views</option>
			  <option value="conversions" '.(isset($so_split_test_settings['end_type'])&&$so_split_test_settings['end_type']=='conversions'?'selected="selected"':'').'>Total Conversions</option>
			  </select>
			  <br />';

			  echo '<span class="so_label">Winner is the variation with:</span><select name="so-test-end-winner">
			  <option value="conversions" '.(isset($so_split_test_settings['end_winner'])&&$so_split_test_settings['end_winner']=='conversions'?'selected="selected"':'').'>Most Conversions</option>
			  <option value="rate" '.(isset($so_split_test_settings['end_winner'])&&$so_split_test_settings['end_winner']=='views'?'selected="selected"':'').'>Highest Conversion Rate</option>
			  </select>
			  <br /><br />';

			  echo '<div class="spinner so-spinner"></div>';

			  if(!$so_split_test_settings){
				echo '<input type="button" id="so_enable_split_test_submit" class="button button-primary" value="Create A/B Test" />';
			  } else {
				echo '<input type="button" id="so_update_split_test_submit" class="button button-primary" value="Update A/B Test Settings" />';
				echo '<input type="button" id="so_reset_split_test_submit" class="button" value="Reset Stats" />';

			  }
			echo '</div>';
		}
		echo '<div id="so_split_test_info" '.($is_parent_page?'':'style="width:100%;"').'>';

			if($so_split_test_settings && count($so_split_test_settings['pages'])>0){
			  echo '<table class="so_pages_table">';
			  echo '<thead><tr>
				  <td>Variation</td>
				  <td align="center">Views</td>
				  <td align="center">Unique Visits</td>
				  <td align="center">Conversions</td>
				  <td align="right" colspan="3"><div id="so_add_new_page" class="button button-primary">Add New Variation</div></td>
				  </tr>
				  </thead>';

          $total_views=0;
          $unique_views=0;
          $total_conversions=0;

			  foreach($so_split_test_settings['pages'] as $so_page_id){
				  $page_info=get_post($so_page_id);

          $page_stats = $this->get_ab_test_stats($so_page_id);
          $total_views+=$page_stats['totals']['views'];
          $unique_views+=$page_stats['totals']['views_unique'];
          $total_conversions+=$page_stats['totals']['conversions'];

				  if($total_conversions>0 && $total_views>0){
					  $conversion_rate=round($total_conversions/$total_views*100);
				  } else {
					  $conversion_rate=0;
				  }
				  echo '<tr ';
				  if($so_page_id==$so_split_test_settings['winner']){
					  echo 'class="so_pages_table_row_winner"';
				  } else if($so_page_id==$post->ID){
					  echo 'class="so_pages_table_row"';
				  }
				  echo '>';
				  if($so_page_id==$post->ID){
					echo '<td><strong>Currently Editing: '.$page_info->post_title.'</strong></td>';
				  } else {
					echo '<td><a href='.get_edit_post_link($so_page_id).'>'.$page_info->post_title.'</a></td>';
				  }


				  echo '<td align="center">'.$this->numberAbbreviation($total_views).'</td>
					<td align="center">'.$this->numberAbbreviation($unique_views).'</td>
					<td align="center">'.$conversion_rate.'% ('.$this->numberAbbreviation($total_conversions).')</td>';
					if($so_split_test_settings['winner']!=$so_page_id){
					  echo '<td style="width:100px;"><div id="so_winner_'.$so_page_id.'" class="so_choose_winner button button-primary">Set as winner</div></td>';
					  echo '<td style="width:100px;"><div id="so_clone_'.$so_page_id.'" class="so_clone_page button button-primary">Clone Variation</div></td>';
					  echo '<td style="width:100px;"><div id="so_delete_'.$so_page_id.'" class="so_delete_page button">Delete Variation</div></td>';
					} else {
					  echo '<td></td><td></td>';
					}
				  echo '</tr>';
			  }
			  echo '</table>';
			  echo '';
			}
		echo '</div>';


		echo '<div class="clearfix"></div>';
	  ?>
	  <script>
	  jQuery(document).ready(function(e) {
		<?php
		if($so_split_test_settings && !$so_split_test){
		?>
		jQuery('.postarea').hide();
		jQuery('.edit-slug-box').hide();
		jQuery('#pageparentdiv').hide();
		jQuery('#postimagediv').hide();
		jQuery('#wpseo_meta').hide();
		jQuery('#pbtheme-revolution').hide();
		jQuery('#wf-ss-metabox').hide();
		jQuery('#profitbuilder_exit_redirect').hide();
		jQuery('#wf-ss-metabox-graphs').hide();
		jQuery('#is-checkout-page').hide();
		jQuery('#wide_search_metabox').hide();
		jQuery('#pbtheme-page-options').hide();


		<?php
		}
		?>

		jQuery('.wp-editor-area').on('change',function(){
		  console.log(jQuery('.wp-editor-area').val());
		  var page_content=jQuery('.wp-editor-area').val();
		  if(page_content.search('data-track')>0 || page_content.search('so-track')>0){
			 jQuery('#so-conversion-link-warning').hide();
			 console.log('has tracker');
		  } else {
			 jQuery('#so-conversion-link-warning').show();
			 console.log('no tracker');
		  }
		});

		jQuery('#so_enable_split_test').on('click',function(){
		  jQuery('#so_enable_split_test').hide();
		  jQuery('#so_split_test_settings').show();
		});

		jQuery('#so_enable_split_test_submit').on('click',function(){
			jQuery('.so-spinner').show();
			jQuery('#so_enable_split_test_submit').attr('disabled','disabled');

			jQuery.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data:{action:'so_setup_split_test',
					pages:jQuery('input[name=so-slit-pages]:checked').val(),
					end_value:jQuery('input[name=so-test-end-value]').val(),
					end_type:jQuery('select[name=so-test-end-type] option:selected').val(),
					end_winner:jQuery('select[name=so-test-end-winner] option:selected').val(),
					sopost:jQuery('input[name=so-post]').val()
					},
			  dataType: 'json'}
			).done(function(response) {
			  if(response.success){
				jQuery('.so-spinner').hide();
				location.reload();
			  } else {
				  alert('An Error Occured. Please reload the page and try again.');
			  }
			})
			.fail(function(response){
				alert('An Error Occured. Please reload the page and try again.');
			})
		});

		jQuery('#so_add_new_page').on('click',function(){
			jQuery('.so-spinner').show();
			jQuery('#so_add_new_page').attr('disabled','disabled');

			jQuery.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data:{action:'so_add_page',sopost:jQuery('input[name=so-post]').val() },
			  dataType: 'json'}
			).done(function(response) {
			  if(response.success){
				jQuery('.so-spinner').hide();
				location.reload();
			  } else {
				  alert('An Error Occured. Please reload the page and try again.');
			  }
			})
			.fail(function(response){
				alert('An Error Occured. Please reload the page and try again.');
			})
		});

		jQuery('#so_update_split_test_submit').on('click',function(){
			jQuery('.so-spinner').show();
			jQuery('#so_update_split_test_submit').attr('disabled','disabled');

			jQuery.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data:{action:'so_update_split_test',
					end_value:jQuery('input[name=so-test-end-value]').val(),
					end_type:jQuery('select[name=so-test-end-type] option:selected').val(),
					end_winner:jQuery('select[name=so-test-end-winner] option:selected').val(),
					sopost:jQuery('input[name=so-post]').val()
					},
			  dataType: 'json'}
			).done(function(response) {
			  if(response.success){
				jQuery('.so-spinner').hide();
				location.reload();
			  } else {
				alert('An Error Occured. Please reload the page and try again.');
			  }
			})
			.fail(function(response){
				alert('An Error Occured. Please reload the page and try again.');
			})
		});

		jQuery('#so_reset_split_test_submit').on('click',function(){
			jQuery('.so-spinner').show();
			jQuery('#so_reset_split_test_submit').attr('disabled','disabled');

			jQuery.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data:{action:'so_reset_split_test',
					sopost:jQuery('input[name=so-post]').val()
					},
			  dataType: 'json'}
			).done(function(response) {
			  if(response.success){
				jQuery('.so-spinner').hide();
				location.reload();
			  } else {
				alert('An Error Occured. Please reload the page and try again.');
			  }
			})
			.fail(function(response){
				alert('An Error Occured. Please reload the page and try again.');
			})
		});

		jQuery('.so_choose_winner').on('click',function(){
			jQuery('.so_choose_winner').attr('disabled','disabled');

			jQuery.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data:{action:'so_choose_winner', so_page:jQuery('input[name=so-post]').val(), so_winner_page:this.id.replace('so_winner_','')},
			  dataType: 'json'}
			).done(function(response) {
			  if(response.success){
				location.reload();
			  } else {
				alert('An Error Occured. Please reload the page and try again.');
			  }
			})
			.fail(function(response){
				alert('An Error Occured. Please reload the page and try again.');
			})
		});


		 jQuery('.so_delete_page').on('click',function(){
			jQuery('.so_delete_page').attr('disabled','disabled');

			jQuery.ajax({
			  type: 'POST',
			  url: ajaxurl,
			  data:{action:'so_delete_page', so_page:jQuery('input[name=so-post]').val(), so_delete_page:this.id.replace('so_delete_','')},
			  dataType: 'json'}
			).done(function(response) {
			  if(response.success){
				location.href='<?php echo str_replace('&amp;','&',get_edit_post_link($so_test_page_id)); ?>';
			  } else {
				alert('An Error Occured. Please reload the page and try again.');
			  }
			})
			.fail(function(response){
				alert('An Error Occured. Please reload the page and try again.');
			})
		});

	  });
	  </script>
	  <?php
	}

	function pbso_register_meta_boxes() {
	  add_meta_box( 'pb-abtest', 'ProfitBuilder A/B Test', array(&$this, 'pbso_show_meta_box'), 'page', 'advanced', 'high');
	  add_meta_box( 'pb-funnel', 'ProfitBuilder Funnel', array(&$this, 'funnels_show_meta_box'), 'page', 'advanced', 'high');
	  add_meta_box( 'pb-funnel', 'ProfitBuilder Funnel', array(&$this, 'funnels_show_meta_box'), 'post', 'advanced', 'high');
	  add_meta_box( 'pb-funnel', 'ProfitBuilder Funnel', array(&$this, 'funnels_show_meta_box'), 'product', 'advanced', 'high');
	}



	function pbso_setup_split_test(){
	  global $wpdb;
	  $post_id = $_POST['sopost'];

	  if(!$post_id){
		  return;
	  }

	  $post = get_post( $post_id );
	  $current_user = wp_get_current_user();
	  $new_post_author = $current_user->ID;

	  $split_test_settings=array(
		  'pages'=>array(),
		  'end_type'=>$_POST['end_type'],
		  'end_value'=>$_POST['end_value'],
		  'end_winner'=>$_POST['end_winner'],
		  'start_time'=>time(),
		  'winner'=>false
	  );

	  $args = array(
		'comment_status' => $post->comment_status,
		'ping_status' => $post->ping_status,
		'post_author' => $new_post_author,
		'post_content' => $post->post_content,
		'post_excerpt' => $post->post_excerpt,
		'post_name' => $post->post_name,
		'post_parent' => $post->ID,
		'post_password' => $post->post_password,
		'post_status' => $post->post_status,
		'post_title' => $post->post_title,
		'post_type' => $post->post_type,
		'to_ping' => $post->to_ping,
		'menu_order' => $post->menu_order
	  );


	  $split_test_pages=array();
	  $total_pages=1;
	  for($i=1;$i<=(int)$_POST['pages'];$i++){
		  $args['post_title']=$post->post_title.' '.$total_pages;
		  $new_post_id = wp_insert_post( $args );
		  $total_pages++;
		  if($new_post_id){
			$split_test_pages[]=$new_post_id;
			$taxonomies = get_object_taxonomies($post->post_type);
			if(!empty($taxonomies) && is_array($taxonomies)){
			  foreach ($taxonomies as $taxonomy) {
				$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
			  }
			}


			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
			if (count($post_meta_infos)!=0) {
			  $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			  foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			  }
			  $sql_query.= implode(" UNION ALL ", $sql_query_sel);
			  $wpdb->query($sql_query);
			}
			update_post_meta($new_post_id, 'so_split_test', $post_id);
			update_post_meta($new_post_id, 'so_split_test_views', array(date('Y-m-d')=>0));
			update_post_meta($new_post_id, 'so_split_test_unique_views', array(date('Y-m-d')=>0));
			update_post_meta($new_post_id, 'so_split_test_conversions', array(date('Y-m-d')=>0));
			$this->pbso_copy_post_pb_data($new_post_id,$post_id);
		  }
	  }
	  $split_test_settings['pages']=$split_test_pages;
	  update_post_meta($post_id, 'so_split_test_settings', $split_test_settings);
	  wp_send_json_success( $split_test_settings);
	  die();
	}

	function pbso_add_page(){
	  global $wpdb;
	  $post_id = $_POST['sopost'];

	  if(!$post_id){
		  return;
	  }

	  $post = get_post( $post_id );
	  $current_user = wp_get_current_user();
	  $new_post_author = $current_user->ID;

	  $split_test_settings = get_post_meta($post_id,'so_split_test_settings',true);

	  $args = array(
		'comment_status' => $post->comment_status,
		'ping_status' => $post->ping_status,
		'post_author' => $new_post_author,
		'post_content' => $post->post_content,
		'post_excerpt' => $post->post_excerpt,
		'post_name' => $post->post_name,
		'post_parent' => $post->ID,
		'post_password' => $post->post_password,
		'post_status' => $post->post_status,
		'post_title' => $post->post_title,
		'post_type' => $post->post_type,
		'to_ping' => $post->to_ping,
		'menu_order' => $post->menu_order
	  );



	  $new_post_id = wp_insert_post( $args );
	  if($new_post_id){
		$split_test_settings['pages'][]=$new_post_id;
		$taxonomies = get_object_taxonomies($post->post_type);
		if(!empty($taxonomies) && is_array($taxonomies)){
		  foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		  }
		}


		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
		  $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
		  foreach ($post_meta_infos as $meta_info) {
			$meta_key = $meta_info->meta_key;
			$meta_value = addslashes($meta_info->meta_value);
			$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
		  }
		  $sql_query.= implode(" UNION ALL ", $sql_query_sel);
		  $wpdb->query($sql_query);
		}
		update_post_meta($new_post_id, 'so_split_test', $post_id);
		update_post_meta($new_post_id, 'so_split_test_views', array(date('Y-m-d')=>0));
		update_post_meta($new_post_id, 'so_split_test_unique_views', array(date('Y-m-d')=>0));
		update_post_meta($new_post_id, 'so_split_test_conversions', array(date('Y-m-d')=>0));
		$this->pbso_copy_post_pb_data($new_post_id,$post_id);
	  }

	  update_post_meta($post_id, 'so_split_test_settings', $split_test_settings);
	  wp_send_json_success( $split_test_settings);
	  die();
	}

	function pbso_clone_page(){
	  global $wpdb;
	  $post_id = $_POST['sopost'];
	  $variation_id = $_POST['so_clone_page'];
	  if(!$post_id){
		  return;
	  }

	  $post = get_post( $variation_id );
	  $current_user = wp_get_current_user();
	  $new_post_author = $current_user->ID;

	  $split_test_settings = get_post_meta($post_id,'so_split_test_settings',true);

	  $args = array(
		'comment_status' => $post->comment_status,
		'ping_status' => $post->ping_status,
		'post_author' => $new_post_author,
		'post_content' => $post->post_content,
		'post_excerpt' => $post->post_excerpt,
		'post_name' => $post->post_name,
		'post_parent' => $post_id,
		'post_password' => $post->post_password,
		'post_status' => $post->post_status,
		'post_title' => $post->post_title,
		'post_type' => $post->post_type,
		'to_ping' => $post->to_ping,
		'menu_order' => $post->menu_order
	  );



	  $new_post_id = wp_insert_post( $args );
	  if($new_post_id){
		$split_test_settings['pages'][]=$new_post_id;
		$taxonomies = get_object_taxonomies($post->post_type);
		if(!empty($taxonomies) && is_array($taxonomies)){
		  foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($variation_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		  }
		}


		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
		  $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
		  foreach ($post_meta_infos as $meta_info) {
			$meta_key = $meta_info->meta_key;
			$meta_value = addslashes($meta_info->meta_value);
			$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
		  }
		  $sql_query.= implode(" UNION ALL ", $sql_query_sel);
		  $wpdb->query($sql_query);
		}
		update_post_meta($new_post_id, 'so_split_test', $post_id);
		update_post_meta($new_post_id, 'so_split_test_views', array(date('Y-m-d')=>0));
		update_post_meta($new_post_id, 'so_split_test_unique_views', array(date('Y-m-d')=>0));
		update_post_meta($new_post_id, 'so_split_test_conversions', array(date('Y-m-d')=>0));
		$this->pbso_copy_post_pb_data($new_post_id,$variation_id);
	  }

	  update_post_meta($post_id, 'so_split_test_settings', $split_test_settings);
	  wp_send_json_success( $split_test_settings);
	  die();
	}

	function pbso_update_split_test(){
	  global $wpdb;
	  $post_id = $_POST['sopost'];

	  if(!$post_id){
		  return;
	  }

	  $split_test_settings = get_post_meta($post_id,'so_split_test_settings',true);
	  $split_test_settings['end_type']=$_POST['end_type'];
	  $split_test_settings['end_value']=$_POST['end_value'];
	  $split_test_settings['end_winner']=$_POST['end_winner'];
	  $split_test_settings['winner']=false;

	  update_post_meta($post_id, 'so_split_test_settings', $split_test_settings);
	  wp_send_json_success( $split_test_settings);
	  die();
	}

	function pbso_delete_page(){
	  global $wpdb;
	  $page_id = (int)$_POST['so_page'];

	  $delete_id = (int)$_POST['so_delete_page'];

	  $pbso_current_page=0;

	  if(!$page_id || !$delete_id){
		  return;
	  }

	  $pbso_live_editor=true;
	  if($_POST['pbso_live_editor']=='true'){
		  $so_page_front=true;
		  $pbso_current_page=(int)$_POST['current_page'];
	  }

	  $split_test_settings = get_post_meta($page_id,'so_split_test_settings',true);

	  if(($key = array_search($delete_id, $split_test_settings['pages'])) !== false) {
		unset($split_test_settings['pages'][$key]);
		wp_delete_post($delete_id,true);
	  }

	  $split_test_settings['pages']=array_values($split_test_settings['pages']);

	  update_post_meta($page_id, 'so_split_test_settings', $split_test_settings);
	  if($pbso_live_editor && $pbso_current_page == $delete_id){
		  wp_send_json_success(array('redirect'=>admin_url('admin-ajax.php').'?action=pbuilder_edit&p='.$split_test_settings['pages'][0]));
	  } else {
		  wp_send_json_success();
	  }
	  die();
	}

	function pbso_choose_winner(){
	  global $wpdb;
	  $page_id = $_POST['so_page'];
	  $winner_id = $_POST['so_winner_page'];
	  if(!$page_id || !$winner_id){
		  return;
	  }

	  $split_test_settings = get_post_meta($page_id,'so_split_test_settings',true);
	  $split_test_settings['winner']=$winner_id;
	  update_post_meta($page_id, 'so_split_test_settings', $split_test_settings);
	  wp_send_json_success( $split_test_settings);
	  die();
	}


	function pbso_add_conversion(){
	  global $wpdb;
	  $page_id = $_POST['so_page'];
    $this->add_ab_conversion($page_id);
	  wp_send_json_success( $total_conversions);
	  die();
	}

	function pbso_copy_post_pb_data($new_id, $post) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'profit_builder_pages';
		$sql = 'SELECT * FROM ' . $table_name . " where id=" . $post;
		$row = $wpdb->get_row($sql);
		if ($row) {
			$data = array(
				'id' => $new_id,
				'switch' => $row->switch,
				'layout' => $row->layout,
				'items' => $row->items
			);
			$wpdb->insert($table_name, $data);
		}
	}

	function pbso_reset_split_test(){
	  global $wpdb;
	  $post_id = $_POST['sopost'];

	  if(!$post_id){
		  return;
	  }

	  $split_test_settings = get_post_meta($post_id,'so_split_test_settings',true);
	  foreach($split_test_settings['pages'] as $page_id){
  		$wpdb->query('DELETE FROM '.$wpdb->prefix.'profit_builder_ab_stats WHERE post_id="'.$page_id.'"');
	  }
	  $split_test_settings['winner']=false;
    $split_test_settings['start_time']=time();
	  update_post_meta($post_id, 'so_split_test_settings', $split_test_settings);
	  wp_send_json_success( $split_test_settings);
	  die();
	}

}
