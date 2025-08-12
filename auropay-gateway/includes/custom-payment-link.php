<?php

/**
 * An external standard for AuroPay.
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

define( 'AUROPAY_AMOUNT', '_amount' );
define( 'AUROPAY_TRANSACTION_DATE', '_auropay_transaction_date' );

global $auropay_all_order_data;

if ( !function_exists( 'auropay_submenu_link' ) ) {
	function auropay_submenu_link() {
		$options = get_option( 'auropay_payment' );
		$apLogging = get_option( 'auropay_logging' );
		if ( 'payment' == $options ) {
			add_submenu_page( 'auropay-settings', 'Payment Overview', 'Payments Overview', 'read_private_pages', 'payment-overview', 'auropay_payment_overview_callback' );
			add_submenu_page( 'auropay-settingss', 'Refund overview', 'Refund overview', 'read_private_pages', 'refund-overview', 'auropay_refund_overview', 0 );
			if ( 'logging' == $apLogging ) {
				add_submenu_page( 'auropay-settings', 'Logs', 'Logs', 'read_private_pages', 'auropay-log-viewer', 'auropay_logs' );
			}
		}
	}
}

/**
 * This is for creating custom log page callback
 *
 * @return void
 */
if ( !function_exists( 'auropay_logs' ) ) {
	function auropay_logs() {
		include_once AUROPAY_PLUGIN_PATH . '/includes/view/auropay-log-viewer.php';
	}
}

/**
 * This is for creating custom refund page callback
 *
 * @return void
 */
if ( !function_exists( 'auropay_refund_overview' ) ) {
	function auropay_refund_overview() {
		include_once AUROPAY_PLUGIN_PATH . '/includes/refund-overview.php';
	}
}

add_action( 'admin_enqueue_scripts', 'auropay_admin_style_js' );
/**
 * This includes js and css
 *
 * @return void
 */
if ( !function_exists( 'auropay_admin_style_js' ) ) {
	function auropay_admin_style_js() {
		wp_enqueue_style( 'auropay_admin_ui_styles', AUROPAY_PLUGIN_URL . '/assets/css/jquery-ui.css', array(), filemtime( AUROPAY_PLUGIN_PATH . '/assets/css/jquery-ui.css' ) );
		wp_enqueue_style( 'auropay_admin_styles', AUROPAY_PLUGIN_URL . '/assets/css/style.css', array(), filemtime( AUROPAY_PLUGIN_PATH . '/assets/css/style.css' ) );
		wp_enqueue_style( 'auropay_bank_icon_styles', AUROPAY_PLUGIN_URL . '/assets/css/bank-icon.css', array(), filemtime( AUROPAY_PLUGIN_PATH . '/assets/css/bank-icon.css' ) );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-blockui-js', AUROPAY_PLUGIN_URL . '/assets/js/jquery-blockui/jquery.blockUI.min.js', array( 'jquery' ), filemtime( AUROPAY_PLUGIN_PATH . '/assets/js/jquery-blockui/jquery.blockUI.min.js' ), true );
		wp_enqueue_script(
			'charts-js',
			AUROPAY_PLUGIN_URL . '/assets/js/jquery-chart/chart.js',
			array('jquery'),
			filemtime(AUROPAY_PLUGIN_PATH . '/assets/js/jquery-chart/chart.js'),
			true
		);
	}
}

/**
 * This will generate date array for selcted date range filter on payment page
 *
 * @param string $current_range date rage
 *
 * @return array
 */
