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
if (!defined('ABSPATH')) {
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
class Custom_Log_Functions
{
    /**
     * Log
     *
     * @param $message error message
     * @param string $level   information of error log
     * 
     * @return void
     */
    public static function log($message, $level = 'info')
    {
        $logging = get_option('ap_logging');

        if ($logging == 'logging') {
            $log = error_log($message);
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
    static function apPluginLog($entry, $mode = 'a', $file = 'plugin')
    {
        // Get WordPress uploads directory.
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        // If the entry is array, json_encode.
        if (is_array($entry)) {
            $entry = json_encode($entry);
        }
        // Write the log file.
        $file  = $upload_dir . '/' . $file . '.log';
        $file  = fopen($file, $mode);
        $bytes = fwrite($file, current_time('mysql') . "::" . $entry . "\n");
        fclose($file);
        return $bytes;
    }
}
