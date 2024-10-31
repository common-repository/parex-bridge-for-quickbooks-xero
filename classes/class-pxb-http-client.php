<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

class PXB_HttpClient
{

    public function callAPI($url, $parameters = [], $method = "POST")
    {

        $parameters['plugin_version'] = getPluginVersionPXB();
        $parameters['shop_url']       = get_option('siteurl');
        $parameters['pxb_secret']     = (!isset($parameters['pxb_secret'])) ? md5(getPXBGUID()) : $parameters['pxb_secret'];

        $request_url = WP_PXB_API_URL . "/" . $url;
        
        $request = wp_remote_post($request_url, array(
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'        => json_encode($parameters, true),
            'method'      => "POST",
            'data_format' => 'body',
        ));
        $body             = wp_remote_retrieve_body($request);

        if (is_wp_error($request)) {
            $error_string     = $request->get_error_message();
            $response_headers = wp_remote_retrieve_headers($request);
            $body             = wp_remote_retrieve_body($request);
            $results          = json_decode($body);
            setErrorMessagePXB($results->message);
        } else {
            $results = wp_remote_retrieve_body($request);

            $results = json_decode($results, true);

            if ($results['code'] == '200') {
                return $results['data'];
            } else if ($results['code'] == '400') {
                return $results;
            }
        }
    }

}
