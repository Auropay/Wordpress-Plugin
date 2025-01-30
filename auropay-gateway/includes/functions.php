<?php

/**
 * Export data in csv and pdf format
 *
 * @category Payment
 * @package  AuroPay_Gateway_For_Wordpress
 * @author   Akshita Minocha <akshita.minocha@aurionpro.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://auropay.net/
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save the error logs
 *
 * @category Payment
 * @package  AuroPay_Gateway_For_Wordpress
 * @author   Akshita Minocha <akshita.minocha@aurionpro.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://auropay.net/
 */
if ( !class_exists( 'AUROPAY_Custom_Log' ) ) {
	class AUROPAY_Custom_Log {
		/**
		 * Log
		 *
		 * @param $message error message
		 * @param string $level   information of error log
		 *
		 * @return void
		 */
		public static function log( $message ) {
			$logging = get_option( 'auropay_logging' );
			if ( 'logging' == $logging ) {
				$upload_dir = wp_upload_dir();
				$upload_path = $upload_dir['basedir'] . '/auropay-gateway';

				if ( !file_exists( $upload_path ) ) {
					wp_mkdir_p( $upload_path );
				}

				$logFile = $upload_path . '/auropay-gateway.log';
				$logEntry = '[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message . "\n";
				error_log( $logEntry, 3, $logFile );
			}
		}
	}
}
