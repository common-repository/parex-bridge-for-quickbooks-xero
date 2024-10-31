<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

function addAdminMenuPXB()
{
    $filename_icon = sanitize_file_name('parexbridge.png');
    $page_title    = WP_PXB_PLUGIN_NAME;
    $menu_title    = WP_PXB_PLUGIN_NAME;
    $capability    = 'manage_options';
    $menu_slug     = 'parex-bridge-for-quickbooks-and-xero';
    $function      = 'menuCallbackPXB';
    $icon_url      = plugin_dir_url(__DIR__) . 'assets/img/' . $filename_icon;
    $position      = 81;
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
}

function menuCallbackPXB()
{
    require_once plugin_dir_path(__DIR__) . '/views/parexbridge-redirect.php';
}

function isCheckWoocommerceAvailablePXB()
{
    $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
    if (in_array('woocommerce/woocommerce.php', $active_plugins)) {
        return true;
    } else {
        return false;
    }
}

function getPXBDataFromServer()
{
    return array('guid' => trim(getPXBGUID()), 'userId' => trim(get_option('wp_pxb_user')), 'module' => trim(get_option('wp_pxb_module')));
}

function getPluginVersionPXB()
{
    if (!function_exists('get_plugins')) {

        require_once ABSPATH . 'wp-admin\includes\plugin.php';
    }
    $plugin_data = get_plugins();
    return $plugin_data['parex-bridge-for-quickbooks-and-xero/parex-bridge-for-quickbooks-and-xero.php']['Version'];
}

function getUserDataPXB()
{
    global $wpdb;

    $table_wp_options = $wpdb->prefix . 'options';
    $table_wp_users   = $wpdb->prefix . 'users';

    $current_users = [];
    // get_option('admin_email'); //Get admin email address from wp_options table
    //First user created in wordpress will be admin
    $all_users = $wpdb->get_results("SELECT ID, user_email, user_nicename, display_name FROM $table_wp_users LIMIT 1");

    $current_user_data = isset($all_users[0]) ? $all_users[0] : [];

    $current_users['id']            = isset($current_user_data->ID) ? $current_user_data->ID : 1;
    $current_users['user_email']    = isset($current_user_data->user_email) ? $current_user_data->user_email : get_option('admin_email');
    $current_users['user_nicename'] = isset($current_user_data->user_nicename) ? $current_user_data->user_nicename : "";
    $current_users['display_name']  = isset($current_user_data->display_name) ? $current_user_data->display_name : "";
    return $current_users;
}

function isUserLoggedInPXB()
{

    if (!function_exists('wp_get_current_user')) {
        include_once ABSPATH . 'wp-includes/pluggable.php';
    }

    $user = wp_get_current_user();
    if ($user->exists()) {
        return true;
    } else {
        return false;
    }

}

function activatePluginPXB()
{
    PXB_Activator::activate();
}

function pluginDeactivatePXB()
{
    PXB_Activator::deactivate();
}

function isDependencyAvailablePXB()
{
    $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

    if (in_array('woocommerce/woocommerce.php', $active_plugins)) {
        return true;
    } else {
        deactivate_plugins(plugin_basename(__FILE__));
        $link = admin_url('plugin-install.php?s=woocommerce&tab=search&type=term');
        wp_die(WP_PXB_PLUGIN_NAME . " can not activate because WooCommerce is not installed or active. Please activate WooCommerce if already installed or <a href='" . $link . "'>Install WooCommerce!</a>");
        return false;
    }

}

function pluginActivationRedirectPXB($plugin)
{
    exit(wp_redirect(admin_url('plugins.php')));
}

function registerUserPXB()
{
    $client      = new PXB_HttpClient();
    $resultsData = $client->callAPI("oauth");
    $user        = trim(get_option('wp_pxb_user'));

    if (!empty($resultsData) && isset($user) && $user == '') {
        $userData = array(
            'token'               => $resultsData['token'],
            'pxb_secret'          => getPXBGUID(),
            'shop_email'          => getUserDataPXB()['user_email'],
            'shop_owner'          => getUserDataPXB()['display_name'],
            'shop_name'           => get_option('blogname'),
            'currency'            => get_woocommerce_currency(),
            'country_code'        => getCountryState()['country'],
            'province_code'       => getCountryState()['state'],
            'city'                => get_option('woocommerce_store_city'),
            'address1'            => get_option('woocommerce_store_address'),
            'gmt_offset_timezone' => get_option('gmt_offset'),
            'string_timezone'     => get_option('timezone_string'),
            'description'         => 'User Create Request',
        );

        $resultUserData = $client->callAPI("create", $userData);
        if (!empty($resultUserData) && isset($resultUserData['user_id'])) {
            update_option('wp_pxb_user', $resultUserData['user_id']);
            return $resultUserData['user_id'];
        } else if (isset($resultUserData['message'])) {
            setErrorMessagePXB($resultUserData['message']);
            return false;
        }else{
            return false;
        }
    } else {
        return $user;
    }
}