if ( !function_exists( 'auropay_calculate_current_range' ) ) {
	function auropay_calculate_current_range( $current_range ) {
		global $auropay_start_date, $auropay_end_date;

		switch ( $current_range ) {
			case 'custom':
				// Handle custom date ranges submitted via request
				if ( isset( $_REQUEST['auropay-date-form-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['auropay-date-form-nonce'] ) ), 'auropay_date_form_action' ) ) {
					if ( empty( $_GET['start_date'] ) ) {
						$auropay_start_date = strtotime( 'midnight', current_time( 'timestamp' ) );
					} else {
						$auropay_start_date = max( strtotime( '-20 years' ), strtotime( sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) ) );
					}
					if ( empty( $_GET['end_date'] ) ) {
						$auropay_end_date = strtotime( 'midnight', current_time( 'timestamp' ) );
					} else {
						$auropay_end_date = strtotime( 'midnight', strtotime( sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) ) );
					}

					// Calculate the interval in months between start and end dates
					$interval = 0;
					$min_date = $auropay_start_date;
					while ( ( $min_date = strtotime( '+1 MONTH', $min_date ) ) <= $auropay_end_date ) {
						$interval++;
					}
					if ( $interval > 3 ) {
						$chart_groupby = 'month';
					} else {
						$chart_groupby = 'day';
					}
				}
				break;
			case 'year':
				// Set range to the start of the current year to the current date
				$auropay_start_date = strtotime( gmdate( 'Y-01-01', current_time( 'timestamp' ) ) );
				$auropay_end_date = strtotime( 'midnight', current_time( 'timestamp' ) );
				$chart_groupby = 'month';
				break;
			case 'last_month':
				// Set range to the previous month
				$first_day_current_month = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );
				$auropay_start_date = strtotime( gmdate( 'Y-m-01', strtotime( '-1 DAY', $first_day_current_month ) ) );
				$auropay_end_date = strtotime( gmdate( 'Y-m-t', strtotime( '-1 DAY', $first_day_current_month ) ) );
				$chart_groupby = 'day';
				break;
			case 'month':
				// Set range to the current month from the 1st to the current date
				$auropay_start_date = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );
				$auropay_end_date = strtotime( 'midnight', current_time( 'timestamp' ) );
				$chart_groupby = 'day';
				break;
			case '7day':
				// Set range to the last 7 days from midnight today
				$auropay_start_date = strtotime( '-6 days', strtotime( 'midnight', current_time( 'timestamp' ) ) );
				$auropay_end_date = strtotime( 'midnight', current_time( 'timestamp' ) );
				$chart_groupby = 'day';
				break;
			default:
				break;
		}

		// Calculate the interval and barwidth based on chart groupby
		switch ( $chart_groupby ) {
			case 'day':
				$barwidth = 60 * 60 * 24 * 1000;
				$interval = absint( ceil( max( 0, ( $auropay_end_date - $auropay_start_date ) / ( 60 * 60 * 24 ) ) ) );
				break;

			case 'month':
				$barwidth = 60 * 60 * 24 * 7 * 4 * 1000;
				$interval = 0;
				$min_date = strtotime( gmdate( 'Y-m-01', $auropay_start_date ) );
				while ( ( $min_date = strtotime( '+1 MONTH', $min_date ) ) <= $auropay_end_date ) {
					$interval++;
				}
				break;
			default:
				break;
		}

		// Format the end date correctly
		$auropay_end_date = gmdate( 'd-m-Y', $auropay_end_date ) . " 23:59:59";

		return [
			'start_date' => $auropay_start_date,
			'end_date' => strtotime( $auropay_end_date ),
			'chart_groupby' => $chart_groupby,
			'barwidth' => $barwidth,
			'interval' => $interval,
		];
	}
}

/**
 * Get the order data
 *
 * @return array
 */
