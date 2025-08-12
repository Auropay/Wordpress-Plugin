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

add_shortcode( 'AURP', 'auropay_checkout' );
/**
 * This Add the button on checkout page
 *
 * @return string
 */
if ( !function_exists( 'auropay_checkout' ) ) {
	function auropay_checkout() {
		$auropay_page_id = get_the_ID();
		$button_text = get_option( 'auropay_button_text' );
		$image_url = AUROPAY_PLUGIN_URL . '/assets/images/creating-your-order.gif';
		return '<input id="auropay_page_id" type="hidden" value="' . $auropay_page_id . '" />
			<div class="pay-now-div">
				<div class="form-div" style="display: none;">
					<form role="form" id="checkout-auropay-form">
						' . wp_nonce_field('auropay_checkout_pay_form_action', 'auropay-checkout-pay-nonce', true, false) . '
						<h2>Checkout</h2>
						<div class="form-group"><label for="firstname">First Name</label> <input type="text" class="form-control" id="firstname" placeholder="Enter your first name" /> <span id="errMsgFirstname" class="errorMsg"></span></div>
						<div class="form-group"><label for="lastname">Last Name</label> <input type="text" class="form-control" id="lastname" placeholder="Enter your last name" /> <span id="errMsgLastname" class="errorMsg"></span></div>
						<div class="form-group"><label for="phone">Phone Number</label> <input type="tel" maxlength="10" class="form-control" id="phone" placeholder="Enter your phone number" /> <span id="errMsgPhone" class="errorMsg"></span></div>
						<div class="form-group"><label for="email">Email</label> <input type="email" class="form-control" id="email" maxlength="77" placeholder="Enter your email" /> <span id="errMsgEmail" class="errorMsg"></span></div>
						<div class="form-group"><label for="amount">Amount</label> <input type="text" class="form-control" id="amount" placeholder="Enter the amount" /> <span id="errMsgAmount" class="errorMsg"></span></div>
						<button type="button" class="btn btn-primary submitBtn" onClick="auropay_contact_form()">Submit</button>
					</form>
					<div class="timeline-event" id="c_step1" style="display: block;" align="center">
						<div class="timeline-event-content">
							<div class="timeline-event-title"><img src="' . esc_url($image_url) . '" alt="Creating your order" width="400" height="200" /> <br /></div>
							<div class="timeline-event-description">
								<p style="color: #0000ff;">
									Hey! <br />
									We are creating your order
								</p>
							</div>
						</div>
					</div>
				</div>
				<br />
				<input
					type="button"
					class="auropay-place-order-btn button button-primary alt"
					name="auropay_place_order"
					id="auropay_place_order"
					value="' . $button_text . '"
					data-value="Pay Now"
					onClick="auropay_pay_now()"
					class="btn btn-success btn-lg"
					data-toggle="modal"
				/>
			</div>
			<div class="payment-msg"></div>';
	}
}

/**
 * This will get - return url
 *
 * @return string
 */
if ( !function_exists( 'auropay_get_return_url' ) ) {
	function auropay_get_return_url() {
		return str_replace( 'https:', 'http:', add_query_arg( 'wp-api', 'auropay_response', home_url( '/' ) ) );
	}
}

/**
 * This will get - callback return
 *
 * @param int $auropay_page_id current page id
 *
 * @return string
 */
if ( !function_exists( 'auropay_get_callback_url' ) ) {
	function auropay_get_callback_url( $auropay_page_id = '' ) {
		$url = auropay_get_return_url();
		$callback_nonce = wp_create_nonce( 'auropay_callback_nonce' );
		return add_query_arg(
			array(
				'redirectWindow' => 'parent',
				'callback_nonce' => urlencode( $callback_nonce ), // Ensure nonce is URL encoded
				'page_id' => $auropay_page_id,
			),
			$url
		);
	}
}

/**
 * This gives the order parameter
 *
 * @param int   $order_id     order page id
 * @param array $customerData customer data
 * @param float $amount       order amount
 * @param int   $auropay_page_id   page id
 * @param bool  $repay        repay
 *
 * @return string
 */
