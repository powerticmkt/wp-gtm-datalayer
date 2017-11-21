<?php
/**
 * Plugin Name: Google Tag Manager DataLayer by Powertic
 * Plugin URI: https://github.com/powertic/wp-gtm-datalayer
 * Description: Google Tag Manager DataLayer with Wordpress Data
 * Version: 0.10.1
 * Author: Powertic
 * Author URI: https://powertic.com
 * License: GPL3
 */

// Prevent direct access to this file.
if (! defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}

// Store plugin directory
define('gtmdl_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Store plugin main file path
define('gtmdl_PLUGIN_FILE', __FILE__);

// Action to hook up plugin scripts to page head
add_action('wp_head', 'wpgtmdl_datalayer_data');

// Verify if WooCommerce is active
function is_woocommerce_active()
{
    return class_exists('WooCommerce');
}

/**
 * Writes Google Tag Manager Data Layer Info
 */
function wpgtmdl_datalayer_data()
{

    // get wordpress global post query
    global $wp_query;

    // start the Google Tag Manager Data Layer array
    $dataLayer = array();

    // Wordpress current logged user
    $current_user = wp_get_current_user();


    $wp_userid = get_current_user_id();
    if ($wp_userid > 0) {
        $dataLayer["gtmWPUserId"] = $wp_userid;
    }

    //TEST: return utm_source var
    if (isset($_GET['utm_source'])):
        $dataLayer["gtmUtmSource"] = $_GET['utm_source'];
    endif;

    $dataLayer["gtmUtmMedium"] = isset($_GET['utm_medium']) ? $_GET['utm_medium'] : "";

    $dataLayer["gtmUtmCampaign"] = isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : "";

    $dataLayer["gtmUtmTerm"] = isset($_GET['utm_term']) ? $_GET['utm_term'] : "";

    $dataLayer["gtmUtmContent"] = isset($_GET['utm_content']) ? $_GET['utm_content'] : "";

    $dataLayer["gtmWPUserEmail"] = (empty($current_user->user_email) ? "" : $current_user->user_email);

    $dataLayer["gtmWPUserType"] = (empty($current_user->roles[0]) ? "" : $current_user->roles[0]);

    // Try get remote user IP Address
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }

    // get JSON content from public IP API
    $ipquery = @unserialize(file_get_contents('http://ip-api.com/php/'.$ipaddress));
    $dataLayer["gtmGeoCountry"] = $ipquery['country'];
    $dataLayer["gtmGeoCountryCode"] = $ipquery['countryCode'];
    $dataLayer["gtmGeoRegion"] = $ipquery['region'];
    $dataLayer["gtmGeoRegionName"] = $ipquery['regionName'];
    $dataLayer["gtmGeoCity"] = $ipquery['city'];

    spl_autoload_register(function ($class) {
        $class_parts = explode("\\", $class);
        if ("WhichBrowser" == $class_parts[0]) {
            include dirname(__FILE__) . "/whichbrowser/" . str_replace(array( "WhichBrowser", "\\" ), array( "src", "/" ), $class) . ".php";
        }
    });

    require_once(dirname(__FILE__) . "/whichbrowser/src/Parser.php");

    $all_headers = getallheaders();
    if ((false === $all_headers) && isset($_SERVER['HTTP_USER_AGENT'])) {
        $all_headers = $_SERVER['HTTP_USER_AGENT'];
    }
    if (false !== $all_headers) {
        $detected = new WhichBrowser\Parser($all_headers);

        $dataLayer["gtmBrowserName"]         = isset($detected->browser->name) ? $detected->browser->name : "";
        $dataLayer["gtmBrowserVersion"]      = isset($detected->browser->version->value) ? $detected->browser->version->value : "";

        $dataLayer["gtmBrowserEngineName"]         = isset($detected->engine->name) ? $detected->engine->name : "";
        $dataLayer["gtmBrowserEngineVersion"]      = isset($detected->engine->version->value) ? $detected->engine->version->value : "";

        $dataLayer["gtmOsName"]         = isset($detected->os->name) ? $detected->os->name : "";
        $dataLayer["gtmOsVersion"]      = isset($detected->os->version->value) ? $detected->os->version->value : "";

        $dataLayer["gtmDeviceType"]         = isset($detected->device->type) ? $detected->device->type : "";
        $dataLayer["gtmDeviceManufacturer"] = isset($detected->device->manufacturer) ? $detected->device->manufacturer : "";
        $dataLayer["gtmDeviceModel"]        = isset($detected->device->model) ? $detected->device->model : "";
    }

    if (is_singular()) {
        $dataLayer["gtmPagePostType"] = get_post_type();
        $dataLayer["gtmPageTemplate"] = "single";

        $_post_cats = get_the_category();
        if ($_post_cats) {
            foreach ($_post_cats as $_one_cat) {
                $dataLayer["gtmPageCategory"] = $_one_cat->slug;
            }
        }

        $_post_tags = get_the_tags();
        if ($_post_tags) {
            foreach ($_post_tags as $_one_tag) {
                $dataLayer["gtmPageTags"] = $_one_tag->slug;
            }
        }

        $postuser = get_userdata($GLOBALS["post"]->post_author);
        if (false !== $postuser) {
            $dataLayer["gtmPagePostAuthorID"] = $postuser->ID;
            $dataLayer["gtmPagePostAuthor"] = $postuser->display_name;
        }

        $dataLayer["gtmPagePostDate"] = get_the_date();
        $dataLayer["gtmPagePostDateYear"] = get_the_date("Y");
        $dataLayer["gtmPagePostDateMonth"] = get_the_date("m");
        $dataLayer["gtmPagePostDateDay"] = get_the_date("d");
    } //isSingluar

    $dataLayer["gtmReferer"] = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");

    if (is_woocommerce_active()) :
        $customer_orders = get_posts(array(
                'numberposts' => -1,
                'meta_key'    => '_customer_user',
                'meta_value'  => $wp_userid,
                'post_type'   => wc_get_order_types(),
                'post_status' => array_keys(wc_get_order_statuses()),
            ));
    $dataLayer["gtmWooOrdersCount"] = count($customer_orders);
    endif;

    if (is_user_logged_in()) {
        $dataLayer["gtmWPLogged"] = "logged";
    } else {
        $dataLayer["gtmWPLogged"] = "anonymous";
    }

    echo '<!-- begin Google Tag Manager Data Layer by Powertic -->';
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
    'https://github.com/powerticmkt/wp-gtm-datalayer/',
    __FILE__,
    'wp-gtm-datalayer'
);
$myUpdateChecker->setBranch('master');
/////////// **********************************************
