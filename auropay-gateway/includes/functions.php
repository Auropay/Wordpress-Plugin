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
				$logEntry = '[' . date( 'Y-m-d H:i:s' ) . '] ' . $message . "\n";
				error_log( $logEntry, 3, $logFile );
			}
		}

		/**
		 * Log info
		 *
		 * @param string $entry information of error log
		 * @param string $mode  mode of file
		 * @param string $file  file
		 *
		 * @return string
		 */
		public static function apPluginLog( $entry, $mode = 'a', $file = 'plugin' ) {
			// Get WordPress uploads directory.
			$upload_dir = wp_upload_dir();
			$upload_dir = $upload_dir['basedir'];
			// If the entry is array, json_encode.
			if ( is_array( $entry ) ) {
				$entry = json_encode( $entry );
			}
			// Write the log file.
			$file = $upload_dir . '/' . $file . '.log';
			$file = fopen( $file, $mode );
			$bytes = fwrite( $file, current_time( 'mysql' ) . "::" . $entry . "\n" );
			fclose( $file );
			return $bytes;
		}
	}
}
