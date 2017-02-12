<?php
/**
 * Plugin Name: Google Tag Manager DataLayer
 * Plugin URI: https://github.com/luizeof/wp-gtm-datalayer
 * Description: Generate a Google Tag Manager DataLayer with a lot of data
 * Version: 0.9.0
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
define( 'GTM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
// Store plugin main file path
define( 'GTM_PLUGIN_FILE', __FILE__ );

add_action('wp_head', 'wpgtm_datalayer_data');

// Verifica se o WooCommerce ta ativo
function is_woocommerce_active() {
	  return class_exists( 'WooCommerce' );
}

/**
 * Writes Google Tag Manager Data Layer Info
 */
function wpgtm_datalayer_data()
{

	global $wp_query;
	$dataLayer = array();
	$current_user = wp_get_current_user();

	$wp_userid = get_current_user_id();
	if ( $wp_userid > 0 ) {
		$dataLayer["gtmUserId"] = $wp_userid;
	}

	$dataLayer["gtmUtmSource"] = isset($_GET['utm_source']) ? $_GET['utm_source'] : "";
	$dataLayer["gtmUtmMedium"] = isset($_GET['utm_medium']) ? $_GET['utm_medium'] : "";
	$dataLayer["gtmUtmCampaign"] = isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : "";
	$dataLayer["gtmUtmTerm"] = isset($_GET['utm_term']) ? $_GET['utm_term'] : "";
	$dataLayer["gtmUtmContent"] = isset($_GET['utm_content']) ? $_GET['utm_content'] : "";

	$dataLayer["gtmUserEmail"] = ( empty( $current_user->user_email ) ? "" : $current_user->user_email );
	$dataLayer["gtmUserType"] = ( empty( $current_user->roles[0] ) ? "" : $current_user->roles[0] );

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
	$dataLayer["gtmGeoCountry"] = $ipquery['country'];
	$dataLayer["gtmGeoCountryCode"] = $ipquery['countryCode'];
	$dataLayer["gtmGeoRegion"] = $ipquery['region'];
	$dataLayer["gtmGeoRegionName"] = $ipquery['regionName'];
	$dataLayer["gtmGeoCity"] = $ipquery['city'];
	$dataLayer["gtmGeoTimezone"] = $ipquery['timezone'];
	$dataLayer["gtmGeoISP"] = $ipquery['isp'];

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

				$dataLayer["gtmBrowserName"]         = isset( $detected->browser->name ) ? $detected->browser->name : "";
				$dataLayer["gtmBrowserVersion"]      = isset( $detected->browser->version->value ) ? $detected->browser->version->value : "";

				$dataLayer["gtmBrowserEngineName"]         = isset( $detected->engine->name ) ? $detected->engine->name : "";
				$dataLayer["gtmBrowserEngineVersion"]      = isset( $detected->engine->version->value ) ? $detected->engine->version->value : "";

				$dataLayer["gtmOsName"]         = isset( $detected->os->name ) ? $detected->os->name : "";
				$dataLayer["gtmOsVersion"]      = isset( $detected->os->version->value ) ? $detected->os->version->value : "";

				$dataLayer["gtmDeviceType"]         = isset( $detected->device->type ) ? $detected->device->type : "";
				$dataLayer["gtmDeviceManufacturer"] = isset( $detected->device->manufacturer ) ? $detected->device->manufacturer : "";
				$dataLayer["gtmDeviceModel"]        = isset( $detected->device->model ) ? $detected->device->model : "";

		}

		if ( is_singular() ) {
				$dataLayer["gtmPagePostType"] = get_post_type();
				$dataLayer["gtmPagePostType2"] = "single-".get_post_type();

				$_post_cats = get_the_category();
				if ( $_post_cats ) {
					$dataLayer["gtmPageCategory"] = array();
					foreach( $_post_cats as $_one_cat ) {
						$dataLayer["gtmPageCategory"][0] = $_one_cat->slug;
					}
				}

				$_post_tags = get_the_tags();
				if ( $_post_tags ) {
					$dataLayer["gtmPageAttributes"] = array();
					foreach( $_post_tags as $_one_tag ) {
						$dataLayer["gtmPageAttributes"][] = $_one_tag->slug;
					}
				}

				$postuser = get_userdata( $GLOBALS["post"]->post_author );
				if ( false !== $postuser ) {
					$dataLayer["gtmPagePostAuthorID"] = $postuser->ID;
		      $dataLayer["gtmPagePostAuthor"] = $postuser->display_name;
				}

				$dataLayer["gtmPagePostDate"] = get_the_date();
				$dataLayer["gtmPagePostDateYear"] = get_the_date( "Y" );
				$dataLayer["gtmPagePostDateMonth"] = get_the_date( "m" );
				$dataLayer["gtmPagePostDateDay"] = get_the_date( "d" );

		} //isSingluar

	$dataLayer["gtmReferer"] = ( isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "" );

	if ( is_woocommerce_active() ) :
		$customer_orders = get_posts( array(
		        'numberposts' => -1,
		        'meta_key'    => '_customer_user',
		        'meta_value'  => $wp_userid,
		        'post_type'   => wc_get_order_types(),
		        'post_status' => array_keys( wc_get_order_statuses() ),
		    ) );
		$dataLayer["gtmWooOrdersCount"] = count($customer_orders);
	endif;

	if ( is_user_logged_in() ) {
		$dataLayer["gtmLoginState"] = "logged";
	} else {
		$dataLayer["gtmLoginState"] = "anonymous";
	}


 echo '<script>';
	echo 'window.dataLayer = window.dataLayer || [];';
	echo 'window.dataLayer.push({';
		foreach ($dataLayer as $x => $x_value) :
			echo "'" . $x . "' : '" . $x_value . "',";
		endforeach;
	echo '});';
 echo '</script>';

}


/////////// PLUGIN UPDATE CHECKER ***********************
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/luizeof/wp-gtm-datalayer/',
    __FILE__,
    'wp-gtm-datalayer'
);
$myUpdateChecker->setBranch('master');
/////////// **********************************************
