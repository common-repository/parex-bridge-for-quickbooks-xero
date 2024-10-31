<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

/**
 * Plugin Name: Parex Bridge for QuickBooks & Xero
 * Plugin URI: http://www.parextech.com/parex-bridge-for-quickbooks-and-xero
 * Description: This Plugin will help you to integrate your marketplace order's account with QuickBooks and Xero.
 * Version: 1.0.3
 * Author: Parex Technologies
 * Author URI: http://www.parextech.com
 */

require plugin_dir_path(__FILE__) . 'config/const.php';
require plugin_dir_path(__FILE__) . 'classes/class-pxb.php';

register_activation_hook(__FILE__, 'activatePluginPXB');
register_deactivation_hook( __FILE__, 'pluginDeactivatePXB' );

$px_plugin = new PXB_Controller();
$px_plugin->runPX();

if (isCheckWoocommerceAvailablePXB()) {
    add_action('admin_menu', 'addAdminMenuPXB');
}


add_action('admin_enqueue_scripts', 'pxb_adding_styles_and_scripts');
add_action('wp_ajax_pxb_action', 'updateModuleInPXB');
add_action('wp_ajax_pxb_sendEmail', 'resendEmailToPXBUser');