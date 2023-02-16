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
    exit;
}

add_shortcode('AURP', 'apCheckout');
/**
 * This Add the button on checkout page
 * 
 * @return string
 */
function apCheckout()
{
    $ap_page_id = get_the_ID();
    $button_text = get_option('ap_button_text');
    $html = '<input id="ap_page_id" type="hidden" value="' . $ap_page_id . '"><div class="pay-now-div"><div class="form-div" style="display:none"><form role="form" id="checkout-auropay-form"> <h2>Checkout </h2><div class="form-group"> <label for="phone">Phone Number</label> <input type="text" class="form-control" id="phone" placeholder="Enter your phone number"/> <span id="errMsgPhone" class="errorMsg"></span> </div><div class="form-group"> <label for="email">Email</label> <input type="email" class="form-control" id="email" placeholder="Enter your email"/> <span id="errMsgEmail" class="errorMsg"></span> </div><div class="form-group"> <label for="amount">Amount</label> <input type="text" class="form-control" id="amount" placeholder="Enter the amount"/> <span id="errMsgAmount" class="errorMsg"></span> </div><button type="button" class="btn btn-primary submitBtn" onClick="ap_contact_form()">SUBMIT</button> </form> <div class="timeline-event" id="c_step1" style="display:block;" align="center"> <div class="timeline-event-content"> <div class="timeline-event-title"> <img src="' . AUROPAY_PLUGIN_URL . '/assets/images/creating-your-order.gif" width="400" height="200"/> <br/> </div><div class="timeline-event-description"> <p style="color:#0000ff;">Hey! <br/> We are creating your order</p></div></div></div></div><br><input type="button"  class="auropay-place-order-btn button button-primary alt" name="auropay_place_order" id="auropay_place_order" value="' . $button_text . '" data-value="Pay Now" onClick="ap_pay_now()" class="btn btn-success btn-lg" data-toggle="modal" />
        </div> <div class="payment-msg"></div>';
    return $html;
}

/**
 * This will get - return url
 * 
 * @return string
 */
function apGetReturnUrl()
{
    return str_replace('https:', 'http:', add_query_arg('wp-api', 'ap_response', home_url('/')));
}

/**
 * This will get - callback return
 * 
 * @param int $ap_page_id current page id
 * 
 * @return string
 */
function apGetCallbackUrl($ap_page_id = '')
{
    $url = apGetReturnUrl();
    return $url =  $url . "&redirectWindow=parent&page_id=" . $ap_page_id;
}

/**
 * This gives the order parameter
 * 
 * @param int   $order_id     order page id
 * @param array $customerData customer data 
 * @param float $amount       order amount 
 * @param int   $ap_page_id   page id
 * @param bool  $repay        repay 
 * 
 * @return string
 */
function apPaymentLinkParams($order_id, $customerData, $amount, $ap_page_id, $repay = 0)
{
    if ($repay) {
        $title = "Auropay_RePay_" . $order_id . "_" . time();
        $refNo = "Auropay_RePay_" . $order_id . "_" . time();
    } else {
        $title = "Auropay_" . $order_id;
        $refNo = $order_id;
    }

    $curr_date = date('Y-m-d H:i:s');
    $expire_date = strtotime($curr_date . ' + ' . get_option('ap_expiry') . ' minute');
    $expireOn1 = date('d-m-Y H:i:s', $expire_date);
    $expire_date1 = strtotime($expireOn1 . ' + 30 minute');
    $expireOn2 = date('d-m-Y H:i:s', $expire_date1);
    $expire_date2 = strtotime($expireOn2 . ' + 5 hour');
    $expireOn = date('d-m-Y H:i:s', $expire_date2);
    $customer_array = array(0 => $customerData);
    $amount = number_format($amount, 2, '.', '');

    update_post_meta($order_id, '_payment_method', 'auropay_gateway');
    update_post_meta($order_id, '_ap_accesskey', ACCESSKEY);
    update_post_meta($order_id, '_ap_securetoken', SECRETKEY);
    update_post_meta($order_id, '_ap_transaction_reference_number', $refNo);

    $request_data = array(
        "amount" => $amount,
        "title" =>  $title,
        "shortDescription" =>  "",
        "paymentDescription" => "",
        "invoiceNumber" => $order_id,
        "enablePartialPayment" =>  false,
        "enableMultiplePayment" =>  false,
        "enableProtection" =>  false,
        "displayReceipt" =>  false,
        "expireOn" =>  $expireOn,
        "applyPaymentAdjustments" =>  false,
        "customers" =>  $customer_array,
        "responseType" => 1,
        "source" => 'ecommerce',
        "platform" => 'wordpress',
        "callbackParameters" => array(
            "AccessKey" => ACCESSKEY,
            "SecretKey" => SECRETKEY,
            "ReferenceNo" => $refNo,
            "ReferenceType" => "AuropayOrder",
            "TransactionId" => "",
            "CallbackApiUrl" => apGetCallbackUrl($ap_page_id), //$this->get_callbackapiUrl()
        ),
        "settings" => array(
            "displaySummary" => false,
        )
    );
    return $request_data;
}

