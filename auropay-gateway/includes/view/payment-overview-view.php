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
	exit;
}

require_once AUROPAY_PLUGIN_PATH . '/includes/html-report-by-date.php';

// Define a constant for the color code
define( 'AUROPAY_CUSTOM_COLOR', '#eba3a3' );

$arp_status_color = array(
	'In Process' => '#c8d7e1',
	'Authorized' => '#c6e1c6;',
	'Cancelled' => '#b1d4ea',
	'Failed' => AUROPAY_CUSTOM_COLOR,
	'RefundAttempted' => '#dbe1e3',
	'Refunded' => '#FFB52E',
	'Hold' => '#5cc488',
	'Success' => '#c6e1c6',
	'RefundFailed' => AUROPAY_CUSTOM_COLOR,
	'PartialRefundAttempted' => AUROPAY_CUSTOM_COLOR,
	'PartiallyRefunded' => AUROPAY_CUSTOM_COLOR,
	'UserCancelled' => '#b1d4ea',
	'Expired' => AUROPAY_CUSTOM_COLOR,
	'SettlementFailed' => AUROPAY_CUSTOM_COLOR,
	'Approved' => '#c6e1c6',
);

$auropay_refund_nonce = wp_create_nonce( 'auropay_refund_nonce' );
if ( isset( $_REQUEST['auropay-date-form-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['auropay-date-form-nonce'] ) ), 'auropay_date_form_action' ) ) {
	$auropay_date_nonce = '&auropay-date-form-nonce=' . sanitize_text_field( wp_unslash( $_REQUEST['auropay-date-form-nonce'] ) );
} else {
	$auropay_date_nonce = '&auropay-date-form-nonce=' . wp_create_nonce( 'auropay-date-form-nonce' );
}
?>

<div id="wpwrap" style="width:97%">
	<div id="wpbody" role="main">
		<div id="wpbody-content">
			<div class="wrap">
				<h1 class="wp-heading-inline">Payments Overview</h1>
				<div class="woocommerce-section-header ">
					<div id="poststuff" class="woocommerce-reports-wide">
						<div class="postbox pt-bx-dtl">
							<div class="stats_range">
								<ul>
									<?php
foreach ( $ranges as $range => $name ) {
	if ( 'Custom' != $name ) {
		echo '<li class="' . ( $current_range == $range ? 'active' : '' ) . ' odate_range" id="' . esc_attr( $range ) . '">
    <a href="' . esc_url(
			add_query_arg(
				array(
					'range' => $range,
					'auropay-date-form-nonce' => esc_attr( $auropay_date_nonce ),
				),
				remove_query_arg( array( 'start_date', 'end_date' ) )
			)
		) . '">' . esc_html( $name ) . '</a></li>';
	} else {
		echo '<li class="' . ( $current_range == $range ? 'active' : '' ) . ' custom_range" id="custom" ><a href="#" >' . esc_html( $name ) . '</a></li>';
	}
}
?>
									<li class="custom active" id="custom-box">
										<form method="GET">
										<?php wp_nonce_field( 'auropay_date_form_action', 'auropay-date-form-nonce', false, true ); ?>
											<div>
												<?php

if ( isset( $_GET['page'] ) ) {
	echo '<input type="hidden" name="page" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) . '" />';
}

if ( isset( $_GET['orderby'] ) ) {
	echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ) . '" />';
}

if ( isset( $_GET['order'] ) ) {
	echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) . '" />';
}

if ( isset( $_GET['transaction_status'] ) ) {
	echo '<input type="hidden" name="transaction_status" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['transaction_status'] ) ) ) . '" />';
}

if ( isset( $_GET['range'] ) && !is_array( $_GET['range'] ) ) {
	echo '<input type="hidden" name="range" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['range'] ) ) ) . '" />';
}

if ( isset( $_GET['range'] ) && is_array( $_GET['range'] ) ) {
	echo '<input type="hidden" name="range" value="custom" />';
	if ( isset( $_GET['range']['start_date'] ) ) {
		echo '<input type="hidden" name="start_date" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['range']['start_date'] ) ) ) . '" />';
	}

	if ( isset( $_GET['range']['end_date'] ) ) {
		echo '<input type="hidden" name="end_date" value="' . esc_attr( sanitize_text_field( wp_unslash( $_GET['range']['end_date'] ) ) ) . '" />';
	}
}

?>
												<input type="hidden" name="range" value="custom" />

												<?php