if ( !function_exists( 'auropay_payment_link_params' ) ) {
	function auropay_payment_link_params( $order_id, $customerData, $amount, $auropay_page_id, $repay = 0 ) {
		if ( $repay ) {
			$title = "Auropay_RePay_" . $order_id . "_" . time();
			$refNo = "Auropay_RePay_" . $order_id . "_" . time();
		} else {
			$title = "Auropay_" . $order_id;
			$refNo = $order_id;
		}

		$curr_date = gmdate( 'Y-m-d H:i:s' );
		$expire_date = strtotime( $curr_date . ' + ' . get_option( 'auropay_expiry' ) . ' minute' );
		$expireOn1 = gmdate( AUROPAY_DATE_FORMAT, $expire_date );
		$expire_date1 = strtotime( $expireOn1 . ' + 30 minute' );
		$expireOn2 = gmdate( AUROPAY_DATE_FORMAT, $expire_date1 );
		$expire_date2 = strtotime( $expireOn2 . ' + 5 hour' );
		$expireOn = gmdate( AUROPAY_DATE_FORMAT, $expire_date2 );
		$customer_array = array( 0 => $customerData );
		$amount = number_format( $amount, 2, '.', '' );

		update_post_meta( $order_id, '_payment_method', 'auropay_gateway' );
		update_post_meta( $order_id, '_auropay_transaction_reference_number', $refNo );

		return array(
			"amount" => $amount,
			"title" => $title,
			"shortDescription" => "",
			"paymentDescription" => "",
			"enablePartialPayment" => false,
			"enableMultiplePayment" => false,
			"enableProtection" => false,
			"displayReceipt" => false,
			"expireOn" => $expireOn,
			"applyPaymentAdjustments" => false,
			"customers" => $customer_array,
			"responseType" => 1,
			"source" => 'ecommerce',
			"platform" => 'WordPress',
			"callbackParameters" => array(
				"ReferenceNo" => $refNo,
				"ReferenceType" => "AuropayOrder",
				"TransactionId" => "",
				"CallbackApiUrl" => auropay_get_callback_url( $auropay_page_id ), //$this->get_callbackapiUrl()
			),
			"settings" => array(
				"displaySummary" => false,
			),
		);
	}
}

add_action( 'wp_ajax_auropay_orders', 'auropay_order_data' );
add_action( 'wp_ajax_nopriv_auropay_orders', 'auropay_order_data' );
add_action( 'wp_enqueue_scripts', 'auropay_add_style' );
/**
 * Includes the styles
 *
 * @return void
 */
if ( !function_exists( 'auropay_add_style' ) ) {
	function auropay_add_style() {
		wp_enqueue_style( 'auropay_button_styles', AUROPAY_PLUGIN_URL . '/assets/css/style.css', array(), filemtime( AUROPAY_PLUGIN_PATH . '/assets/css/style.css' ) );
		wp_enqueue_style( 'auropay_bootstrap_css', AUROPAY_PLUGIN_URL . '/assets/bootstrap-5.3.3/css/bootstrap.min.css', array(), filemtime( AUROPAY_PLUGIN_PATH . '/assets/bootstrap-5.3.3/css/bootstrap.min.css' ) );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'auropay_bootstrap_js', AUROPAY_PLUGIN_URL . '/assets/bootstrap-5.3.3/js/bootstrap.min.js', array(), filemtime( AUROPAY_PLUGIN_PATH . '/assets/bootstrap-5.3.3/js/bootstrap.min.js' ), true );
	}
}

/**
 * Get the payment link after place the order
 *
 * @return string
 */
