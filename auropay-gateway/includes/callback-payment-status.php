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

global $wp;

if (isset($_REQUEST['refNo'])) {
    $order_id = 0;
    $transaction_id = 0;
    $order_id = $_REQUEST['refNo'];
    $transaction_id = $_REQUEST['id'];
    $page_id = $_REQUEST['page_id'];
    $redirect_url = home_url('/') . '?page_id=' . $page_id;
    $status = Auropay_Api::apGetPaymentStatus($transaction_id, $order_id);

    if ($status == "Success") {
        update_post_meta($order_id, '_auropay_order_status', $status);
        echo '<script>alert("Payment was successfully processed by Auropay Payments")</script>';
    } else {
        update_post_meta($order_id, '_auropay_order_status', 'Failed');
        echo '<script>alert("Payment failed")</script>';
    }

    echo '<script>'
        . "parent.location.href = '" . $redirect_url . "'"
        . '</script>';
    exit;
}
