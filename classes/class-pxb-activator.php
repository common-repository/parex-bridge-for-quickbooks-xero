<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

class PXB_Activator
{

    public static function activate()
    {
        if (isDependencyAvailablePXB()) {
            $result = registerUserPXB();
            if ($result != false && !empty($result)) {
                update_option('woocommerce_api_enabled', 'yes');
                add_action('activated_plugin', 'pluginActivationRedirectPXB');
            }
        }
    }

    public static function deactivate()
    {
        //Do nothing
    }
}