$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
$pickerStartDate = $start_date;

$end_date = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
$pickerEndDate = $end_date;
?>
												<span> From: </span>
												<input type="text" size="20" id="from_datepicker"
													placeholder="yyyy-mm-dd"
													value="<?php echo esc_attr( $pickerStartDate ); ?>"
													name="start_date" class="range_datepicker from"
													autocomplete="off" /><?php //@codingStandardsIgnoreLine
?>
												<span> To: </span>
												<input type="text" size="20" id="to_datepicker" placeholder="yyyy-mm-dd"
													value="<?php echo esc_attr( $pickerEndDate ); ?>" name="end_date"
													class="range_datepicker to" autocomplete="off" /><?php //@codingStandardsIgnoreLine
?>
												<button type="submit" class="button-go" value="Go">Go</button>
											</div>
										</form>
									</li>
								</ul>
							</div>

							<?php
global $auropay_tot_payments;
global $auropay_tot_refunded;
global $auropay_tot_failed;
?>
							<div class="pymnt-hdr">
								<div class="leftbox-sales" id="sale_box">
									<img class="pymnt-ico"
										src="<?php echo esc_url( AUROPAY_PLUGIN_URL ) ?>/assets/images/summary/calculator_color.png"
										id="sale_img" alt="Sales" />
									<div class="pymnt-ico-label">
										<div class="pymnt-label">
											<strong> Sales</strong>
										</div>
										<div class="pymnt-amt clr-grn">
											<strong><span><span>₹</span><?php echo esc_html( $auropay_tot_payments ); ?></span></strong>
										</div>
									</div>
								</div>
								<div class="middlebox-refund" id="refunded_box">
									<img class="pymnt-ico"
										src="<?php echo esc_url( AUROPAY_PLUGIN_URL ) ?>/assets/images/summary/calendar_refund_color.png"
										id="refunded_img" alt="Refund" />
									<div class="pymnt-ico-label">
										<div class="pymnt-label">
											<strong>Refund</strong>
										</div>
										<div class="pymnt-amt clr-orng">
											<strong><span><span>₹</span><?php echo esc_html( $auropay_tot_refunded ); ?></span></strong>
										</div>
									</div>
								</div>
								<div class="rightbox-failed" id="failed_box">
									<img class="pymnt-ico"
										src="<?php echo esc_url( AUROPAY_PLUGIN_URL ) ?>/assets/images/summary/calendar_decline_color.png"
										id="failed_img" alt="Failed" />
									<div class="pymnt-ico-label">
										<div class="pymnt-label">
											<strong>Failed</strong>
										</div>
										<div class="pymnt-amt clr-rd">
											<strong><span><span>₹</span><?php echo esc_html( $auropay_tot_failed ); ?></span></strong>
										</div>
									</div>
								</div>
								<input type="hidden" value="line" id="chart_type">
								<input type="hidden" value="sale" id="data_type">

								<div class="sls-row" id="summary_main_box_type">
									<div class="pymnt-sumry-lbl" id="summary_box_type">
										Sales
									</div>
									<div class="card-label" id="sales_stat_details">
										<img alt="Credit Card" class="ico-crd"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-card.png" />
										<span><strong>Credit Card </strong>
											<?php echo esc_html( $auropay_all_order_data['sale_tot_credit_card_payments'] ); ?>
											&nbsp;&nbsp;</span>
										<img alt="Debit Card" class="ico-crd"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-card.png" />
										<span><strong>Debit Card
											</strong><?php echo esc_html( $auropay_all_order_data['sale_tot_debit_card_payments'] ); ?>
											&nbsp;&nbsp;</span>
										<img alt="Net Banking" class="ico-nb"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-ach.png" />
										<span><strong>Net Banking
											</strong><?php echo esc_html( $auropay_all_order_data['sale_tot_netbanking_payments'] ); ?></span>
										<img alt="UPI" class="ico-upi"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-upi.png" />
										<span><strong>UPI
											</strong><?php echo esc_html( $auropay_all_order_data['sale_tot_upi_payments'] ); ?></span>
										<img alt="Wallet" class="ico-upi"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-wallet.png" />
										<span><strong>Wallet
											</strong><?php echo esc_html( $auropay_all_order_data['sale_tot_wallet_payments'] ); ?></span>
									</div>

									<div class="card-label" id="refunded-stat-details">
										<img alt="Credit Card" class="ico-crd"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-card.png" />
										<span><strong>Credit Card </strong>
											<?php echo esc_html( $auropay_all_order_data['refunded_tot_credit_card_payments'] ); ?>
											&nbsp;&nbsp;</span>
										<img alt="Debit Card" class="ico-crd"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-card.png" />
										<span><strong>Debit Card
											</strong><?php echo esc_html( $auropay_all_order_data['refunded_tot_debit_card_payments'] ); ?>
											&nbsp;&nbsp;</span>
										<img alt="Net Banking" class="ico-nb"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-ach.png" />
										<span><strong>Net Banking
											</strong><?php echo esc_html( $auropay_all_order_data['refunded_tot_netbanking_payments'] ); ?></span>
										<img alt="UPI" class="ico-upi"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-upi.png" />
										<span><strong>UPI
											</strong><?php echo esc_html( $auropay_all_order_data['refunded_tot_upi_payments'] ); ?></span>
										<img alt="Wallet" class="ico-upi"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-wallet.png" />
										<span><strong>Wallet
											</strong><?php echo esc_html( $auropay_all_order_data['refunded_tot_wallet_payments'] ); ?></span>
									</div>

									<div class="card-label" id="failed-stat-details">
										<img alt="Credit Card" class="ico-crd"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-card.png" />
										<span><strong>Credit Card </strong>
											<?php echo esc_html( $auropay_all_order_data['failed_tot_credit_card_payments'] ); ?>
											&nbsp;&nbsp;</span>
										<img alt="Debit Card" class="ico-crd"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ) ?>/assets/images/cards/ico-card.png" />
										<span><strong>Debit Card
											</strong><?php echo esc_html( $auropay_all_order_data['failed_tot_debit_card_payments'] ); ?>
											&nbsp;&nbsp;</span>
										<img alt="Net Banking" class="ico-nb"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-ach.png" />
										<span><strong>Net Banking
											</strong><?php echo esc_html( $auropay_all_order_data['failed_tot_netbanking_payments'] ); ?></span>
										<img alt="UPI" class="ico-upi"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-upi.png" />
										<span><strong>UPI
											</strong><?php echo esc_html( $auropay_all_order_data['failed_tot_upi_payments'] ); ?></span>
										<img alt="Wallet" class="ico-upi"
											src="<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/cards/ico-wallet.png" />
										<span><strong>Wallet
											</strong><?php echo esc_html( $auropay_all_order_data['failed_tot_wallet_payments'] ); ?></span>
									</div>
								</div>
								<div class="grph-cntrl" id="show_main_all">
									<div role="menubar" aria-orientation="horizontal"
										class="woocommerce-chart__types grph-btn">
										<button type="button" disabled id="line_chart" title="Line chart"
											aria-checked="false" role="menuitemradio" tabindex="-1"
											class="components-button woocommerce-chart__type-button pmt-btn"><svg
												class="gridicon gridicons-line-graph" height="15" width="15"
												xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
												<g>
													<path
														d="M3 19h18v2H3zm3-3c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3L8.8 10H9c.5 0 1-.2 1.3-.5l2.7 1.4v.1c0 1.1.9 2 2 2s2-.9 2-2c0-.5-.2-.9-.5-1.3L17.8 7h.2c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2c0 .5.2 1 .5 1.3L15.2 9H15c-.5 0-1 .2-1.3.5L11 8.2V8c0-1.1-.9-2-2-2s-2 .9-2 2c0 .5.2 1 .5 1.3L6.2 12H6c-1.1 0-2 .9-2 2s.9 2 2 2z">
													</path>
												</g>
											</svg></button>
										<button type="button" id="bar_chart" title="Bar chart" aria-checked="true"
											role="menuitemradio" tabindex="0"
											class="components-button woocommerce-chart__type-button woocommerce-chart__type-button-selected pmt-btn"><svg
												class="gridicon gridicons-stats-alt" height="15" width="15"
												xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
												<g>
													<path
														d="M21 21H3v-2h18v2zM8 10H4v7h4v-7zm6-7h-4v14h4V3zm6 3h-4v11h4V6z">
													</path>
												</g>
											</svg></button>
									</div>
								</div>
							</div>
							<br class="clear">
							<div class="inside">
								<div class="chart-container chart-view" id="chrt" style="width: 99.5%;">
									<canvas id="main_chart_canvas"></canvas> 
								</div>
							</div>
						</div>
					</div>
					<div class="trans-dtl-tbl">
						<div class="trans-dtl-tbl-head"><strong>Transaction Details</strong></div>
						<ul class="subsubsub">
							<li class="all"><a
									href="?page=payment-overview&orderby=post_id&order=desc&transaction_status=all&<?php echo esc_attr( $range_filter ); ?><?php echo esc_attr( $auropay_date_nonce ); ?>">All
									<span
										class="count">(<?php echo esc_html( $auropay_all_order_data['total_all_records'] ); ?>)</span></a>
								|</li>
							<li><a
									href="?page=payment-overview&orderby=post_id&order=desc&transaction_status=completed&<?php echo esc_attr( $range_filter ); ?><?php echo esc_attr( $auropay_date_nonce ); ?>">Sales
									<span
										class="count">(<?php echo esc_html( $auropay_all_order_data['total_completed_records'] ); ?>)</span></a>
								|</li>
							<li><a
									href="?page=payment-overview&orderby=post_id&order=desc&transaction_status=refund&<?php echo esc_attr( $range_filter ); ?><?php echo esc_attr( $auropay_date_nonce ); ?>">Refunded
									<span
										class="count">(<?php echo esc_html( $auropay_all_order_data['total_refund_records'] ); ?>)</span></a>
								|</li>
							<li><a
									href="?page=payment-overview&orderby=post_id&order=desc&transaction_status=failed&<?php echo esc_attr( $range_filter ); ?><?php echo esc_attr( $auropay_date_nonce ); ?>">Failed
									<span
										class="count">(<?php echo esc_html( $auropay_all_order_data['total_failed_records'] ); ?>)</span></a>
							</li>
						</ul>

						<div class="export-section">
							<form class="form-horizontal" action="" enctype="multipart/form-data" method="post"
								name="upload_excel">
								<?php wp_nonce_field( 'auropay_export_form_action', 'auropay-export-form-nonce' ); ?>
								<div class="form-group">
									<div class="col-md-4 col-md-offset-4" style="cursor:pointer">
										<select name="export_type" id="export_type" onchange="this.form.submit()">
											<option selected="selected" value="0">&#8595; Export</option>
											<option value="csv">CSV</option>
											<option value="pdf">PDF</option>
										</select>
										<input class="btn btn-success export-input" name="Export" type="hidden"
											value="&#8595; Export" />
									</div>
								</div>
							</form>
						</div>

						<div class="post-type-shop_order1 order-lst-tp">
							<table class="wp-list-table widefat fixed striped w-auto table-view-list posts ">
								<caption><strong>Sales Report</strong></caption>
								<thead>
									<tr>
										<th scope="col" id="order_number"
											class="manage-column column-order_number c_order_number column-primary1 sortable <?php echo esc_html( $auropay_all_order_data['link_order'] ); ?>">
											<a
												href="?page=payment-overview&orderby=post_id&order=<?php echo esc_html( $auropay_all_order_data['link_order'] ); ?>&<?php echo esc_attr( $range_filter ); ?>">
												<span>Order</span><span class="sorting-indicator"></span>
											</a>
										</th>
										<th scope="col" id="order_date"
											class="manage-column column-order_number   c_order_date column-primary2 sortable">
											<a
												href="?page=payment-overview&order=<?php echo esc_html( $auropay_all_order_data['link_order'] ); ?>&<?php echo esc_attr( $range_filter ); ?>">
												<span>Date & Time (IST)</span>
											</a>
										</th>
										<th scope="col" id="order_status"
											class="manage-column c_order_status sortable">Status
										</th>
										<th scope="col" id="order_total"
											class="manage-column c_order_total column-primary3 sortable">
											Sale</th>
										<th scope="col" id="refund_total"
											class="manage-column c_order_total column-primary3 sortable">
											Refund</th>
										<th scope="col" id="order_type"
											class="manage-column c_order_type sortable">Type</th>
										<th scope="col" id="order_payment_id"
											class="manage-column column-order_payment_id  c_order_payment_id sortable">
											Payment Id</th>

										<th scope="col" id="payment_method"
											class="manage-column c_payment_method ">Method</th>
										<th scope="col" id="payment_method"
											class="manage-column c_card_type ">Payment Detail</th>
										<th scope="col" id="auth_code"
											class="manage-column c_auth_code ">Auth Code</th>
									</tr>
								</thead>
								<tbody id="the-list">
									<?php
