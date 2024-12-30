<?php

/**
 * Plugin Name: Auropay Gateway
 * Plugin URI: https://auropay.net/
 * Text Domain: auropay-gateway
 * Description: Custom payment gateway powered by AuroPay.
 * Version: 1.0.4
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: Akshita Minocha
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Tested up to: 6.7
 *
 * @package  Auropay_Gateway
 * @link     https://auropay.net/
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AUROPAY_MAIN_FILE', __FILE__ );
define( 'AUROPAY_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'AUROPAY_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'AUROPAY_ACCESS_KEY', 'cGF5bWVudCBnYXRld2F5MDA5=dfsdfsdfdsfsdf432423423434+sfjejd9' );
define( 'AUROPAY_PLUGIN_NAME', 'Auropay Gateway' );
define( 'AUROPAY_TIMEZONE', 'Asia/Kolkata' );
define( 'AUROPAY_ORDER_STATUS', '_auropay_order_status' );
define( 'AUROPAY_ORDER_ID', 'orderid' );
define( 'AUROPAY_DATE_FORMAT', 'd-m-Y H:i:s' );
define( 'AUROPAY_PAYMENT_ID', 'Payment Id' );
define( 'AUROPAY_PAYMENT_DETAIL', 'Payment Detail' );

if ( get_option( 'auropay_payment_mode' ) == 'test_mode' ) {
	define( 'AUROPAY_APIURL', get_option( 'auropay_test_api_url' ) );
	define( 'AUROPAY_ACCESSKEY', get_option( 'auropay_test_access_key' ) );
	define( 'AUROPAY_SECRETKEY', get_option( 'auropay_test_secret_key' ) );
} else {
	define( 'AUROPAY_APIURL', get_option( 'auropay_api_url' ) );
	define( 'AUROPAY_ACCESSKEY', get_option( 'auropay_access_key' ) );
	define( 'AUROPAY_SECRETKEY', get_option( 'auropay_secret_key' ) );
}

if ( is_admin() ) {
	add_filter(
		'plugin_action_links_' . plugin_basename( __FILE__ ),
		function ( $links ) {
			$pluginLinks = array(
				'<a href="admin.php?page=auropay-settings">' . esc_html__( 'Settings', 'auropay-gateway' ) . '</a>',
			);
			return array_merge( $pluginLinks, $links );
		}
	);
}

require_once plugin_dir_path( __FILE__ ) . '/includes/auropay-settings.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/functions.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/class-auropay-api.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/auropay-place-order.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/callback-payment-status.php';
require_once plugin_dir_path( __FILE__ ) . '/auropay-cron/auropay-sync-order-status.php';

add_action( 'admin_menu', 'auropay_register_custom_submenu' );
/**
 * This sets Wordpress timeout to 30 seconds to counter Curl timeout error
 *
 * @return void
 */
if ( !function_exists( 'auropay_register_custom_submenu' ) ) {
	function auropay_register_custom_submenu() {
		include_once AUROPAY_PLUGIN_PATH . '/includes/custom-payment-link.php';
		auropay_submenu_link();
	}
}
