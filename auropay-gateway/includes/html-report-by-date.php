<?php

/**
 * An external standard for Auropay.
 *
 * @category Payment
 * @package  AuroPay_Gateway_For_Wordpress
 * @author   Akshita Minocha <akshita.minocha@aurionpro.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://auropay.net/
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $wp_locale;
// Sanitize the array values
$sanitizedMonthAbbrev = array_map( 'esc_html', array_values( $wp_locale->month_abbrev ) );

// Encode the sanitized array into JSON
$jsonMonthAbbrev = wp_json_encode( $sanitizedMonthAbbrev );

$chart_groupby = $auropay_all_order_data['chart_groupby'];
$barwidth = $auropay_all_order_data['barwidth'];
$start_date = $auropay_all_order_data['start_date'];
$interval = $auropay_all_order_data['interval'];
$allChartData = $auropay_all_order_data['chart_datas'];

//configure color of chart
$chart_colours = array(
	'sales_amount' => 'green',
	'net_sales_amount' => '#3498db',
	'average' => '#b1d4ea',
	'net_average' => '#3498db',
	'order_count' => '#dbe1e3',
	'item_count' => '#ecf0f1',
	'shipping_amount' => '#5cc488',
	'coupon_amount' => '#f1c40f',
	'refund_amount' => 'orange',
	'failed_amount' => 'red',
);

/**
 * This return the date time and values array
 *
 * @param array  $data       order data
 * @param string $data_key   order key
 * @param int    $interval   time interval
 * @param string $start_date start date
 * @param string $group_by   date range
 *
 * @return array
 */

function auropay_prepare_chart_data( $data, $data_key, $interval, $start_date, $group_by ) {
	$prepared_data = array();

	if ( 'day' === $group_by ) {
		$prepared_data = auropay_initialize_day_data( $interval, $start_date );
	} else {
		$prepared_data = auropay_initialize_month_data( $interval, $start_date );
	}

	auropay_populate_data( $prepared_data, $data, $data_key, $group_by );

	return $prepared_data;
}

function auropay_initialize_day_data( $interval, $start_date ) {
	$data = array();
	for ( $i = 0; $i <= $interval; $i++ ) {
		$time = strtotime( gmdate( 'Ymd', strtotime( "+{$i} DAY", $start_date ) ) ) . '000';
		$data[$time] = array( esc_js( $time ), 0 );
	}
	return $data;
}

function auropay_initialize_month_data( $interval, $start_date ) {
	$data = array();
	$current_yearnum = gmdate( 'Y', $start_date );
	$current_monthnum = gmdate( 'm', $start_date );

	for ( $i = 0; $i <= $interval; $i++ ) {
		$time = strtotime( $current_yearnum . str_pad( $current_monthnum, 2, '0', STR_PAD_LEFT ) . '01' ) . '000';
		$data[$time] = array( esc_js( $time ), 0 );

		$current_monthnum++;

		if ( $current_monthnum > 12 ) {
			$current_monthnum = 1;
			$current_yearnum++;
		}
	}
	return $data;
}

function auropay_populate_data( &$prepared_data, $data, $data_key, $group_by ) {
	foreach ( $data as $key => $value ) {
		if ( $key !== $data_key ) {
			continue;
		}

		foreach ( $value as $k => $v ) {
			$time = ( 'day' === $group_by )
			? strtotime( gmdate( 'Ymd', $k ) ) . '000'
			: strtotime( gmdate( 'Ym', $k ) . '01' ) . '000';

			if ( 'day' === $group_by ) {
				$prepared_data[$time][1] = $v;
			} else {
				$prepared_data[$time][1] += $v;
			}
		}
	}
}

// Define the sanitizing function
function auropay_sanitize_chart_data( $data ) {
	return array_map( function ( $item ) {
		$timestamp = $item[0];

		// Sanitize the value, ensuring it's a float or integer
		if ( is_numeric( $item[1] ) ) {
			$value = $item[1] + 0; // Converts the value to the appropriate numeric type
		} else {
			$value = 0; // Or handle as appropriate (e.g., set to 0 or throw an error)
		}

		return [$timestamp, $value];
	}, $data );
}

// Prepare the chart data
$ChartDataArr = array(
	'sale_amount' => auropay_sanitize_chart_data( auropay_prepare_chart_data( $allChartData, 'sale_amount', $interval, $start_date, $chart_groupby ) ),
	'refund_amount' => auropay_sanitize_chart_data( auropay_prepare_chart_data( $allChartData, 'refund_amount', $interval, $start_date, $chart_groupby ) ),
	'failed_amount' => auropay_sanitize_chart_data( auropay_prepare_chart_data( $allChartData, 'failed_amount', $interval, $start_date, $chart_groupby ) ),
);

// Encode the sanitized data to JSON
$chart_data = wp_json_encode(
	array(
		'sale_amount' => !empty( $ChartDataArr['sale_amount'] ) ? array_values( $ChartDataArr['sale_amount'] ) : [],
		'refund_amount' => !empty( $ChartDataArr['refund_amount'] ) ? array_values( $ChartDataArr['refund_amount'] ) : [],
		'failed_amount' => !empty( $ChartDataArr['failed_amount'] ) ? array_values( $ChartDataArr['failed_amount'] ) : [],
	)
);

?>

<?php
function auropay_enqueue_report( $current_range, $chart_colours, $barwidth, $chart_groupby, $chart_data, $jsonMonthAbbrev ) {
	// Register the JavaScript file
	wp_enqueue_script(
		'report-by-date-js',
		AUROPAY_PLUGIN_URL . '/assets/js/time-report-by-date.js',
		array( 'jquery', 'jquery-ui-datepicker' ),
		filemtime( AUROPAY_PLUGIN_PATH . '/assets/js/time-report-by-date.js' ),
		true
	);

	// Pass dynamic data from PHP to JavaScript (current_range)
	wp_localize_script( 'report-by-date-js', 'report_vars', array(
		'current_range' => $current_range,
		'chart_colours' => $chart_colours,
		'barwidth' => $barwidth,
		'chart_groupby' => $chart_groupby,
		'chart_data' => rawurlencode( $chart_data ),
		'jsonMonthAbbrev' => rawurlencode( $jsonMonthAbbrev ),
		'gross_sale_amount' => __( 'Gross sales amount', 'auropay-gateway' ),
		'refund_amount' => __( 'Refund amount', 'auropay-gateway' ),
		'failed_amount' => __( 'Failed amount', 'auropay-gateway' ),
		'sales' => __( 'Sales', 'auropay-gateway' ),
		'refunds' => __( 'Refunds', 'auropay-gateway' ),
		'failed' => __( 'Failed', 'auropay-gateway' ),
	) );
}

add_action( 'admin_footer', function () use ( $current_range, $chart_colours, $barwidth, $chart_groupby, $chart_data, $jsonMonthAbbrev ) {
	auropay_enqueue_report( $current_range, $chart_colours, $barwidth, $chart_groupby, $chart_data, $jsonMonthAbbrev );
}, 10 );
