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
		return '<input id="auropay_page_id" type="hidden" value="' . $auropay_page_id . '"><div class="pay-now-div"><div class="form-div" style="display:none"><form role="form" id="checkout-auropay-form"> <h2>Checkout </h2><div class="form-group"> <label for="firstname">Firstname</label> <input type="text" class="form-control" id="firstname" placeholder="Enter your firstname"/> <span id="errMsgFirstname" class="errorMsg"></span> </div><div class="form-group"> <label for="lastname">Lastname</label> <input type="text" class="form-control" id="lastname" placeholder="Enter your lastname"/> <span id="errMsgLastname" class="errorMsg"></span> </div><div class="form-group"> <label for="phone">Phone Number</label> <input type="tel" maxlength="10" class="form-control" id="phone" placeholder="Enter your phone number"/> <span id="errMsgPhone" class="errorMsg"></span> </div><div class="form-group"> <label for="email">Email</label> <input type="email" class="form-control" id="email" maxlength="77" placeholder="Enter your email"/> <span id="errMsgEmail" class="errorMsg"></span> </div><div class="form-group"> <label for="amount">Amount</label> <input type="text" class="form-control" id="amount" placeholder="Enter the amount"/> <span id="errMsgAmount" class="errorMsg"></span> </div><button type="button" class="btn btn-primary submitBtn" onClick="auropay_contact_form()">SUBMIT</button> </form> <div class="timeline-event" id="c_step1" style="display:block;" align="center"> <div class="timeline-event-content"> <div class="timeline-event-title"> <img src="' . AUROPAY_PLUGIN_URL . '/assets/images/creating-your-order.gif" width="400" height="200"/> <br/> </div><div class="timeline-event-description"> <p style="color:#0000ff;">Hey! <br/> We are creating your order</p></div></div></div></div><br><input type="button"  class="auropay-place-order-btn button button-primary alt" name="auropay_place_order" id="auropay_place_order" value="' . $button_text . '" data-value="Pay Now" onClick="auropay_pay_now()" class="btn btn-success btn-lg" data-toggle="modal" />
        </div> <div class="payment-msg"></div>';
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
		return $url = $url . "&redirectWindow=parent&page_id=" . $auropay_page_id;
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

		$curr_date = date( 'Y-m-d H:i:s' );
		$expire_date = strtotime( $curr_date . ' + ' . get_option( 'auropay_expiry' ) . ' minute' );
		$expireOn1 = date( AUROPAY_DATE_FORMAT, $expire_date );
		$expire_date1 = strtotime( $expireOn1 . ' + 30 minute' );
		$expireOn2 = date( AUROPAY_DATE_FORMAT, $expire_date1 );
		$expire_date2 = strtotime( $expireOn2 . ' + 5 hour' );
		$expireOn = date( AUROPAY_DATE_FORMAT, $expire_date2 );
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
			"platform" => 'wordpress',
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

add_action( 'wp_ajax_ajax_orders', 'auropay_ajax_order_data' );
add_action( 'wp_ajax_nopriv_ajax_orders', 'auropay_ajax_order_data' );
add_action( 'wp_head', 'auropay_order_hook_js' );
add_action( 'wp_enqueue_scripts', 'auropay_add_style' );
/**
 * Includes the styles
 *
 * @return void
 */
if ( !function_exists( 'auropay_add_style' ) ) {
	function auropay_add_style() {
		wp_enqueue_style( 'auropay_button_styles', AUROPAY_PLUGIN_URL . '/assets/css/style.css' );
		wp_enqueue_style( 'auropay_bootstrap_css', AUROPAY_PLUGIN_URL . '/assets/bootstrap-5.3.3/css/bootstrap.min.css' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'auropay_bootstrap_js', AUROPAY_PLUGIN_URL . '/assets/bootstrap-5.3.3/js/bootstrap.min.js' );
	}
}

/**
 * Get the payment link after place the order
 *
 * @return string
 */
