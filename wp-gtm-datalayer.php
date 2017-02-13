<?php
/**
 * Plugin Name: Google Tag Manager DataLayer
 * Plugin URI: https://github.com/luizeof/wp-gtmdl-datalayer
 * Description: Generate a Google Tag Manager DataLayer with a lot of data
 * Version: 0.9.2
 * Author: luizeof
 * Author URI: http://luizeof.com.br
 * License: GPL3
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	echo 'This file should not be accessed directly!';
	exit; // Exit if accessed directly
}

// Store plugin directory
define( 'gtmdl_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
// Store plugin main file path
define( 'gtmdl_PLUGIN_FILE', __FILE__ );

add_action('wp_head', 'wpgtmdl_datalayer_data');

// Verifica se o WooCommerce ta ativo
function is_woocommerce_active() {
	  return class_exists( 'WooCommerce' );
}

/**
 * Writes Google Tag Manager Data Layer Info
 */
function wpgtmdl_datalayer_data() {

	global $wp_query;
	$dataLayer = array();
	$current_user = wp_get_current_user();

	$wp_userid = get_current_user_id();
	if ( $wp_userid > 0 ) {
		$dataLayer["gtmdlUserId"] = $wp_userid;
	}

	$dataLayer["gtmdlUtmSource"] = isset($_GET['utm_source']) ? $_GET['utm_source'] : "";
	$dataLayer["gtmdlUtmMedium"] = isset($_GET['utm_medium']) ? $_GET['utm_medium'] : "";
	$dataLayer["gtmdlUtmCampaign"] = isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : "";
	$dataLayer["gtmdlUtmTerm"] = isset($_GET['utm_term']) ? $_GET['utm_term'] : "";
	$dataLayer["gtmdlUtmContent"] = isset($_GET['utm_content']) ? $_GET['utm_content'] : "";

	$dataLayer["gtmdlUserEmail"] = ( empty( $current_user->user_email ) ? "" : $current_user->user_email );
	$dataLayer["gtmdlUserType"] = ( empty( $current_user->roles[0] ) ? "" : $current_user->roles[0] );

	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
			$ipaddress = 'UNKNOWN';

	$ipquery = @unserialize(file_get_contents('http://ip-api.com/php/'.$ipaddress));
	$dataLayer["gtmdlGeoCountry"] = $ipquery['country'];
	$dataLayer["gtmdlGeoCountryCode"] = $ipquery['countryCode'];
	$dataLayer["gtmdlGeoRegion"] = $ipquery['region'];
	$dataLayer["gtmdlGeoRegionName"] = $ipquery['regionName'];
	$dataLayer["gtmdlGeoCity"] = $ipquery['city'];
	$dataLayer["gtmdlGeoTimezone"] = $ipquery['timezone'];
	$dataLayer["gtmdlGeoISP"] = $ipquery['isp'];

	spl_autoload_register( function( $class ) {
			$class_parts = explode( "\\", $class );
			if ( "WhichBrowser" == $class_parts[0] ) {
				include dirname( __FILE__ ) . "/whichbrowser/" . str_replace( array( "WhichBrowser", "\\" ), array( "src", "/" ), $class ) . ".php";
			}
		});

		require_once( dirname( __FILE__ ) . "/whichbrowser/src/Parser.php" );

		$all_headers = getallheaders();
		if ( ( false === $all_headers ) && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$all_headers = $_SERVER['HTTP_USER_AGENT'];
		}
		if ( false !== $all_headers ) {
			$detected = new WhichBrowser\Parser($all_headers);

				$dataLayer["gtmdlBrowserName"]         = isset( $detected->browser->name ) ? $detected->browser->name : "";
				$dataLayer["gtmdlBrowserVersion"]      = isset( $detected->browser->version->value ) ? $detected->browser->version->value : "";

				$dataLayer["gtmdlBrowserEngineName"]         = isset( $detected->engine->name ) ? $detected->engine->name : "";
				$dataLayer["gtmdlBrowserEngineVersion"]      = isset( $detected->engine->version->value ) ? $detected->engine->version->value : "";

				$dataLayer["gtmdlOsName"]         = isset( $detected->os->name ) ? $detected->os->name : "";
				$dataLayer["gtmdlOsVersion"]      = isset( $detected->os->version->value ) ? $detected->os->version->value : "";

				$dataLayer["gtmdlDeviceType"]         = isset( $detected->device->type ) ? $detected->device->type : "";
				$dataLayer["gtmdlDeviceManufacturer"] = isset( $detected->device->manufacturer ) ? $detected->device->manufacturer : "";
				$dataLayer["gtmdlDeviceModel"]        = isset( $detected->device->model ) ? $detected->device->model : "";

		}

		if ( is_singular() ) {
				$dataLayer["gtmdlPagePostType"] = get_post_type();
				$dataLayer["gtmdlPagePostType2"] = "single-".get_post_type();

				$_post_cats = get_the_category();
				if ( $_post_cats ) {
					foreach( $_post_cats as $_one_cat ) {
						$dataLayer["gtmdlPageCategory"] = $_one_cat->slug;
					}
				}

				$_post_tags = get_the_tags();
				if ( $_post_tags ) {
					foreach( $_post_tags as $_one_tag ) {
						$dataLayer["gtmdlPageAttributes"] = $_one_tag->slug;
					}
				}

				$postuser = get_userdata( $GLOBALS["post"]->post_author );
				if ( false !== $postuser ) {
					$dataLayer["gtmdlPagePostAuthorID"] = $postuser->ID;
		      $dataLayer["gtmdlPagePostAuthor"] = $postuser->display_name;
				}

				$dataLayer["gtmdlPagePostDate"] = get_the_date();
				$dataLayer["gtmdlPagePostDateYear"] = get_the_date( "Y" );
				$dataLayer["gtmdlPagePostDateMonth"] = get_the_date( "m" );
				$dataLayer["gtmdlPagePostDateDay"] = get_the_date( "d" );

		} //isSingluar

	$dataLayer["gtmdlReferer"] = ( isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "" );

	if ( is_woocommerce_active() ) :
		$customer_orders = get_posts( array(
		        'numberposts' => -1,
		        'meta_key'    => '_customer_user',
		        'meta_value'  => $wp_userid,
		        'post_type'   => wc_get_order_types(),
		        'post_status' => array_keys( wc_get_order_statuses() ),
		    ) );
		$dataLayer["gtmdlWooOrdersCount"] = count($customer_orders);
	endif;

	if ( is_user_logged_in() ) {
		$dataLayer["gtmdlLogin"] = "logged";
	} else {
		$dataLayer["gtmdlLogin"] = "anonymous";
	}

 echo '<!-- begin Google Tag Manager Data Layer by luizeof -->';
 echo '<script>';
	echo 'window.dataLayer = window.dataLayer || [];';
	echo 'window.dataLayer.push({';
		foreach ($dataLayer as $x => $x_value) :
			echo "'" . $x . "' : '" . $x_value . "',";
		endforeach;
	echo '});';
 echo '</script>';
 echo '<!-- end Google Tag Manager Data Layer by luizeof -->';

} // wpgtmdl_datalayer_data()


/////////// PLUGIN UPDATE CHECKER ***********************
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/luizeof/wp-gtm-datalayer/',
    __FILE__,
    'wp-gtm-datalayer'
);
$myUpdateChecker->setBranch('master');
/////////// **********************************************
