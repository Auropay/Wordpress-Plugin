<?php

/**
 * An external standard for Auropay.
 *
 * @package AuroPay_Gateway_For_WooCommerce
 * @link    https://auropay.net/
 */
if (!defined('ABSPATH')) {
	exit;
}


add_filter('cron_schedules', 'arp_set_execution_cron_interval');
/**
 * Adding custome interval
 *
 * @param string $schedules the time interval
 *
 * @return array
 */
if (!function_exists('arp_set_execution_cron_interval')) {
	function arp_set_execution_cron_interval($schedules)
	{
		$schedules['five_minute'] = array(
			'interval' => 300,
			'display' => esc_html__('Every five minutes'),
		);
		return $schedules;
	}
}

//Setting custom hook
add_action('auropay_cron_hook', 'arp_sync_order_status');

/**
 * The event function
 *
 * @return array
 */
if (!function_exists('arp_sync_order_status')) {
	function arp_sync_order_status()
	{
		global $wpdb;
		$orderData = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT post_id 
			FROM ' . $wpdb->prefix . 'postmeta AS pm WHERE meta_key = %s and meta_value = %s',
				array(
					'_payment_method',
					'auropay_gateway'
				)
			)
		);

		foreach ($orderData as $value) {

			$orderStatus = get_post_meta($value->post_id, ARP_ORDER_STATUS, true);
			$transactionDate = get_post_meta($value->post_id, '_ap_transaction_date', true);
			if ($orderStatus == 'In Process' || $orderStatus == 'Hold') {
				$timeAfterTenMin = (strtotime($transactionDate)) + (60 * 10);
				$currentTime = current_time('Y-m-d H:i:s');
				ARP_Custom_Log::log(ARP_ORDER_ID . $value->post_id . '_cron_order_created_time ' . $transactionDate);
				ARP_Custom_Log::log(ARP_ORDER_ID . $value->post_id . '_cron_after_10_minutes_of_order_creation ' . gmdate('Y-m-d H:i:s', $timeAfterTenMin));
				if (strtotime($currentTime) > $timeAfterTenMin) {
					ARP_Custom_Log::log(ARP_ORDER_ID . $value->post_id . '_cron_run_timing ' . $currentTime);
					$orderId = $value->post_id;
					$refNo = get_post_meta($orderId, '_ap_transaction_reference_number', true);
					ARP_Custom_Log::log(ARP_ORDER_ID . $orderId . '_cron_order_refference_number ' . $refNo);
					$paymentData = ARP_Payment_Api::arp_get_payment_order_status_by_reference($refNo, $orderId);
					$arpStatusArr = ARP_Payment_Api::arp_status_mapping();

					if (-1 != $paymentData) {
						if ($arpStatusArr[$paymentData['transactionStatus']]) {
							update_post_meta($orderId, '_ap_transaction_id', $paymentData['transactionId']);
							update_post_meta($orderId, ARP_ORDER_STATUS, $arpStatusArr[$paymentData['transactionStatus']]);
							ARP_Custom_Log::log(ARP_ORDER_ID . $orderId . '_cron_order_status ' . $arpStatusArr[$paymentData['transactionStatus']]);
						}
					} else {
						update_post_meta($orderId, ARP_ORDER_STATUS, 'Cancelled');
						ARP_Custom_Log::log(ARP_ORDER_ID . $orderId . '_cron_order_not_found ');
					}
				}
			}
		}
	}
}

//Scheduling recurring event to prevent duplicate event
if (!wp_next_scheduled('auropay_cron_hook')) {
	wp_schedule_event(time(), 'five_minute', 'auropay_cron_hook');
}
