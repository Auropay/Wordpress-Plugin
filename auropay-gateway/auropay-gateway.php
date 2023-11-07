<?php

/**
 * Plugin Name: Auropay Gateway
 * Plugin URI: https://auropay.net/
 * Description: Custom payment gateway powered by AuroPay.
 * Version: 1.0.0
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: Akshita Minocha
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Tested up to: 5.6
 *
 * @package  Auropay_Gateway
 * @link     https://auropay.net/
 */

if (!defined('ABSPATH')) {
	exit;
}

define('AUROPAY_MAIN_FILE', __FILE__);
define('ARP_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('ARP_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('ARP_ACCESS_KEY', 'cGF5bWVudCBnYXRld2F5MDA5=dfsdfsdfdsfsdf432423423434+sfjejd9');
define('ARP_PLUGIN_NAME', 'Auropay Gateway');
define('ARP_TIMEZONE', 'Asia/Kolkata');
define('ARP_ORDER_STATUS', '_auropay_order_status');
define('ARP_ORDER_ID', 'orderid');
define('ARP_DATE_FORMAT', 'd-m-Y H:i:s');
define('ARP_PAYMENT_ID', 'Payment Id');
define('ARP_PAYMENT_DETAIL', 'Payment Detail');

if (get_option('ap_payment_mode') == 'test_mode') {
	define('ARP_APIURL', get_option('ap_test_api_url'));
	define('ARP_ACCESSKEY', get_option('ap_test_access_key'));
	define('ARP_SECRETKEY', get_option('ap_test_secret_key'));
} else {
	define('ARP_APIURL', get_option('ap_api_url'));
	define('ARP_ACCESSKEY', get_option('ap_access_key'));
	define('ARP_SECRETKEY', get_option('ap_secret_key'));
}

if (is_admin()) {
	add_filter(
		'plugin_action_links_' . plugin_basename(__FILE__),
		function ($links) {
			$pluginLinks = array(
				'<a href="admin.php?page=auropay-settings">' . esc_html__('Settings', 'auropay-settings') . '</a>',
			);
			return array_merge($pluginLinks, $links);
		}
	);
}

require_once plugin_dir_path(__FILE__) . '/includes/auropay-settings.php';
require_once plugin_dir_path(__FILE__) . '/includes/functions.php';
require_once plugin_dir_path(__FILE__) . '/includes/class-auropay-api.php';
require_once plugin_dir_path(__FILE__) . '/includes/auropay-place-order.php';
require_once plugin_dir_path(__FILE__) . '/includes/callback-payment-status.php';
require_once plugin_dir_path(__FILE__) . '/auropay-cron/auropay-sync-order-status.php';

add_action('admin_menu', 'arp_register_custom_submenu');
/**
 * This sets Wordpress timeout to 30 seconds to counter Curl timeout error
 *
 * @return void
 */
if (!function_exists('arp_register_custom_submenu')) {
	function arp_register_custom_submenu()
	{
		include_once ARP_PLUGIN_PATH . '/includes/custom-payment-link.php';
		arp_submenu_link();
	}
}