if ( !function_exists( 'auropay_ajax_order_data' ) ) {
	function auropay_ajax_order_data() {
		// check if request come from place order or pay order
		if ( isset( $_POST['order_action'] ) && 'place_order' == $_POST['order_action'] ) {
			$order_id = sanitize_text_field( $_POST['order_id'] );
			$email = sanitize_email( $_POST['email'] );
			$phoneNumber = sanitize_text_field( $_POST['phoneNumber'] );
			$amount = sanitize_text_field( $_POST['amount'] );
			$firstname = sanitize_text_field( $_POST['firstname'] );
			$lastname = sanitize_text_field( $_POST['lastname'] );
			$auropay_page_id = sanitize_text_field( $_POST['auropay_page_id'] );
			$customerData = array(
				"firstName" => $firstname,
				"lastName" => $lastname,
				"email" => $email,
				"phone" => $phoneNumber,
			);

			update_post_meta( $order_id, '_order_key', 'auropay_order' );
			update_post_meta( $order_id, '_email', $email );
			update_post_meta( $order_id, '_phoneNumber', $phoneNumber );
			update_post_meta( $order_id, '_amount', $amount );

			$params = auropay_payment_link_params( $order_id, $customerData, $amount, $auropay_page_id, 0 );
			$response = AUROPAY_Payment_Api::auropay_get_payment_link( $params );

			if ( 400 == $response['status_code'] ) {
				echo wp_json_encode( ['error_message' => $response['message'], 'status_code' => $response['status_code']], JSON_PRETTY_PRINT );
			} else {
				update_post_meta( $order_id, '_auropay_payment_link', $response['paymentLink'] );
				update_post_meta( $order_id, '_auropay_payment_link_id', $response['id'] );
				echo wp_json_encode( ['paymentLink' => $response['paymentLink']], JSON_PRETTY_PRINT );
				die();
			}
			exit;
		}
		die();
	}
}

add_action( 'wp_ajax_ajax_refund_order', 'auropay_refund_order' );
add_action( 'wp_ajax_nopriv_ajax_refund_order', 'auropay_refund_order' );
add_action( 'admin_footer', 'auropay_refund_order_js' );

/**
 * Refund the amount
 *
 * @return string
 */
if ( !function_exists( 'auropay_refund_order' ) ) {
	function auropay_refund_order() {
		if ( isset( $_POST['refund_action'] ) && 'refund_order' == $_POST['refund_action'] ) {
			$refundAmount = sanitize_text_field( $_POST['refundAmount'] );
			$order_id = sanitize_text_field( $_POST['order_id'] );
			if ( '' == $_POST['reason'] ) {
				$reason = "Refund for order " . sanitize_text_field( $_POST['order_id'] );
			} else {
				$reason = sanitize_text_field( $_POST['reason'] );
			}
			$params = array(
				"UserType" => 1,
				"Amount" => $refundAmount,
				"Remarks" => $reason,
			);

			$response = AUROPAY_Payment_Api::auropay_process_refund( $params, $order_id );
			echo wp_json_encode( $response, JSON_PRETTY_PRINT );
			die();
		}
	}
}

/**
 * Refund js
 *
 * @return void
 */
if ( !function_exists( 'auropay_refund_order_js' ) ) {
	function auropay_refund_order_js() {
		$order_id = isset( $_GET['order_id'] ) ? $_GET['order_id'] : 0;
		$auropay_order_id = sanitize_text_field( $order_id );
		?>
<script>
function auropay_refund_amount() {
	let message = 'Are you sure you wish to process this refund? This action cannot be undone.';
	if (confirm(message) == true) {
		var refund_amount = jQuery('#refund_amount').val();
		var order_id = '<?php echo esc_html( $auropay_order_id ); ?>';
		var refund_reason = jQuery('#refund_reason').val();
		if (refund_amount.trim() == '') {
			jQuery('#refundError').html("Please enter Amount");
			return false;
		} else if (refund_amount.trim() < 1) {
			jQuery('#refundError').html("Invalid refund amount entered.");
			return false;
		} else {
			jQuery('#refundError').html('');
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
				dataType: 'json',
				data: {
					'action': 'ajax_refund_order',
					'refund_action': 'refund_order',
					'refundAmount': refund_amount,
					'order_id': order_id,
					'reason': refund_reason,
				},
				cache: false,
				success: function(result) {
					if (result === false) {
						alert('System unable to Refund. Contact Auropay Support at support@auropay.net')
					} else {
						location.reload();
					}
				},
				error: function(error) {
					console.log(error);
				}
			});
		}
	}
}
</script>

