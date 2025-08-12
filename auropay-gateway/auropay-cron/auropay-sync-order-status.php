<?php

/**
 * An external standard for AuroPay.
 *
 * @package AuroPay_Gateway_For_WooCommerce
 * @link    https://auropay.net/
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'cron_schedules', 'auropay_set_execution_cron_interval' );
/**
 * Adding custome interval
 *
 * @param string $schedules the time interval
 *
 * @return array
 */
if ( !function_exists( 'auropay_set_execution_cron_interval' ) ) {
	function auropay_set_execution_cron_interval( $schedules ) {
		$schedules['five_minute'] = array(
			'interval' => 300,
			'display' => esc_html__( 'Every five minutes', 'auropay-gateway' ),
		);
		return $schedules;
	}
}

//Setting custom hook
add_action( 'auropay_cron_hook', 'auropay_sync_order_status' );

/**
 * The event function
 *
 * @return array
 */
function auropay_sync_order_status() {
	global $wpdb;

	$orderData = auropay_fetch_order_data( $wpdb );

	foreach ( $orderData as $value ) {
		auropay_process_order( $value );
	}
}

function auropay_fetch_order_data( $wpdb ) {
	return $wpdb->get_results(
		$wpdb->prepare(
			'SELECT post_id FROM ' . $wpdb->prefix . 'postmeta AS pm WHERE meta_key = %s AND meta_value = %s',
			array( '_payment_method', 'auropay_gateway' )
		)
	);
}
function auropay_process_order( $order ) {
	$orderStatus = get_post_meta( $order->post_id, AUROPAY_ORDER_STATUS, true );
	$transactionDate = get_post_meta( $order->post_id, '_ap_transaction_date', true );

	if ( auropay_should_update_status( $orderStatus, $transactionDate ) ) {
		auropay_update_order_status( $order->post_id, $transactionDate );
	}
}

function auropay_should_update_status( $orderStatus, $transactionDate ) {
	return ( 'In Process' == $orderStatus || 'Hold' == $orderStatus ) &&
		( strtotime( current_time( 'Y-m-d H:i:s' ) ) > ( strtotime( $transactionDate ) + ( 60 * 10 ) ) );
}

function auropay_update_order_status( $orderId, $transactionDate ) {
	AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_cron_order_created_time ' . $transactionDate );

	$timeAfterTenMin = strtotime( $transactionDate ) + ( 60 * 10 );
	AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_cron_after_10_minutes_of_order_creation ' . gmdate( AUROPAY_DATE_FORMAT, $timeAfterTenMin ) );

	$currentTime = current_time( 'Y-m-d H:i:s' );
	AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_cron_run_timing ' . $currentTime );

	$refNo = get_post_meta( $orderId, '_ap_transaction_reference_number', true );
	AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_cron_order_refference_number ' . $refNo );

	$paymentData = AUROPAY_Payment_Api::auropay_get_payment_order_status_by_reference( $refNo, $orderId );
	$arpStatusArr = AUROPAY_Payment_Api::auropay_status_mapping();

	if ( -1 != $paymentData ) {
		$transactionStatus = $paymentData['transactionStatus'];
		if ( isset( $arpStatusArr[$transactionStatus] ) ) {
			update_post_meta( $orderId, '_ap_transaction_id', $paymentData['transactionId'] );
			update_post_meta( $orderId, AUROPAY_ORDER_STATUS, $arpStatusArr[$transactionStatus] );
			AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_cron_order_status ' . $arpStatusArr[$transactionStatus] );
		}
	} else {
		update_post_meta( $orderId, AUROPAY_ORDER_STATUS, 'Cancelled' );
		AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_cron_order_not_found ' );
	}
}

//Scheduling recurring event to prevent duplicate event
if ( !wp_next_scheduled( 'auropay_cron_hook' ) ) {
	wp_schedule_event( time(), 'five_minute', 'auropay_cron_hook' );
}