add_action('wp_ajax_ajax_order', 'apAjaxOrderData');
add_action('wp_ajax_nopriv_ajax_order', 'apAjaxOrderData');
add_action('wp_head', 'apOrderHookJs');
add_action('wp_enqueue_scripts', 'apAddStyle');
/**
 * Includes the styles
 * 
 * @return void
 */
function apAddStyle()
{
    wp_enqueue_style('auropay_button_styles', AUROPAY_PLUGIN_URL . '/assets/css/style.css');
    wp_enqueue_style('auropay_bootstrap_css', AUROPAY_PLUGIN_URL . '/assets/bootstrap/3.4.1/css/bootstrap.min.css');
    wp_enqueue_script('auropay_js', get_site_url() . '/wp-includes/js/jquery/jquery.js');
    wp_enqueue_script('auropay_bootstrap_js', AUROPAY_PLUGIN_URL . '/assets/bootstrap/3.4.1/js/bootstrap.min.js');
}

/**
 * Get the payment link after place the order
 * 
 * @return string
 */
function apAjaxOrderData()
{
    // check if request come from place order or pay order
    if (isset($_POST['order_action']) && $_POST['order_action'] == 'place_order') {
        $order_id = $_POST['order_id'];
        $email = $_POST['email'];
        $phoneNumber = $_POST['phoneNumber'];
        $amount = $_POST['amount'];
        $ap_page_id = $_POST['ap_page_id'];
        $customerData = array(
            "email" =>  $email,
            "phone" =>  $phoneNumber
        );

        update_post_meta($order_id, '_order_key', 'auropay_order');
        update_post_meta($order_id, '_email', $email);
        update_post_meta($order_id, '_phoneNumber', $phoneNumber);
        update_post_meta($order_id, '_amount', $amount);

        $redirect = AUROPAY_PLUGIN_URL . '/includes/callback-payment-status';
        $params =  apPaymentLinkParams($order_id, $customerData, $amount, $ap_page_id, 0);
        $response = Auropay_API::apGetPaymentLink($params);

        if ($response['status_code'] == 400) {
            echo json_encode(['error_message' => $response['message'], 'status_code' => $response['status_code']]);
        } else {
            update_post_meta($order_id, '_ap_payment_link', $response['paymentLink']);
            update_post_meta($order_id, '_ap_payment_link_id', $response['id']);
            echo json_encode(['paymentLink' => $response['paymentLink']]);
        }
        exit;
    }
    die();
}

add_action('wp_ajax_ajax_refund_order', 'apRefundOrder');
add_action('wp_ajax_nopriv_ajax_refund_order', 'apRefundOrder');
add_action('admin_footer', 'apRefundOrderJs');

/**
 * Refund the amount
 * 
 * @return string
 */
function apRefundOrder()
{
    if (isset($_POST['refund_action']) && $_POST['refund_action'] == 'refund_order') {
        $refundAmount = $_POST['refundAmount'];
        $order_id = $_POST['order_id'];
        if ($_POST['reason'] == '') {
            $reason = "Refund for order " . $_POST['order_id'];
        } else {
            $reason = $_POST['reason'];
        }
        $params = array(
            "UserType" => 1,
            "Amount" => $refundAmount,
            "Remarks" =>  $reason,
        );

        $response = Auropay_Api::apProcessRefund($params, $order_id);
        if ($response) {
            echo json_encode($response);
        } else {
            echo json_encode($response);
        }
        exit;
    }
}

