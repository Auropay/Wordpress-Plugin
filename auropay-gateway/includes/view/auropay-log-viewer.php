<?php
if (!defined('ABSPATH')) {
	exit;
}

//Path of log file
$arpLogFile = WP_CONTENT_DIR . '/uploads/arp-logs/auropay-gateway.log';

// Check if the log file exists
if (file_exists($arpLogFile)) {
	$fiveDaysAgo = date('Y-m-d', strtotime('-10 days'));
	$arpLogEntries = file($arpLogFile, FILE_IGNORE_NEW_LINES);
	// Read and display log entries
	foreach ($arpLogEntries as $logEntry) {
		// Extract the date from each log entry (assuming date format is consistent)
		preg_match('/^\[(.*?)\]/', $logEntry, $matches);

		if (!empty($matches[1])) {
			$log_date = date('Y-m-d', strtotime($matches[1]));

			// Check if the log entry date is within the last 5 days
			if ($log_date >= $fiveDaysAgo) {
				echo '<pre>' . esc_html($logEntry,'auropay-gateway') . '</pre>';
			}
		}
	}
} else {
	echo 'Log not found.';
}