function auropay_get_order_data() {
	global $auropay_tot_payments, $auropay_tot_refunded, $auropay_tot_failed, $auropay_total_all_records;
	global $total_completed_records, $total_failed_records, $auropay_total_refund_records;
	global $sale_tot_credit_card_payments, $sale_tot_debit_card_payments, $auropay_sale_tot_netbanking_payments;
	global $sale_tot_upi_payments, $sale_tot_wallet_payments, $auropay_chart_datas;
	global $refunded_tot_credit_card_payments, $refunded_tot_debit_card_payments, $auropay_refunded_tot_netbanking_payments;
	global $refunded_tot_upi_payments, $auropay_refunded_tot_wallet_payments;
	global $failed_tot_credit_card_payments, $auropay_failed_tot_debit_card_payments;
	global $failed_tot_netbanking_payments, $failed_tot_upi_payments, $auropay_failed_tot_wallet_payments;
	global $order_datas, $total_orders, $num_of_pages, $auropay_dates;

	// Initialize chart data arrays
	$auropay_chart_datas['sale_amount'] = [];
	$auropay_chart_datas['refund_amount'] = [];
	$auropay_chart_datas['failed_amount'] = [];

	list( $page_num, $limit, $order, $link_order, $range, $range_filter, $current_range, $ranges ) = auropay_initialize_settings();
	$order_data = auropay_get_order_query( $order );

	// Process each order's data
	foreach ( $order_data as $value ) {
		$auropay_dates = auropay_calculate_current_range( $range );
		$transaction_date = get_post_meta( $value->post_id, AUROPAY_TRANSACTION_DATE, true );
		$order_status = get_post_meta( $value->post_id, '_auropay_order_status', true );
		$transaction_type = get_post_meta( $value->post_id, '_auropay_transaction_channel_type', true );
		$order_date = get_post_meta( $value->post_id, AUROPAY_TRANSACTION_DATE, true );
		$order_date = gmdate( 'd-m-Y', strtotime( $order_date ) );
		$order_date = strtotime( $order_date );

		$sale_amount = get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
		$sale_amount = number_format( (float) $sale_amount, 2, '.', '' );
		if ( strtotime( $transaction_date ) >= $auropay_dates['start_date'] && strtotime( $transaction_date ) < $auropay_dates['end_date'] ) {
			$auropay_total_all_records++;
			if ( isset( $_REQUEST['auropay-date-form-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['auropay-date-form-nonce'] ) ), 'auropay_date_form_action' ) ) {
				$transaction_status = isset( $_GET['transaction_status'] ) ? sanitize_text_field( wp_unslash( $_GET['transaction_status'] ) ) : '';
			} else {
				$transaction_status = '';
			}

			// Handle 'Authorized' orders
			if ( 'Authorized' == $order_status ) {
				if ( 'completed' == $transaction_status ) {
					$order_datas['order_id'][] = $value->post_id;
				}
				$total_completed_records++;

				if ( 3 == $transaction_type ) {
					$sale_tot_credit_card_payments = $sale_tot_credit_card_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 4 == $transaction_type ) {
					$sale_tot_debit_card_payments = $sale_tot_debit_card_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 6 == $transaction_type ) {
					$sale_tot_upi_payments = $sale_tot_upi_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 7 == $transaction_type ) {
					$auropay_sale_tot_netbanking_payments = $auropay_sale_tot_netbanking_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 8 == $transaction_type ) {
					$sale_tot_wallet_payments = $sale_tot_wallet_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}

				$auropay_tot_payments = $auropay_tot_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				if ( isset( $auropay_chart_datas['sale_amount'][$order_date] ) ) {
					$auropay_chart_datas['sale_amount'][$order_date] += $sale_amount;
				} else {
					$auropay_chart_datas['sale_amount'][$order_date] = $sale_amount;
				}
			} elseif ( 'Failed' == $order_status ) {
				if ( 'failed' == $transaction_status ) {
					$order_datas['order_id'][] = $value->post_id;
				}

				if ( 3 == $transaction_type ) {
					$failed_tot_credit_card_payments = $failed_tot_credit_card_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 4 == $transaction_type ) {
					$auropay_failed_tot_debit_card_payments = $auropay_failed_tot_debit_card_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 6 == $transaction_type ) {
					$failed_tot_upi_payments = $failed_tot_upi_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 7 == $transaction_type ) {
					$failed_tot_netbanking_payments = $failed_tot_netbanking_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 8 == $transaction_type ) {
					$auropay_failed_tot_wallet_payments = $auropay_failed_tot_wallet_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}

				$total_failed_records++;
				$auropay_tot_failed = $auropay_tot_failed + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );

				if ( isset( $auropay_chart_datas['failed_amount'][$order_date] ) ) {
					$auropay_chart_datas['failed_amount'][$order_date] += $sale_amount;
				} else {
					$auropay_chart_datas['failed_amount'][$order_date] = $sale_amount;
				}
			} elseif ( 'Refunded' == $order_status ) {
				if ( 'refund' == $transaction_status ) {
					$order_datas['order_id'][] = $value->post_id;
				}
				if ( 3 == $transaction_type ) {
					$refunded_tot_credit_card_payments = $refunded_tot_credit_card_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 4 == $transaction_type ) {
					$refunded_tot_debit_card_payments = $refunded_tot_debit_card_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 6 == $transaction_type ) {
					$refunded_tot_upi_payments = $refunded_tot_upi_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 7 == $transaction_type ) {
					$auropay_refunded_tot_netbanking_payments = $auropay_refunded_tot_netbanking_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				if ( 8 == $transaction_type ) {
					$auropay_refunded_tot_wallet_payments = $auropay_refunded_tot_wallet_payments + get_post_meta( $value->post_id, AUROPAY_AMOUNT, true );
				}
				$auropay_total_refund_records++;
				$auropay_tot_refunded = $auropay_tot_refunded + get_post_meta( $value->post_id, '_refund_amount', true );

				if ( isset( $auropay_chart_datas['refund_amount'][$order_date] ) ) {
					$auropay_chart_datas['refund_amount'][$order_date] += $sale_amount;
				} else {
					$auropay_chart_datas['refund_amount'][$order_date] = $sale_amount;
				}
			}
			if ( 'all' == $transaction_status || empty( $transaction_status ) ) {
				$order_datas['order_id'][] = $value->post_id;
			}
		}
	}

	$order_csv_data = $order_datas['order_id'] ?? [];
	if ( !empty( $order_datas['order_id'] ) ) {
		$total_orders = count( $order_datas['order_id'] );
		$num_of_pages = ceil( $total_orders / $limit );
		$order_datas = array_chunk( $order_datas['order_id'], $limit );
		if ( $page_num ) {
			$pagenum = $page_num - 1;
			$order_datas = $order_datas[$pagenum];
		}
	}

	( !empty( $auropay_chart_datas['sale_amount'] ) ) ? ksort( $auropay_chart_datas['sale_amount'] ) : $auropay_chart_datas['sale_amount'];
	( !empty( $auropay_chart_datas['failed_amount'] ) ) ? ksort( $auropay_chart_datas['failed_amount'] ) : $auropay_chart_datas['failed_amount'];
	( !empty( $auropay_chart_datas['refund_amount'] ) ) ? ksort( $auropay_chart_datas['refund_amount'] ) : $auropay_chart_datas['refund_amount'];

	// Handle sale amounts for each payment type (Credit Card, Debit Card, etc.)
	$sale_tot_credit_card_payments = number_format( (float) $sale_tot_credit_card_payments, 2, '.', '' );
	$sale_tot_debit_card_payments = number_format( (float) $sale_tot_debit_card_payments, 2, '.', '' );
	$auropay_sale_tot_netbanking_payments = number_format( (float) $auropay_sale_tot_netbanking_payments, 2, '.', '' );
	$sale_tot_upi_payments = number_format( (float) $sale_tot_upi_payments, 2, '.', '' );
	$sale_tot_wallet_payments = number_format( (float) $sale_tot_wallet_payments, 2, '.', '' );

	$refunded_tot_credit_card_payments = number_format( (float) $refunded_tot_credit_card_payments, 2, '.', '' );
	$refunded_tot_debit_card_payments = number_format( (float) $refunded_tot_debit_card_payments, 2, '.', '' );
	$auropay_refunded_tot_netbanking_payments = number_format( (float) $auropay_refunded_tot_netbanking_payments, 2, '.', '' );
	$refunded_tot_upi_payments = number_format( (float) $refunded_tot_upi_payments, 2, '.', '' );
	$auropay_refunded_tot_wallet_payments = number_format( (float) $auropay_refunded_tot_wallet_payments, 2, '.', '' );

	$failed_tot_credit_card_payments = number_format( (float) $failed_tot_credit_card_payments, 2, '.', '' );
	$auropay_failed_tot_debit_card_payments = number_format( (float) $auropay_failed_tot_debit_card_payments, 2, '.', '' );
	$failed_tot_netbanking_payments = number_format( (float) $failed_tot_netbanking_payments, 2, '.', '' );
	$failed_tot_upi_payments = number_format( (float) $failed_tot_upi_payments, 2, '.', '' );
	$auropay_failed_tot_wallet_payments = number_format( (float) $auropay_failed_tot_wallet_payments, 2, '.', '' );

	if ( !empty( $transaction_status ) ) {
		if ( 'failed' == $transaction_status ) {
			$total_items = $total_failed_records;
		}
		if ( 'completed' == $transaction_status ) {
			$total_items = $total_completed_records;
		}
		if ( 'refund' == $transaction_status ) {
			$total_items = $auropay_total_refund_records;
		}
		if ( 'all' == $transaction_status ) {
			$total_items = $total_orders;
		}
	} else {
		$total_items = $total_orders;
	}

	if ( $auropay_tot_payments > 0 ) {
		$auropay_tot_payments = round( $auropay_tot_payments, 2 );
	} else {
		$auropay_tot_payments = 0;
	}
	if ( $auropay_tot_failed > 0 ) {
		$auropay_tot_failed = round( $auropay_tot_failed, 2 );
	} else {
		$auropay_tot_failed = 0;
	}
	if ( $auropay_tot_refunded > 0 ) {
		$auropay_tot_refunded = round( $auropay_tot_refunded, 2 );
	} else {
		$auropay_tot_refunded = 0;
	}
	$auropay_tot_payments = number_format( (float) $auropay_tot_payments, 2, '.', '' );
	$auropay_tot_failed = number_format( (float) $auropay_tot_failed, 2, '.', '' );
	$auropay_tot_refunded = number_format( (float) $auropay_tot_refunded, 2, '.', '' );

	//generate pagination link
	$auropay_all_order_data['page_links'] = auropay_generate_pagination( $num_of_pages, $page_num );
	$auropay_all_order_data['ranges'] = $ranges;
	$auropay_all_order_data['order_datas'] = $order_datas;
	$auropay_all_order_data['start_date'] = $auropay_dates['start_date'] ?? 0;
	$auropay_all_order_data['order_csv_data'] = $order_csv_data;
	$auropay_all_order_data['chart_datas'] = $auropay_chart_datas;
	$auropay_all_order_data['chart_groupby'] = $auropay_dates['chart_groupby'] ?? 0;
	$auropay_all_order_data['interval'] = $auropay_dates['interval'] ?? 0;
	$auropay_all_order_data['barwidth'] = $auropay_dates['barwidth'] ?? 0;
	$auropay_all_order_data['current_range'] = $current_range;
	$auropay_all_order_data['range_filter'] = $range_filter;
	$auropay_all_order_data['link_order'] = $link_order;
	$auropay_all_order_data['total_all_records'] = $auropay_total_all_records;
	$auropay_all_order_data['total_items'] = $total_items;
	$auropay_all_order_data['total_completed_records'] = $total_completed_records;
	$auropay_all_order_data['total_failed_records'] = $total_failed_records;
	$auropay_all_order_data['total_refund_records'] = $auropay_total_refund_records;

	$auropay_all_order_data['sale_tot_credit_card_payments'] = $sale_tot_credit_card_payments;
	$auropay_all_order_data['sale_tot_debit_card_payments'] = $sale_tot_debit_card_payments;
	$auropay_all_order_data['sale_tot_netbanking_payments'] = $auropay_sale_tot_netbanking_payments;
	$auropay_all_order_data['sale_tot_wallet_payments'] = $sale_tot_wallet_payments;
	$auropay_all_order_data['sale_tot_upi_payments'] = $sale_tot_upi_payments;

	$auropay_all_order_data['failed_tot_credit_card_payments'] = $failed_tot_credit_card_payments;
	$auropay_all_order_data['failed_tot_debit_card_payments'] = $auropay_failed_tot_debit_card_payments;
	$auropay_all_order_data['failed_tot_netbanking_payments'] = $failed_tot_netbanking_payments;
	$auropay_all_order_data['failed_tot_wallet_payments'] = $auropay_failed_tot_wallet_payments;
	$auropay_all_order_data['failed_tot_upi_payments'] = $failed_tot_upi_payments;

	$auropay_all_order_data['refunded_tot_credit_card_payments'] = $refunded_tot_credit_card_payments;
	$auropay_all_order_data['refunded_tot_debit_card_payments'] = $refunded_tot_debit_card_payments;
	$auropay_all_order_data['refunded_tot_netbanking_payments'] = $auropay_refunded_tot_netbanking_payments;
	$auropay_all_order_data['refunded_tot_wallet_payments'] = $auropay_refunded_tot_wallet_payments;
	$auropay_all_order_data['refunded_tot_upi_payments'] = $refunded_tot_upi_payments;

	return $auropay_all_order_data;
}

/**
 * Initialize the order data
 *
 * @return array
 */
function auropay_initialize_settings() {
	// Default settings
	$page_num = 1;
	$order = 'desc';
	$limit = 10;
	$link_order = ( "asc" == $order ) ? "desc" : "asc";
	$range = isset( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : "7day";
	$current_range = !empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : '7day';

	// Validate the current range, if it's invalid, set to '7day'
	if ( !in_array( $current_range, array( 'custom', 'year', 'last_month', '7day', 'month' ) ) ) {
		$current_range = '7day';
	}
	// Define possible range options
	$ranges = array(
		'7day' => __( 'Last 7 Days', 'auropay-gateway' ),
		'month' => __( 'Day to Month', 'auropay-gateway' ),
		'last_month' => __( 'Last Month', 'auropay-gateway' ),
		'year' => __( 'Day to Year', 'auropay-gateway' ),
		'custom' => __( 'Custom', 'auropay-gateway' ),
	);
	$range_filter = "range=" . $range;

	// If the range is 'custom', add custom date parameters to the filter
	if ( 'custom' == $range ) {
		$cstart_date = '';
		$cend_date = '';
		$range_filter .= '&start_date=' . $cstart_date . '&end_date=' . $cend_date;
	}

	// Check if the nonce for the date form is valid and process settings accordingly
	if ( isset( $_REQUEST['auropay-date-form-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['auropay-date-form-nonce'] ) ), 'auropay_date_form_action' ) ) {
		$page_num = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
		$order = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : "desc";
		$link_order = ( "asc" == $order ) ? "desc" : "asc";
		$range = isset( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : "7day";
		$current_range = !empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : '7day';

		if ( 'custom' == $range ) {
			$cstart_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
			$cend_date = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
			$range_filter .= '&start_date=' . $cstart_date . '&end_date=' . $cend_date;
		}

		return [$page_num, $limit, $order, $link_order, $range, $range_filter, $current_range, $ranges];
	} else {
		return [$page_num, $limit, $order, $link_order, $range, $range_filter, $current_range, $ranges];
	}
}

/**
 * Get the order query
 *
 * @return array
 */
function auropay_get_order_query( $order ) {
	global $wpdb;
	$meta_key_payment_method = '_payment_method';
	$meta_value_auropay_gateway = 'auropay_gateway';
	$meta_key_transaction_date = AUROPAY_TRANSACTION_DATE;
	$date_format = '%d-%m-%Y %H:%i:%s';

	if ( 'desc' == $order ) {
		$order_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, date_meta.meta_value AS transaction_date
            FROM {$wpdb->prefix}postmeta AS pm
            INNER JOIN {$wpdb->prefix}postmeta AS date_meta
            ON pm.post_id = date_meta.post_id
            WHERE pm.meta_key = %s
            AND pm.meta_value = %s
            AND date_meta.meta_key = %s
            ORDER BY STR_TO_DATE(date_meta.meta_value, %s) DESC",
				$meta_key_payment_method,
				$meta_value_auropay_gateway,
				$meta_key_transaction_date,
				$date_format
			)
		);
	} else {
		$order_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, date_meta.meta_value AS transaction_date
            FROM {$wpdb->prefix}postmeta AS pm
            INNER JOIN {$wpdb->prefix}postmeta AS date_meta
            ON pm.post_id = date_meta.post_id
            WHERE pm.meta_key = %s
            AND pm.meta_value = %s
            AND date_meta.meta_key = %s
            ORDER BY STR_TO_DATE(date_meta.meta_value, %s) ASC",
				$meta_key_payment_method,
				$meta_value_auropay_gateway,
				$meta_key_transaction_date,
				$date_format
			)
		);
	}
	return $order_data;
}

/**
 * Generate pagination
 *
 * @return array
 */
function auropay_generate_pagination( $num_of_pages, $page_num ) {
	return paginate_links(
		array(
			'base' => add_query_arg( 'pagenum', '%#%' ),
			'format' => '?paged=%#%',
			'prev_text' => __( '<div class="next-page button"><</div>', 'auropay-gateway' ),
			'next_text' => __( '<div class="next-page button">></div>', 'auropay-gateway' ),
			'total' => $num_of_pages,
			'current' => $page_num,
			'show_all' => false,
			'type' => 'plain',
			'end_size' => 2,
			'mid_size' => 2,
			'prev_next' => true,
			'add_args' => false,
			'add_fragment' => '',
		)
	);
}

/**
 * This will export the data
 *
 * @return void
 */
if ( !function_exists( 'auropay_csv_pdf_export' ) ) {
	function auropay_csv_pdf_export() {
		include_once AUROPAY_PLUGIN_PATH . '/includes/export.php';
		$transaction_data = auropay_get_order_data();

		if ( isset( $_POST['export_type'] ) &&
			!empty( $_POST['export_type'] ) &&
			isset( $transaction_data['order_csv_data'] ) &&
			$transaction_data['order_csv_data'] &&
			isset( $_REQUEST['auropay-export-form-nonce'] ) &&
			wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_REQUEST['auropay-export-form-nonce'] ) ),
				'auropay_export_form_action'
			)
		) {
			auropay_export_data( sanitize_text_field( wp_unslash( $_POST["export_type"] ) ), $transaction_data['order_csv_data'] );

		}
	}
}

if (
	isset( $_POST['Export'] ) &&
	isset( $_REQUEST['auropay-export-form-nonce'] ) &&
	wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_REQUEST['auropay-export-form-nonce'] ) ),
		'auropay_export_form_action'
	)
) {
	auropay_csv_pdf_export();
}

/**
 * This is the callback for payment overview
 *
 * @return void
 */
if ( !function_exists( 'auropay_payment_overview_callback' ) ) {
	function auropay_payment_overview_callback() {
		$auropay_all_order_data = auropay_get_order_data();
		$ranges = $auropay_all_order_data['ranges'];
		$range_filter = $auropay_all_order_data['range_filter'];
		$current_range = $auropay_all_order_data['current_range'];
		include_once AUROPAY_PLUGIN_PATH . '/includes/view/payment-overview-view.php';
	}
}
