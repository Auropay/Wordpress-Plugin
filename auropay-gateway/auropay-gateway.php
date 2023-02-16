<?php

/**
 * Plugin Name: AuroPay Gateway for wordpress 
 * Plugin URI: https://auropay.net/ 
 * Description: Custom payment gateway powered by AuroPay.
 * Author: AuroPay
 * Author URI: https://auropay.net/
 * Version: 1.0.0
 * Requires at least: 5.6
 * Tested up to: 5.6
 * Requires PHP: 7.4
 * 
 * @category Payment
 * @package  AuroPay_Gateway_For_Wordpress
 * @author   Akshita Minocha <akshita.minocha@aurionpro.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://auropay.net/
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AUROPAY_MAIN_FILE', __FILE__);
define('AUROPAY_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('AUROPAY_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('AUROPAY_ACCESS_KEY', 'cGF5bWVudCBnYXRld2F5MDA5=dfsdfsdfdsfsdf432423423434+sfjejd9');
define('AUROPAY_PLUGIN_NAME', 'Auropay Gateway');
define('AUROPAY_TIMEZONE', 'Asia/Kolkata');

if (get_option('ap_payment_mode') == 'test_mode') {
    define('APIURL', get_option('ap_test_api_url'));
    define('ACCESSKEY', get_option('ap_test_access_key'));
    define('SECRETKEY', get_option('ap_test_secret_key'));
} else {
    define('APIURL', get_option('ap_api_url'));
    define('ACCESSKEY', get_option('ap_access_key'));
    define('SECRETKEY', get_option('ap_secret_key'));
}

if (is_admin()) {
    add_filter(
        'plugin_action_links_' . plugin_basename(__FILE__),
        function ($links) {

            $plugin_links = array(
                '<a href="admin.php?page=auropay-settings">' . esc_html__('Settings', 'auropay-settings') . '</a>'
            );
            return array_merge($plugin_links, $links);
        }
    );
}

require_once plugin_dir_path(__FILE__) . '/includes/auropay-settings.php';
require_once plugin_dir_path(__FILE__) . '/includes/functions.php';
require_once plugin_dir_path(__FILE__) . '/includes/class-auropay-api.php';
require_once plugin_dir_path(__FILE__) . '/includes/auropay-place-order.php';
require_once plugin_dir_path(__FILE__) . '/includes/callback-payment-status.php';

add_action('admin_menu', 'registerCustomSubmenu');
/**
 * This sets Wordpress timeout to 30 seconds to counter Curl timeout error
 * 
 * @return void
 */
function registerCustomSubmenu()
{
    include_once  AUROPAY_PLUGIN_PATH . '/includes/custom-payment-link.php';
    apSubmenuLink();
}