if ( !function_exists( 'auropay_order_data' ) ) {
	function auropay_order_data() {
		// Validate nonce first
		if (
			! isset( $_POST['auropay-checkout-pay-nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['auropay-checkout-pay-nonce'] ) ),
				'auropay_checkout_pay_form_action'
			)
		) {
			wp_send_json_error( [ 'error_message' => 'Invalid nonce. Please refresh the page and try again.' ], 403 );
			wp_die();
		}

		// Check the request data is set or not
		if ( isset( $_POST['order_action'] ) && 'place_order' == $_POST['order_action'] ) {

			// Retrieve and sanitize input data
			$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
			$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
			$phoneNumber = isset( $_POST['phoneNumber'] ) ? sanitize_text_field( wp_unslash( $_POST['phoneNumber'] ) ) : '';
			$encrypted_amount = isset( $_POST['amount'] ) ? sanitize_text_field( wp_unslash( $_POST['amount'] ) ) : '';
			$amount = floatval( base64_decode( $encrypted_amount ) );
			$firstname = isset( $_POST['firstname'] ) ? sanitize_text_field( wp_unslash( $_POST['firstname'] ) ) : '';
			$lastname = isset( $_POST['lastname'] ) ? sanitize_text_field( wp_unslash( $_POST['lastname'] ) ) : '';
			$auropay_page_id = isset( $_POST['auropay_page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['auropay_page_id'] ) ) : '';

			// Prepare customers data and API request
			$customerData = array(
				"firstName" => $firstname,
				"lastName" => $lastname,
				"email" => $email,
				"phone" => $phoneNumber,
			);

			// Update order metadata
			auropay_update_order_metadata( $order_id, $email, $phoneNumber, $amount );

			$params = auropay_payment_link_params( $order_id, $customerData, $amount, $auropay_page_id, 0 );
			$response = AUROPAY_Payment_Api::auropay_get_payment_link( $params );

			// Handle the response
			if ( 400 == $response['status_code'] ) {
				echo wp_json_encode( ['error_message' => $response['message'], 'status_code' => $response['status_code']], JSON_PRETTY_PRINT );
			} else {
				// Handle the success response
				update_post_meta( $order_id, '_auropay_payment_link', $response['paymentLink'] );
				update_post_meta( $order_id, '_auropay_payment_link_id', $response['id'] );
				echo wp_json_encode( ['paymentLink' => $response['paymentLink']], JSON_PRETTY_PRINT );
			}
		}else {
			wp_send_json_error( [ 'error_message' => 'Invalid action.' ], 400 );
		}
		wp_die();
	}
}

/**
 * Update order metadata
 */
function auropay_update_order_metadata( $order_id, $email, $phoneNumber, $amount ) {
	update_post_meta( $order_id, '_order_key', 'auropay_order' );
	update_post_meta( $order_id, '_email', $email );
	update_post_meta( $order_id, '_phoneNumber', $phoneNumber );
	update_post_meta( $order_id, '_amount', $amount );
}

add_action( 'wp_ajax_auropay_refund_order', 'auropay_refund_order' );
add_action( 'wp_ajax_nopriv_auropay_refund_order', 'auropay_refund_order' );
add_action( 'admin_footer', 'auropay_refund_js' );

/**
 * Refund the amount
 *
 * @return string
 */
if (!function_exists('auropay_refund_order')) {
	function auropay_refund_order()
	{
		if (isset($_POST['auropay-refund-form-nonce']) && ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['auropay-refund-form-nonce'])), 'auropay_refund_form_action')) {
			wp_send_json_error(__('Nonce refund auropay verification failed.', 'auropay-gateway'));
		}

		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Permission denied.');
		}

		if (isset($_POST['refund_action']) && 'refund_order' == $_POST['refund_action']) {
			// Retrieve and sanitize input data
			$refundAmount = isset($_POST['refundAmount']) ? floatval($_POST['refundAmount']) : 0;
			$order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
			$sanitisedReason = isset($_POST['reason']) ? sanitize_text_field(wp_unslash($_POST['reason'])) : '';
			if ('' == $_POST['reason']) {
				$reason = "Refund for order " . $order_id;
			} else {
				$reason = $sanitisedReason;
			}

			//Request parameters for refund
			$params = array(
				"UserType" => 1,
				"Amount" => $refundAmount,
				"Remarks" => $reason,
			);

			// Handle the response
			$response = AUROPAY_Payment_Api::auropay_process_refund($params, $order_id);
			echo wp_json_encode($response, JSON_PRETTY_PRINT);
			die();
		}
	}
}

/**
 * Enqueue refund JavaScript
 *
 * @return void
 */
if ( !function_exists( 'auropay_refund_js' ) ) {
	function auropay_refund_js() {
		$order_id = isset( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : 0;
		if ( $order_id ) {
			if ( isset( $_REQUEST['refund_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['refund_nonce'] ) ), 'auropay_refund_nonce' ) ) {

				// Pass data to JavaScript
				wp_enqueue_script( 'auropay-refund-js', AUROPAY_PLUGIN_URL . '/assets/js/auropay-refund.js', array( 'jquery' ), '1.0', true );
				wp_localize_script( 'auropay-refund-js', 'auropayRefundData', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'order_id' => $order_id,
					'refund_sec_nonce' => wp_create_nonce('auropay_refund_form_action'),
					'confirm_text' => __( 'Are you sure you wish to process this refund? This action cannot be undone.', 'auropay-gateway' ),
					'error_empty' => __( 'Please enter an amount.', 'auropay-gateway' ),
					'error_invalid' => __( 'Invalid refund amount entered.', 'auropay-gateway' ),
					'error_system' => __( 'System unable to process the refund. Contact Auropay Support at support@auropay.net', 'auropay-gateway' ),
				) );
			} else {
				// Nonce verification failed, handle the error or display an error message.
				die( esc_html( __( 'Nonce refund auropay verification failed.', 'auropay-gateway' ) ) );
			}
		}
	}
}

/**
 * Enqueue Place Order JavaScript
 *
 * @return void
 */
function auropay_enqueue_scripts() {
	wp_enqueue_script(
		'auropay-js',
		AUROPAY_PLUGIN_URL . '/assets/js/auropay.js',
		array( 'jquery' ),
		'1.0',
		true
	);

	wp_localize_script( 'auropay-js', 'AUROPAY', array(
		'EXPIRY' => get_option( 'auropay_expiry' ),
		'AUROPAY_PLUGIN_URL' => esc_url( AUROPAY_PLUGIN_URL ),
		'AJAX_URL' => admin_url( 'admin-ajax.php' ),
		'NONCE' => wp_create_nonce('auropay_checkout_pay_form_action'),
	) );
}
add_action( 'wp_enqueue_scripts', 'auropay_enqueue_scripts' );