/**
 * Refund js
 * 
 * @return void
 */
function apRefundOrderJs()
{
    $ap_order_id = $_GET['order_id'] ?? 0;
?>
    <script>
        function ap_refund_amount() {
            let message = 'Are you sure you wish to process this refund? This action cannot be undone.';
            if (confirm(message) == true) {
                var refund_amount = jQuery('#refund_amount').val();
                var order_id = '<?php echo $ap_order_id; ?>';
                var refund_reason = jQuery('#refund_reason').val();
                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
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
                        if (!result) {
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
    </script>

<?php
}

/**
 * Place order js
 * 
 * @return void
 */
function apOrderHookJs()
{
    $orderID = mt_rand(0, mt_getrandmax());
    $ap_expiry = get_option('ap_expiry');
?>
    <script>
        var ap_expiry_min = <?php echo $ap_expiry; ?>;
        var ap_timeout_in_miliseconds = ap_expiry_min * 60000;
        var ap_timeout_id;
        var ap_page_id = 0;

        function ap_startTimer() {
            // window.setTimeout returns an Id that can be used to start and stop a timer
            ap_timeout_id = window.setTimeout(ap_doInactive, ap_timeout_in_miliseconds)
        }

        function ap_doInactive() {
            alert("Found no activity, so reloading checkout page again");
            // does whatever you need it to actually do - probably signs them out or stops polling the server for info
            window.location.reload();
        }

        function ap_setupTimers() {
            document.addEventListener("mousemove", ap_resetTimer, false);
            document.addEventListener("mousedown", ap_resetTimer, false);
            document.addEventListener("keypress", ap_resetTimer, false);
            document.addEventListener("touchmove", ap_resetTimer, false);
            ap_startTimer();
        }

        function ap_resetTimer() {
            window.clearTimeout(ap_timeout_id)
            ap_startTimer();
        }

        function ap_pay_now() {
            ap_page_id = jQuery('#ap_page_id').val();
            jQuery('.form-div').css('display', 'block');
            jQuery('#auropay_place_order').css('display', 'none');
            jQuery('#c_step1').css('display', 'none');
        }

        function ap_contact_form() {
            var reg = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
            var phone_number_regex = /^[0-9-+]+$/;
            var phoneNumber = jQuery('#phone').val();
            var email = jQuery('#email').val();
            var amount = jQuery('#amount').val();
            jQuery('#errMsgPhone').html('');
            jQuery('#errMsgEmail').html('');
            jQuery('#errMsgAmount').html('');
            if (phoneNumber.trim() == '') {
                jQuery('#errMsgPhone').html('Please enter your phone number.');
                jQuery('#phone').focus();
                return false;
            } else if (phoneNumber.trim() != '' && !phone_number_regex.test(phoneNumber)) {
                jQuery('#errMsgPhone').html('Please enter valid phone number.');
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
            } else if (amount.trim() == '') {
                jQuery('#errMsgAmount').html('Please enter amount.');
                jQuery('#amount').focus();
                return false;
            } else {
                jQuery('#c_step1').css('display', 'block');
                jQuery.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    dataType: 'json',
                    cache: false,
                    data: {
                        'action': 'ajax_order',
                        'order_id': '<?php echo $orderID; ?>',
                        'order_action': 'place_order',
                        'email': email,
                        'phoneNumber': phoneNumber,
                        'amount': amount,
                        'ap_page_id': ap_page_id
                    },
                    success: function(result) {
                        if (result.status_code != 400) {
                            if (jQuery('#auropay_iframe').length == 0) {
                                jQuery('.pay-now-div').html('<iframe src="' + result.paymentLink + '" name="auropay_iframe" id="auropay_iframe" scrolling="yes" frameborder=0 class="iframe-cs" style="display:block;width:400px;height:450px;"></iframe>');
                            }
                        } else {
                            jQuery('#c_step1').css('display', 'none');
                            jQuery('#errMsgAmount').html('<div id="err-msg">Error when loading the payment form, please contact support team!</div>');
                        }
                        ap_setupTimers();
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
?>