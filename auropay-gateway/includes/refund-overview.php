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

$order_id = sanitize_text_field( $_GET['order_id'] );
$order_amount = get_post_meta( $order_id, '_amount', true );
$refund_amount = get_post_meta( $order_id, '_refund_amount', true );
$order_date = get_post_meta( $order_id, '_auropay_transaction_date', true );

$order_date = date( "M d, Y", strtotime( $order_date ) );
$order_amount = number_format( (float) $order_amount, 2, '.', '' );
$refund_amount = number_format( (float) $refund_amount, 2, '.', '' );
$total_available_to_refund = ( $order_amount - $refund_amount );
$total_available_to_refund = number_format( (float) $total_available_to_refund, 2, '.', '' );
?>
<div>
	<div class="before-refund">
		<table>
			<caption><strong>Order Details</strong>
				<hr>
			</caption>
			<hr><br>
			<thead>
				<th></th>
				<th></th>
			</thead>
			<tbody>
				<tr>
					<td class="label">Items Subtotal:</td>
					<td></td>
					<td class="total">
						<span>₹<?php echo esc_html( $order_amount ); ?></span>
					</td>
				</tr>
				<tr>
					<td class="label">Order Total:</td>
					<td></td>
					<td class="total">
						<span>₹<?php echo esc_html( $order_amount ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>

		<?php if ( '0.00' !== $refund_amount ) {
	?>
		<table style="border-top: 1px solid #999; margin-top:12px; padding-top:12px">
			<caption><strong>Paid Details</strong>
				<hr>
			</caption>
			<hr><br>
			<thead>
				<th></th>
				<th></th>
			</thead>
			<tbody>
				<tr>
					<td class="label">Paid:</td>
					<td></td>
					<td class="total">
						<span>₹<?php echo esc_html( $order_amount ); ?></span>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<span class="description">
							<?php echo esc_html( $order_date ); ?> via AuroPay Gateway </span>
					</td>
				</tr>
			</tbody>
		</table>

		<table style="border-top: 1px solid #999; margin-top:12px; padding-top:12px">
			<caption><strong>Refund Details</strong>
				<hr>
			</caption>
			<hr><br>
			<thead>
				<th></th>
				<th></th>
			</thead>
			<tbody>
				<tr>
					<td class="label refunded-total">Refunded:</td>
					<td></td>
					<td class="total refunded-total"><span>-₹<?php echo esc_html( $refund_amount ); ?></span></td>
				</tr>
				<tr>
					<td class="label label-highlight">Net Payment:</td>
					<td></td>
					<td class="total">
						<span>₹<?php echo esc_html( $total_available_to_refund ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
}?>
		<hr>
		<table style="border-top: 1px solid #999; margin-top:12px; padding-top:12px">
			<caption style="display:none;"><strong>Refund Button</strong>
			</caption>
			<thead>
				<th></th>
				<th></th>
			</thead>
			<tbody>
				<tr>
					<button type="button" class="button button-primary do-api-refund"
						onclick="return auropay_refund_btn()">Refund</button>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="after-refund">
		<div>
			<table>
				<caption><strong>Order Details</strong>
					<hr>
				</caption>
				<hr><br>
				<thead>
					<th></th>
					<th></th>
				</thead>
				<tbody>
					<tr>
						<td class="label">Amount already refunded:</td>
						<td class="total"><span>-₹<?php echo esc_html( $refund_amount ); ?></span></td>
					</tr>
					<tr>
						<td class="label">Total available to refund:</td>
						<td class="total"><span>₹<?php echo esc_html( $total_available_to_refund ); ?></span></td>
					</tr>
					<tr>
						<td class="label">
							<label for="refund_amount">
								Refund amount:
							</label>
						</td>
						<td class="total">
							<input type="number" id="refund_amount" name="refund_amount"
								value="<?php echo esc_html( $total_available_to_refund ); ?>">
							<span id="refundError" class="errorMsg"></span>
						</td>
					</tr>
					<tr>
						<td class="label">
							<label for="refund_reason">
								Reason for refund (optional): </label>
						</td>
						<td class="total">
							<input type="text" id="refund_reason" name="refund_reason">
						</td>
					</tr>
				</tbody>
			</table>
			<hr>
			<div class="refund-actions">
				<button type="button" class="button button-primary do-api-refund" id="btnRefundAmount">Refund ₹ <?php echo esc_html( $total_available_to_refund ); ?> via
					AuroPay
					Gateway</button>
				<button type="button" class="button cancel-action" onclick="auropay_cancel_btn()">Cancel</button>
			</div>
		</div>
	</div>
</div>
