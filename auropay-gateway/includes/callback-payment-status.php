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

if (isset($_REQUEST['refNo']) && isset($_REQUEST['page_id']) && isset($_REQUEST['id'])) {
    $order_id = 0;
    $transaction_id = 0;
    $order_id = sanitize_text_field($_REQUEST['refNo']);
    $transaction_id = sanitize_text_field($_REQUEST['id']);
    $page_id = sanitize_text_field($_REQUEST['page_id']);
    $redirect_url = home_url('/') . '?page_id=' . $page_id;
    $status = ARP_Payment_Api::arp_get_payment_status($transaction_id, $order_id);

    if ($status == "Authorized") {
        update_post_meta($order_id, ARP_ORDER_STATUS, $status);
        echo '<script>alert("Payment was successfully processed by Auropay Payments")</script>';
    } else {
        if ('Fail' != $status) {
            update_post_meta($order_id, ARP_ORDER_STATUS, $status);
            echo '<script>alert("Payment failed")</script>';
        } else {
            update_post_meta($order_id, ARP_ORDER_STATUS, 'Failed');
            echo '<script>alert("Payment failed")</script>';
        }
    }

    echo '<script>'
        . "parent.location.href = '" . $redirect_url . "'"
        . '</script>';
    exit;
}