<?php
}
}

/**
 * Place order js
 *
 * @return void
 */
if ( !function_exists( 'auropay_order_hook_js' ) ) {
	function auropay_order_hook_js() {
		$auropay_expiry = get_option( 'auropay_expiry' );
		?>
<script>
var auropay_expiry_min = <?php echo esc_html( $auropay_expiry ); ?>;
var auropay_timeout_in_miliseconds = auropay_expiry_min * 60000;
var auropay_timeout_id;
var auropay_page_id = 0;

function auropay_hide_iframe() {
	jQuery('#auropay_iframe').hide();
	jQuery('#err-msg').hide();
	jQuery('#close_iframe').hide();
	jQuery('#c_step1').hide();
	jQuery('.submitBtn').show();
}

function auropay_start_timer() {
	// window.setTimeout returns an Id that can be used to start and stop a timer
	auropay_timeout_id = window.setTimeout(auropay_do_inactive, auropay_timeout_in_miliseconds)
}

function auropay_do_inactive() {
	alert("Found no activity, so reloading checkout page again");
	// does whatever you need it to actually do - probably signs them out or stops polling the server for info
	window.location.reload();
}

function auropay_setup_timers() {
	document.addEventListener("mousemove", auropay_reset_timer, false);
	document.addEventListener("mousedown", auropay_reset_timer, false);
	document.addEventListener("keypress", auropay_reset_timer, false);
	document.addEventListener("touchmove", auropay_reset_timer, false);
	auropay_start_timer();
}

function auropay_reset_timer() {
	window.clearTimeout(auropay_timeout_id)
	auropay_start_timer();
}

function auropay_pay_now() {
	auropay_page_id = jQuery('#auropay_page_id').val();
	jQuery('.form-div').css('display', 'block');
	jQuery('#auropay_place_order').css('display', 'none');
	jQuery('#c_step1').css('display', 'none');
}

function auropay_mt_rand(min, max) {
	// Calculate a random number between min (inclusive) and max (exclusive)
	return Math.floor(Math.random() * (max - min)) + min;
}

function auropay_contact_form() {
	var orderId = auropay_mt_rand(0, Math.pow(2, 31));
	var reg = /^[\w]{1,}[\w.+\-]{0,}@[\w\-]{2,}(\.[\w\-]{2,})?\.[a-zA-Z]{2,4}$/;
	var phone_number_regex = /^[0-9-+]+$/;
	var amount_regex = /^(?!0$)([1-9]\d*)(\.\d{1,2})?$/;
	var name_regex = /^[a-zA-Z\s]{1,70}$/;
	var firstName = jQuery('#firstname').val();
	var lastName = jQuery('#lastname').val();
	var phoneNumber = jQuery('#phone').val();
	var email = jQuery('#email').val();
	var amount = jQuery('#amount').val();
	var delimg =
		"<img src='<?php echo esc_url( AUROPAY_PLUGIN_URL ); ?>/assets/images/close.png' width='20' height='20' onClick='auropay_hide_iframe()' style='cursor:pointer' >";

	jQuery('#errMsgFirstname').html('');
	jQuery('#errMsgLastname').html('');
	jQuery('#errMsgPhone').html('');
	jQuery('#errMsgEmail').html('');
	jQuery('#errMsgAmount').html('');
	if (firstName.trim() == '') {
		jQuery('#errMsgFirstname').html('Please enter firstname.');
		jQuery('#firstname').focus();
		return false;
	}else if (firstName.trim() != '' && !name_regex.test(firstName)) {
		jQuery('#errMsgFirstname').html('Please enter valid firstname.');
		jQuery('#firstname').focus();
		return false;
	} else if (lastName.trim() == '') {
		jQuery('#errMsgLastname').html('Please enter lastname.');
		jQuery('#lastname').focus();
		return false;
	}else if (lastName.trim() != '' && !name_regex.test(lastName)) {
		jQuery('#errMsgLastname').html('Please enter valid firstname.');
		jQuery('#lastname').focus();
		return false;
	}else if (phoneNumber.trim() == '') {
		jQuery('#errMsgPhone').html('Please enter your phone number.');
		jQuery('#phone').focus();
		return false;
	}else if (phoneNumber.trim() != '' && !phone_number_regex.test(phoneNumber)) {
		jQuery('#errMsgPhone').html('Please enter valid phone number.');
		jQuery('#phone').focus();
		return false;
	} else if (phoneNumber.trim() != '' && phoneNumber.length != 10) {
		jQuery('#errMsgPhone').html('Phone number is not valid. Enter 10 digit number.');
		jQuery('#phone').focus();
		return false;
	} else if (email.trim() == '') {
		jQuery('#errMsgEmail').html('Please enter your email.');
		jQuery('#email').focus();
		return false;
	} else if (email.trim() != '' && !reg.test(email)) {
		jQuery('#errMsgEmail').html('Please enter valid email.');
		jQuery('#email').focus();
		return false;
	} else if (email.length > 77) {
		jQuery('#errMsgEmail').html('Email address cannot be longer than 77 characters.');
		jQuery('#email').focus();
		return false;
	} else if (amount.trim() == '') {
		jQuery('#errMsgAmount').html('Please enter amount.');
		jQuery('#amount').focus();
		return false;
	} else if (!amount_regex.test(amount)) {
		jQuery('#errMsgAmount').html('Please enter a valid amount.');
		jQuery('#amount').focus();
		return false;
	} else if (amount.length > 15) {
		jQuery('#errMsgAmount').html('Amount is too long. Please enter a shorter amount.');
		jQuery('#amount').focus();
		return false;
	} else {
		jQuery('#c_step1').css('display', 'block');
		jQuery.ajax({
			type: 'POST',
			url: '<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>',
			dataType: 'json',
			cache: false,
			data: {
				'action': 'ajax_orders',
				'order_id': orderId,
				'order_action': 'place_order',
				'email': email,
				'phoneNumber': phoneNumber,
				'amount': amount,
				'firstname': firstName,
				'lastname': lastName,
				'auropay_page_id': auropay_page_id
			},
			success: function(result) {
				jQuery('#close_iframe').show();
				if (result.status_code != 400) {
					jQuery('.submitBtn').hide();
					jQuery('#c_step1').hide();
					if (jQuery('#auropay_iframe').length == 0) {
						jQuery('.pay-now-div').append(
							'<div class="del-img" id="close_iframe" style="display:block">' +
							delimg + '</div>');
						jQuery('.pay-now-div').append('<iframe src="' + result.paymentLink +
							'" name="auropay_iframe" id="auropay_iframe" scrolling="yes" frameborder=0 class="iframe-cs" style="display:block;"></iframe>'
						);
					} else {
						jQuery('#auropay_iframe').remove();
						jQuery('.pay-now-div').append('<iframe src="' + result.paymentLink +
							'" name="auropay_iframe" id="auropay_iframe" scrolling="yes" frameborder=0 class="iframe-cs" style="display:block;"></iframe>'
						);
					}
				} else {
					jQuery('#close_iframe').hide();
					jQuery('#c_step1').css('display', 'none');
					jQuery('#errMsgAmount').html(
						'<div id="err-msg">Error when loading the payment form, please contact support team!</div>'
					);
				}
				auropay_setup_timers();
			},
			error: function(error) {
				console.log(error);
			}
		});
	}
}
</script>

<?php
}
}
?>