//Make API call with GUID and get token for it
function registerStorePXB($module = '')
{
    global $wpdb;

    if (empty($module['code']) || empty($module['module'])) {
        setErrorMessagePXB();
        return false;
    }

    $consumer_key    = 'ck_' . pxbRandHash();
    $consumer_secret = 'cs_' . pxbRandHash();

    $client      = new PXB_HttpClient();
    $resultsData = $client->callAPI("oauth");

    if (isWoocommerceRestApiRegisteredPXB() === false) {
        storeWcDataPXB($consumer_key, $consumer_secret);
    }

    if (!empty($resultsData)) {
        $shopData = array(
            'user_id'             => get_option('wp_pxb_user'),
            'token'               => $resultsData['token'],
            'description'         => 'Store Create Request',
            'permissions'         => "read_write",
            'consumer_key'        => $consumer_key,
            'consumer_secret'     => $consumer_secret,
            'shop_email'          => getUserDataPXB()['user_email'],
            'shop_owner'          => getUserDataPXB()['display_name'],
            'shop_name'           => get_option('blogname'),
            'currency'            => get_woocommerce_currency(),
            'country_code'        => getCountryState()['country'],
            'gmt_offset_timezone' => get_option('gmt_offset'),
            'string_timezone'     => get_option('timezone_string'),
            'module'              => $module['module'],
            'code'                => $module['code'],
        );

        $resultsData = $client->callAPI("userseller", $shopData);
        if (!empty($resultsData) && isset($resultsData['user_id'])) {
            update_option('wp_pxb_old_user', get_option('wp_pxb_user'));
            update_option('wp_pxb_user', $resultsData['user_id']); //Uncomment once below is tested
            update_option('wp_pxb_module', $resultsData['module']);
            return true;
        } else if (isset($resultsData['message'])) {
            setErrorMessagePXB($resultsData['message']);
            return false;
        }
    }
}

//This function will decide to create new entry or use existing entry
function isWoocommerceRestApiRegisteredPXB()
{
    global $wpdb;
    $table = $wpdb->prefix . 'woocommerce_api_keys';

    $consumer_secret_list = $wpdb->get_results("select * from $table where description Like '%parexbridge%'");
    if (empty($consumer_secret_list)) {
        return false;
    } else {
        return true;
    }
}

function updateModuleInPXB($module)
{
    registerStorePXB($module);    
}

function resendEmailToPXBUser(){
    $client      = new PXB_HttpClient();
    $resultsData = $client->callAPI("oauth");

    if (!empty($resultsData)) {
        $userData = array(
            'token'               => $resultsData['token'],
            'pxb_secret'          => getPXBGUID(),
            'shop_email'          => getUserDataPXB()['user_email'],
            'shop_owner'          => getUserDataPXB()['display_name'],
            'shop_name'           => get_option('blogname'),
            'currency'            => get_woocommerce_currency(),
            'country_code'        => getCountryState()['country'],
            'province_code'       => getCountryState()['state'],
            'city'                => get_option('woocommerce_store_city'),
            'address1'            => get_option('woocommerce_store_address'),
            'gmt_offset_timezone' => get_option('gmt_offset'),
            'string_timezone'     => get_option('timezone_string'),
            'description'         => 'User verification Request',
        );

        $resultUserData = $client->callAPI("resendverificationcode", $userData);
        update_option('pxb_verificaitonEmail_status',1); //display error message when user > resend verify link
        return true;
    }
}

function storeWcDataPXB($consumer_key, $consumer_secret)
{
    global $wpdb;
    if (!empty($consumer_key) && !empty($consumer_secret)) {
        $data = array(
            'user_id'         => getUserDataPXB()['id'],
            'description'     => generateKeyDescPXB(),
            'permissions'     => "read_write",
            'consumer_key'    => hash_hmac('sha256', $consumer_key, 'wc-api'),
            'consumer_secret' => $consumer_secret,
            'truncated_key'   => substr($consumer_key, -7),
        );

        $wpdb->insert(
            $wpdb->prefix . 'woocommerce_api_keys',
            $data,
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
        return true;
    } else {
        return false;
    }
}

function getPXBGUID()
{
    $secret = get_option('wp_pxb_secret');

    if (!isset($secret) || trim($secret) === '') {
        $data    = random_bytes(16);
        $guidKey = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        update_option('wp_pxb_secret', $guidKey);
        return $guidKey;
    } else {
        return $secret;
    }
}

function generateKeyDescPXB()
{
    return WP_PXB_PLUGIN_NAME . "(Created at " . date('Y-m-d H:i:s') . ")";
}

function getCountryState()
{
    $countryState = array('country' => '', 'state' => '');

    // The country code /province_code
    $store_raw_country = get_option('woocommerce_default_country');

    if (isset($store_raw_country)) {
        // Split the country code/province_code
        $split_country           = explode(":", $store_raw_country);
        $countryState['country'] = $split_country[0];
        // Country code and province_code separated:
        $countryState['state'] = $split_country[1];
    }
    return $countryState;
}

function isCheckDefaultPermalinkPXB()
{
    $structure = get_option('permalink_structure');
    if (!isset($structure) || trim($structure) === '') {
        return true;
    } else {
        return false;
    }
}

function pxbRandHash() {
  if ( ! function_exists( 'openssl_random_pseudo_bytes' ) ) {
    return sha1( wp_rand() );
  }
  return bin2hex( openssl_random_pseudo_bytes( 20 ) ); 
}

function setErrorMessagePXB($error_message = "")
{
    if (empty($error_message)) {
        $error_message = WP_PXB_NOTIFICATION_ERROR_MSG;
    }

    wp_die('<div id="message" class="error"><p>' . $error_message . '</p></div>');
}

function pxb_adding_styles_and_scripts() {
    wp_enqueue_style('pxb_style', plugin_dir_url(__DIR__) . 'assets/css/pxb-style.css');
    wp_enqueue_style('pxb_bootstrap_style', plugin_dir_url(__DIR__) . 'assets/css/bootstrap.css');
    wp_enqueue_style('pxb_bootstrap_min_style', plugin_dir_url(__DIR__) . 'assets/css/bootstrap.min.css');
    wp_enqueue_script('pxb_script', plugin_dir_url(__DIR__) . 'assets/js/pxb-jquery.min.js');
}
