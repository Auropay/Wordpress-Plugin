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

require_once dirname( __FILE__ ) . '/../../../../wp-load.php';

global $wp;

add_action( 'wp_loaded', 'auropay_callback' );
function auropay_callback() {
	if ( isset( $_REQUEST['refNo'], $_REQUEST['page_id'], $_REQUEST['id'] ) ) {
		if ( isset( $_REQUEST['callback_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['callback_nonce'] ) ), 'auropay_callback_nonce' ) ) {

			$order_id = sanitize_text_field( wp_unslash( $_REQUEST['refNo'] ) );
			$transaction_id = sanitize_text_field( wp_unslash( $_REQUEST['id'] ) );
			$page_id = sanitize_text_field( wp_unslash( $_REQUEST['page_id'] ) );
			$status = AUROPAY_Payment_Api::auropay_get_payment_status( $transaction_id, $order_id );
			if ( "Authorized" === $status ) {
				update_post_meta( $order_id, AUROPAY_ORDER_STATUS, $status );
				$status_message = __( 'Payment was successfully processed by Auropay Payments', 'auropay-gateway' );
			} else {
				if ( 'Fail' != $status ) {
					update_post_meta( $order_id, AUROPAY_ORDER_STATUS, $status );
					$status_message = __( 'Payment failed', 'auropay-gateway' );
				} else {
					update_post_meta( $order_id, AUROPAY_ORDER_STATUS, 'Failed' );
					$status_message = __( 'Payment failed', 'auropay-gateway' );
				}
			}

			$redirect_url = home_url( '/' ) . "?page_id=" . $page_id;

			add_action( 'wp_footer', function () use ( $status_message, $redirect_url ) {
				?>
				<script type="text/javascript">
					alert("<?php echo esc_js( $status_message ); ?>");
					setTimeout(function() {
						window.location.href = "<?php echo esc_js( $redirect_url ); ?>";
					}, 100); // Delay the redirect by 100ms (this ensures the alert is shown first)
				</script>
	<?php
} );
		} else {
			// Nonce verification failed, handle the error or display an error message.
			die( esc_html( __( 'Nonce callback auropay verification failed.', 'auropay-gateway' ) ) );
		}
	}
}