if ( !empty( $auropay_all_order_data['order_datas'] ) ) {
	foreach ( $auropay_all_order_data['order_datas'] as $order_id ) {
		$type_array = array( '3' => 'Credit Card', '4' => 'Debit Card', '6' => 'UPI', '7' => 'NetBanking', '8' => 'Wallets' );
		$payment_method = get_post_meta( $order_id, '_auropay_transaction_channel_type', true );
		$auth_code = get_post_meta( $order_id, '_auropay_transaction_auth_code', true );
		$transaction_date = get_post_meta( $order_id, '_auropay_transaction_date', true );
		$paymentId = get_post_meta( $order_id, '_auropay_transaction_id', true );
		$card_type = get_post_meta( $order_id, '_auropay_transaction_card_type', true );
		$order_status = get_post_meta( $order_id, '_auropay_order_status', true );
		$order_amount = get_post_meta( $order_id, '_amount', true );
		$refund_amount = get_post_meta( $order_id, '_refund_amount', true );
		$order_amount = number_format( (float) $order_amount, 2, '.', '' );
		$refund_amount = number_format( (float) $refund_amount, 2, '.', '' );
		if ( empty( $refund_amount ) || ( is_numeric( $refund_amount ) && (float) $refund_amount == 0 ) ) {
			$refund_amount = '₹' . $refund_amount;
			$color_code = '';
		} else {
			$color_code = 'style="color:red"';
			$refund_amount = '-' . '₹' . $refund_amount;
		}

		if ( 'Refunded' == $order_status ) {
			$type = "Refund";
		} else {
			$type = "Sale";
		}

		if ( isset( $type_array[$payment_method] ) ) {
			$payment_method = $type_array[$payment_method];
		} else {
			$payment_method = "";
		}

		?>
									<tr id="post-<?php echo esc_html( $order_id ); ?>">
										<td id="row_order_number"
											class="order_number column-order_number c_order_number has-row-actions column-primary1">
											<a href="?page=refund-overview&order_id=<?php echo esc_html( $order_id ); ?>&refund_nonce=<?php echo esc_attr( $auropay_refund_nonce ); ?>"><strong>#<?php echo esc_html( $order_id ); ?>
												</strong></a>
										</td>
										<td id="row_order_status"
											class="order_status c_order_status hidden"><?php echo esc_html( $order_id ); ?></td>
										<td id="row_order_date" class="order_date column-order_date c_order_date">
											<?php echo esc_html( $transaction_date ); ?></td>
										<td id="row_order_status"
											class="order_status c_order_status">
											<span><mark class="order-status tips"
													style="background:<?php echo esc_html( $arp_status_color[$order_status] ); ?>"><span><?php echo esc_html( ucfirst( $order_status ) ); ?></span></mark>
											</span>
										</td>
										<td id="row_order_total" class="order_total c_order_total">
											₹<?php echo esc_html( $order_amount ) ?></td>
										<td id="row_refund_amount"
											class="refund_amount c_refund_amount"
											<?php echo esc_html( $color_code ); ?>>
											<?php echo esc_html( $refund_amount ); ?>
										</td>
										<td id="row_order_type" class="order_status c_order_type">
											<span><?php echo esc_html( $type ); ?></span>
										</td>
										<td id="row_order_payment_id"
											class="order_payment c_order_payment_id">
											<span><?php echo esc_html( $paymentId ); ?></span>
										</td>
										<td id="row_payment_method"
											class="order_status c_payment_method">
											<span><?php echo esc_html( $payment_method ); ?></span>
										</td>

										<td id="row_card_type"
											class="order_status c_card_type">
											<span><?php echo esc_html( $card_type ); ?></span>
										</td>
										<td id="row_auth_code" class="order_date c_auth_code">
											<span><?php echo esc_html( $auth_code ); ?></span>
										</td>
									</tr>
									<?php
}
}
?>
								</tbody>
							</table>
						</div>
						<?php
if ( !empty( $auropay_all_order_data['total_items'] ) ) {
	?>
						<div class="tablenav bottom">
							<div class="tablenav-pages order-lst-tp">
								<span class="displaying-num">Total
									<?php echo esc_html( $auropay_all_order_data['total_items'] ); ?>
									items</span>
								<span
									class="pagination_links"><?php echo wp_kses_post( $auropay_all_order_data['page_links'] ); ?></span>
							</div>
							<br class="clear">
						</div>
						<?php
}?>
					</div>
				</div>
			</div>
		</div>
