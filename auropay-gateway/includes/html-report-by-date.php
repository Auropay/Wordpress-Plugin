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

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// Define a constant for the amount
define('ARP_GROSS_SALES_AMOUNT', 'Gross sales amount');
define('ARP_REFUND_AMOUNT', 'Refund amount');
define('ARP_FAILED_AMOUNT', 'Failed amount');
global $wp_locale;

$chart_groupby = $all_order_data['chart_groupby'];
$barwidth = $all_order_data['barwidth'];
$start_date = $all_order_data['start_date'];
$interval = $all_order_data['interval'];
$allChartData = $all_order_data['chart_datas'];

//configure color of chart
$chart_colours = array(
	'sales_amount'     => 'green',
	'net_sales_amount' => '#3498db',
	'average'          => '#b1d4ea',
	'net_average'      => '#3498db',
	'order_count'      => '#dbe1e3',
	'item_count'       => '#ecf0f1',
	'shipping_amount'  => '#5cc488',
	'coupon_amount'    => '#f1c40f',
	'refund_amount'    => 'orange',
	'failed_amount'    => 'red',
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
if (!function_exists('arp_prepare_chart_data')) {
	function arp_prepare_chart_data($data, $data_key, $interval, $start_date, $group_by)
	{
		$prepared_data = array();
		// Ensure all days (or months) have values in this range.
		if ('day' === $group_by) {
			for ($i = 0; $i <= $interval; $i++) {
				$time = strtotime(date('Ymd', strtotime("+{$i} DAY", $start_date))) . '000';
				if (!isset($prepared_data[$time])) {
					$prepared_data[$time] = array(esc_js($time), 0);
				}
			}
		} else {
			$current_yearnum  = date('Y', $start_date);
			$current_monthnum = date('m', $start_date);

			for ($i = 0; $i <= $interval; $i++) {
				$time = strtotime($current_yearnum . str_pad($current_monthnum, 2, '0', STR_PAD_LEFT) . '01') . '000';

				if (!isset($prepared_data[$time])) {
					$prepared_data[$time] = array(esc_js($time), 0);
				}

				$current_monthnum++;

				if ($current_monthnum > 12) {
					$current_monthnum = 1;
					$current_yearnum++;
				}
			}
		}

		foreach ($data as $key => $value) {
			foreach ($value as $k => $v) {
				switch ($group_by) {
					case 'day':
						if ($key == $data_key) {
							$time = strtotime(date('Ymd', $k)) . '000';
							$prepared_data[$time][1] = $v;
						}
						break;
					case 'month':
					default:
						if ($key == $data_key) {
							$time = strtotime(date('Ym', $k) . '01') . '000';
							$prepared_data[$time][1] += $v;
						};
						break;
				}
			}
		}

		return $prepared_data;
	}
}

$ChartDataArr = array(
	'sale_amount'        => arp_prepare_chart_data($allChartData, 'sale_amount', $interval, $start_date, $chart_groupby),
	'refund_amount'       => arp_prepare_chart_data($allChartData, 'refund_amount', $interval, $start_date, $chart_groupby),
	'failed_amount' => arp_prepare_chart_data($allChartData, 'failed_amount', $interval, $start_date, $chart_groupby),
);

$chart_data = wp_json_encode(
	array(
		'sale_amount' => !empty($ChartDataArr['sale_amount']) ? array_values($ChartDataArr['sale_amount']) : [],
		'refund_amount' => !empty($ChartDataArr['refund_amount']) ? array_values($ChartDataArr['refund_amount']) : [],
		'failed_amount' => !empty($ChartDataArr['failed_amount']) ? array_values($ChartDataArr['failed_amount']) : [],
	)
);

?>

<script>
	var main_chart;
	jQuery(function() {
		var order_data = JSON.parse(decodeURIComponent('<?php echo rawurlencode($chart_data); ?>'));

		console.log(order_data)
		var drawGraph = function(highlight, type = 'line') {
			type = jQuery('#chart_type').val();
			if (highlight == 'sale') {
				if (type == 'bar') {
					var series = [{
						label: "<?php echo esc_js(__(ARP_GROSS_SALES_AMOUNT, 'auropay-gateway')); ?>",
						data: order_data.sale_amount,
						yaxis: 1,
						color: '<?php echo esc_js($chart_colours['sales_amount']); ?>',
						bars: {
							fillColor: '<?php echo esc_html($chart_colours['sales_amount']); ?>',
							fill: true,
							show: true,
							lineWidth: 0,
							barWidth: <?php echo esc_html($barwidth); ?> * 0.5,
							align: 'center'
						},
						shadowSize: 0,
						enable_tooltip: true,
						stack: true
					}];
				} else {
					var series = [{
						label: "Gross sales amount",
						data: order_data.sale_amount,
						yaxis: 1,
						color: '<?php echo esc_js($chart_colours['sales_amount']); ?>',
						points: {
							show: true,
							radius: 5,
							lineWidth: 2,
							fillColor: '#fff',
							fill: true
						},
						lines: {
							show: true,
							lineWidth: 2,
							fill: false
						},
						shadowSize: 0,
						enable_tooltip: true
					}];
				}
			} else if (highlight == 'refunded') {
				if (type == 'bar') {
					var series = [{
						label: "<?php echo esc_js(__(ARP_REFUND_AMOUNT, 'auropay-gateway')); ?>",
						data: order_data.refund_amount,
						yaxis: 1,
						color: '<?php echo esc_js($chart_colours['refund_amount']); ?>',
						bars: {
							fillColor: '<?php echo esc_html($chart_colours['refund_amount']); ?>',
							fill: true,
							show: true,
							lineWidth: 0,
							barWidth: <?php echo esc_html($barwidth); ?> * 0.5,
							align: 'center'
						},
						shadowSize: 0,
						enable_tooltip: true,
						stack: true,
						append_tooltip: "<?php echo esc_html(' ' . __('Refunds', 'auropay-gateway')); ?>",
					}];
				} else {
					var series = [{
						label: "<?php echo esc_js(__(ARP_REFUND_AMOUNT, 'auropay-gateway')); ?>",
						data: order_data.refund_amount,
						yaxis: 1,
						color: '<?php echo esc_js($chart_colours['refund_amount']); ?>',
						points: {
							show: true,
							radius: 5,
							lineWidth: 2,
							fillColor: '#fff',
							fill: true
						},
						lines: {
							show: true,
							lineWidth: 2,
							fill: false
						},
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: "<?php echo esc_html(' ' . __('Refunds', 'auropay-gateway')); ?>",
					}];
				}

			} else if (highlight == 'failed') {
				if (type == 'bar') {
					var series = [{
						label: "<?php echo esc_js(__(ARP_FAILED_AMOUNT, 'auropay-gateway')); ?>",
						data: order_data.failed_amount,
						yaxis: 1,
						color: '<?php echo esc_js($chart_colours['failed_amount']); ?>',
						bars: {
							fillColor: '<?php echo esc_html($chart_colours['failed_amount']); ?>',
							fill: true,
							show: true,
							lineWidth: 0,
							barWidth: <?php echo esc_html($barwidth); ?> * 0.5,
							align: 'center'
						},
						stack: true,
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: "<?php echo esc_html(' ' . __('Failed', 'auropay-gateway')); ?>",
					}];
				} else {
					var series = [{
						label: "<?php echo esc_js(__(ARP_FAILED_AMOUNT, 'auropay-gateway')); ?>",
						data: order_data.failed_amount,
						yaxis: 1,
						color: '<?php echo esc_js($chart_colours['failed_amount']); ?>',
						points: {
							show: true,
							radius: 5,
							lineWidth: 2,
							fillColor: '#fff',
							fill: true
						},
						lines: {
							show: true,
							lineWidth: 2,
							fill: false
						},
						shadowSize: 0,
						enable_tooltip: true,
						append_tooltip: "<?php echo esc_html(' ' . __('Failed', 'auropay-gateway')); ?>",
					}];
				}

			} else {
				if (type == 'bar') {
					var series = [{
							label: "<?php echo esc_js(__(ARP_GROSS_SALES_AMOUNT, 'auropay-gateway')); ?>",
							data: order_data.sale_amount,
							yaxis: 1,
							color: '<?php echo esc_js($chart_colours['sales_amount']); ?>',
							bars: {
								fillColor: '<?php echo esc_html($chart_colours['sales_amount']); ?>',
								fill: true,
								show: true,
								lineWidth: 0,
								barWidth: <?php echo esc_html($barwidth); ?> * 0.5,
								align: 'center'
							},
							shadowSize: 0,
							stack: true,
							enable_tooltip: true,
							append_tooltip: "<?php echo esc_html(' ' . __('Sales', 'auropay-gateway')); ?>",

						},
						{
							label: "<?php echo esc_js(__(ARP_REFUND_AMOUNT, 'auropay-gateway')); ?>",
							data: order_data.refund_amount,
							yaxis: 1,
							color: '<?php echo esc_js($chart_colours['refund_amount']); ?>',
							bars: {
								fillColor: '<?php echo esc_html($chart_colours['refund_amount']); ?>',
								fill: true,
								show: true,
								lineWidth: 0,
								barWidth: <?php echo esc_html($barwidth); ?> * 0.5,
								align: 'center'
							},
							shadowSize: 0,
							stack: true,
							enable_tooltip: true,
							append_tooltip: "<?php echo esc_html(' ' . __('Refunds', 'auropay-gateway')); ?>",
						},
						{
							label: "<?php echo esc_js(__(ARP_FAILED_AMOUNT, 'auropay-gateway')); ?>",
							data: order_data.failed_amount,
							yaxis: 1,
							color: '<?php echo esc_js($chart_colours['failed_amount']); ?>',
							bars: {
								fillColor: '<?php echo esc_html($chart_colours['failed_amount']); ?>',
								fill: true,
								show: true,
								lineWidth: 0,
								barWidth: <?php echo esc_html($barwidth); ?> * 0.5,
								align: 'center'
							},
							stack: true,
							shadowSize: 0,
							enable_tooltip: true,
							append_tooltip: "<?php echo esc_html(' ' . __('Failed', 'auropay-gateway')); ?>",
						},
					];

				} else {
					var series = [{
							label: "<?php echo esc_js(__(ARP_GROSS_SALES_AMOUNT, 'auropay-gateway')); ?>",
							data: order_data.sale_amount,
							yaxis: 1,
							color: '<?php echo esc_js($chart_colours['sales_amount']); ?>',
							points: {
								show: true,
								radius: 5,
								lineWidth: 2,
								fillColor: '#fff',
								fill: true
							},
							lines: {
								show: true,
								lineWidth: 2,
								fill: false
							},
							shadowSize: 0,
							enable_tooltip: true,
							append_tooltip: "<?php echo esc_html(' ' . __('Sales', 'auropay-gateway')); ?>",

						},
						{
							label: "<?php echo esc_js(__(ARP_REFUND_AMOUNT, 'auropay-gateway')); ?>",
							data: order_data.refund_amount,
							yaxis: 1,
							color: '<?php echo esc_js($chart_colours['refund_amount']); ?>',
							points: {
								show: true,
								radius: 5,
								lineWidth: 2,
								fillColor: '#fff',
								fill: true
							},
							lines: {
								show: true,
								lineWidth: 2,
								fill: false
							},
							shadowSize: 0,
							enable_tooltip: true,
							append_tooltip: "<?php echo esc_html(' ' . __('Refunds', 'auropay-gateway')); ?>",
						},
						{
							label: "<?php echo esc_js(__(ARP_FAILED_AMOUNT, 'auropay-gateway')); ?>",
							data: order_data.failed_amount,
							yaxis: 1,
							color: '<?php echo esc_js($chart_colours['failed_amount']); ?>',
							points: {
								show: true,
								radius: 5,
								lineWidth: 2,
								fillColor: '#fff',
								fill: true
							},
							lines: {
								show: true,
								lineWidth: 2,
								fill: false
							},
							shadowSize: 0,
							enable_tooltip: true,
							append_tooltip: "<?php echo esc_html(' ' . __('Failed', 'auropay-gateway')); ?>",
						},
					];
				}
			}

			if (highlight !== 'undefined' && series[highlight]) {
				highlight_series = series[highlight];

				highlight_series.color = '#9c5d90';

				if (highlight_series.bars) {
					highlight_series.bars.fillColor = '#9c5d90';
				}

				if (highlight_series.lines) {
					highlight_series.lines.lineWidth = 5;
				}
			}

			main_chart = jQuery.plot(
				jQuery('.chart-placeholder.main'),
				series, {
					legend: {
						show: false
					},
					grid: {
						color: '#aaa',
						borderColor: 'transparent',
						borderWidth: 0,
						hoverable: true
					},
					xaxes: [{
						color: '#aaa',
						position: "bottom",
						tickColor: 'transparent',
						mode: "time",
						timeformat: "<?php echo ('day' === $chart_groupby) ? '%d %b' : '%b'; ?>",
						monthNames: JSON.parse(decodeURIComponent(
							'<?php echo rawurlencode(wp_json_encode(array_values($wp_locale->month_abbrev))); ?>'
						)),
						tickLength: 1,
						minTickSize: [1, "<?php echo esc_js($chart_groupby); ?>"],
						font: {
							color: "#aaa"
						}
					}],
					yaxes: [{
							min: 0,
							minTickSize: 1,
							tickDecimals: 2,
							color: '#d4d9dc',
							font: {
								color: "#aaa"
							},
							tickFormatter: function(v, axis) {
								return "₹" + v.toFixed(axis.tickDecimals)
							}
						},
						{
							position: "right",
							min: 0,
							tickDecimals: 2,
							alignTicksWithAxis: 1,
							color: 'transparent',
							font: {
								color: "#aaa"
							}
						}
					],
				}
			);
			jQuery('.chart-placeholder').resize();
		}

		drawGraph('sale');
		jQuery('.highlight_series').hover(
			function() {
				drawGraph(jQuery(this).data('series'));
			},
			function() {
				drawGraph();
			}
		);

		jQuery('#sale_box').hover(
			function() {
				jQuery(this).css('cursor', 'pointer');
				jQuery(this).css('background-color', '#f0f0f1');
				jQuery('#refunded_box').css('background-color', '#fff');
				jQuery('#failed_box').css('background-color', '#fff');
			}
		);
		jQuery('#refunded_box').hover(
			function() {
				jQuery(this).css('cursor', 'pointer');
				jQuery(this).css('background-color', '#f0f0f1');
				jQuery('#failed_box').css('background-color', '#fff');
				jQuery('#sale_box').css('background-color', '#fff');
			}
		);
		jQuery('#failed_box').hover(
			function() {
				jQuery(this).css('cursor', 'pointer');
				jQuery(this).css('background-color', '#f0f0f1');
				jQuery('#refunded_box').css('background-color', '#fff');
				jQuery('#sale_box').css('background-color', '#fff');
			}
		);

		jQuery('#sale_box').click(
			function() {
				drawGraph('sale');
				jQuery('#data_type').val('sale');
				jQuery('#summary_box_type').html('Sales');
				jQuery('#sales_stat_details').show();
				jQuery('#refunded-stat-details').hide();
				jQuery('#failed-stat-details').hide();
			}
		);

		jQuery('#failed_box').click(
			function() {
				drawGraph('failed');
				jQuery('#data_type').val('failed');
				jQuery('#summary_box_type').html('Failed');
				jQuery('#sales_stat_details').hide();
				jQuery('#refunded-stat-details').hide();
				jQuery('#failed-stat-details').show();
			}
		);

		jQuery('#refunded_box').click(
			function() {
				drawGraph('refunded');
				jQuery('#data_type').val('refunded');
				jQuery('#summary_box_type').html('Refunded');
				jQuery('#sales_stat_details').hide();
				jQuery('#failed-stat-details').hide();
				jQuery('#refunded-stat-details').show();
			}
		);

		jQuery('#line_chart').click(
			function() {
				jQuery('#chart_type').val('line');
				drawGraph(jQuery('#data_type').val());
				jQuery('#line_chart').prop('disabled', true);
				jQuery('#line_chart').css('background-color', 'gray');
				jQuery('#bar_chart').css('background-color', '');
				jQuery('#bar_chart').prop('disabled', false);
			}
		);
		jQuery('#bar_chart').click(
			function() {
				jQuery('#chart_type').val('bar');
				drawGraph(jQuery('#data_type').val());
				jQuery('#line_chart').prop('disabled', false);
				jQuery('#bar_chart').prop('disabled', true);
				jQuery('#bar_chart').css('background-color', 'gray');
				jQuery('#line_chart').css('background-color', '');

			}
		);
		jQuery('#custom').click(
			function() {
				jQuery('#custom-box').show();
				jQuery('#custom').addClass('active');
				jQuery('.odate_range').removeClass('active');
			}
		);
	});
</script>

<script>
	var current_range = '<?php echo $current_range; ?>';
	jQuery(function() {
		jQuery(".custom").hide();
		if (current_range == 'custom') {
			jQuery(".custom").show();
		}

		jQuery("#from_datepicker").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: "dd-mm-yy",
			maxDate: 0,
			onSelect: function(selected) {
				jQuery("#to_datepicker").datepicker("option", "minDate", selected);
			}
		});
		jQuery("#to_datepicker").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: "dd-mm-yy",
			maxDate: 0,
			onSelect: function(selected) {
				jQuery("#from_datepicker").datepicker("option", "maxDate", selected);
			}
		});
	});
</script>
