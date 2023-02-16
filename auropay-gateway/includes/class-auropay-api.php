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

/**
 * Communicates with auropay payment API
 *
 * @category Payment
 * @package  AuroPay_Gateway_For_Wordpress
 * @author   Akshita Minocha <akshita.minocha@aurionpro.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://auropay.net/
 */
class Auropay_Api
{
    /**
     * Check merchant keys are correct
     * 
     * @param string $referenceNo reference number of order
     * @param string $accessKey   accesskey of merchant
     * @param string $secretKey   secretKey of merchant
     * @param string $apiUrl      api url
     * 
     * @return array
     */
    public static function apValidateApiKey($referenceNo, $accessKey, $secretKey, $apiUrl)
    {
        $api = "api/payments/refno/" . $referenceNo;
        $headers = array('x-version' => '1.0', 'x-access-key' => $accessKey, 'x-secret-key' => $secretKey, 'content-type' => 'application/json');
        $endpoint = $apiUrl . $api;

        try {
            $response = wp_remote_post(
                $endpoint,
                [
                    'method'  => 'GET',
                    'headers' => $headers,
                    'timeout' => 50,
                ]
            );

            Custom_Log_Functions::apPluginLog('Something happened.');
            if ($response['response']['code'] != 200 && $response['response']['code'] != 201 && $response['response']['code'] != 204) {
                Custom_Log_Functions::apPluginLog("api:" . $api . "###ERROR:" . $response['body']);
            }

            Custom_Log_Functions::apPluginLog("called api:" . $api);
            return $response['response']['code'];
        } catch (Exception $e) {
            Custom_Log_Functions::apPluginLog("called api:" . $api . "#response:error");
            return array('error' => true);
        }
    }

    /**
     * Send the request to HP API with api key
     * 
     * @param string $api    api url
     * @param string $method method of api
     * @param array  $params parameters
     * 
     * @return array
     */
    public static function apApiKeyRequest($api, $method = 'POST', $params = array())
    {
        Custom_Log_Functions::log("calling api:" . $api);

        $headers = array('x-version' => '1.0', 'x-access-key' => ACCESSKEY, 'x-secret-key' => SECRETKEY, 'content-type' => 'application/json');

        $endpoint = APIURL . $api;

        try {
            if ($method == 'POST') {
                $response = wp_remote_post(
                    $endpoint,
                    [
                        'method'  => $method,
                        'headers' => $headers,
                        'body'    => json_encode($params),
                        'timeout' => 50,
                    ]
                );
            } else {
                $response = wp_remote_post(
                    $endpoint,
                    [
                        'method'  => $method,
                        'headers' => $headers,
                        'timeout' => 50,
                    ]
                );
            }

            if ($response['response']['code'] != 200 && $response['response']['code'] != 201 && $response['response']['code'] != 204) {
                Custom_Log_Functions::log("api:" . $api . "###params:" . json_encode($params));
                Custom_Log_Functions::log("api:" . $api . "###ERROR:" . $response['body']);
            }

            Custom_Log_Functions::log("called api:" . $api);
            return $response;
        } catch (Exception $e) {
            Custom_Log_Functions::log("called api:" . $api . "#response:error");
            return array('error' => true);
        }
    }

    /**
     * Used to get Auropay payment form link
     * 
     * @param array $params parameters
     * 
     * @return array
     */
    public static function apGetPaymentLink($params)
    {
        //api call to get the transaction link
        $api = "api/paymentlinks";

        try {
            $response = self::apApiKeyRequest($api, 'POST', $params);

            $body_data = json_decode($response['body'], true);
            if ($response['response']['code'] == 200 || $response['response']['code'] == 201 || $response['response']['code'] == 204) {
                return $body_data;
            } else {
                return array('status_code' => $response['response']['code'], 'message' => $body_data['message']);
            }
        } catch (Exception $e) {
            return 'There is a problem with your transaction (Error Code 20001). Please try later.';
        }
    }

    /**
     * Used to get Auropay transaction status
     * 
     * @param string $transaction_id transaction id
     * @param string $order_id       order id
     * 
     * @return string
     */
    public static function apGetPaymentStatus($transaction_id, $order_id)
    {
        update_post_meta($order_id, '_ap_transaction_id', $transaction_id);

        //Make the API call to get transaction status
        $api = "api/payments/" . $transaction_id;

        try {
            $response = self::apApiKeyRequest($api, 'GET');

            Custom_Log_Functions::log("api:" . $api . "###RESPONSE:" . print_r($response, true));
            if ($response['response']['code'] == 200 || $response['response']['code'] == 201 || $response['response']['code'] == 204) {

                $response = json_decode($response['body'], true);

                Custom_Log_Functions::log("_ap_transaction_card_type:" . $response['tenderInfo']['cardType']);
                Custom_Log_Functions::log("_ap_transaction_auth_code:" . $response['transactionResult']['processorAuthCode']);

                update_post_meta($order_id, '_ap_transaction_card_type', $response['tenderInfo']['cardType']);
                update_post_meta($order_id, '_ap_transaction_auth_code', $response['transactionResult']['processorAuthCode']);
                update_post_meta($order_id, '_ap_transaction_channel_type', $response['channelType']);
                update_post_meta($order_id, '_ap_transaction_date', $response['transactionDate']);

                //set bankname for net banking
                if ($response['channelType'] == 7) {
                    update_post_meta($order_id, '_ap_transaction_card_type', $response['tenderInfo']['bankName']);
                }

                //set upiid for UPI
                if ($response['channelType'] == 6) {
                    update_post_meta($order_id, '_ap_transaction_card_type', $response['tenderInfo']['upiId']);
                }

                //set upiid for wallet
                if ($response['channelType'] == 8) {
                    update_post_meta($order_id, '_ap_transaction_card_type', $response['tenderInfo']['walletProvider']);
                }

                if ($response['transactionStatus'] == 2) {
                    return "Success";
                } else {
                    return "Fail";
                }
            } else {
                return "Fail";
            }
        } catch (Exception $e) {
            return "Fail";
        }
    }

    /**
     * Used to sync order refund
     * 
     * @param string $params   Parameters
     * @param string $order_id order id
     * 
     * @return bool
     */
    public static function apProcessRefund($params, $order_id)
    {
        $transaction_id = get_post_meta($order_id, '_ap_transaction_id', true);
        $refund_amount = get_post_meta($order_id, '_refund_amount', true);
        $order_amount = get_post_meta($order_id, '_amount', true);

        if (!empty($refund_amount)) {
            $void_amount = $refund_amount + $params['Amount'];
        } else {
            $void_amount = number_format($params['Amount'], 2, '.', '');
        }
        $void_amount = number_format($void_amount, 2, '.', '');

        Custom_Log_Functions::log("refund_amt" . $void_amount);
        Custom_Log_Functions::log("transaction_id" . $transaction_id);

        $params['OrderId'] = $transaction_id;
        $api = "api/refunds";

        try {
            $response = self::apApiKeyRequest($api, 'POST', $params);
            if ($response['response']['code'] == 200 || $response['response']['code'] == 201 || $response['response']['code'] == 204) {
                $response = json_decode($response['body'], true);
                if ($response['transactionStatus'] == 2) {
                    update_post_meta($order_id, '_refund_amount', $void_amount);
                    update_post_meta($order_id, '_refund_reason', $params['Remarks']);
                    $refund_amount = get_post_meta($order_id, '_refund_amount', true);
                    if ($refund_amount == $order_amount) {
                        update_post_meta($order_id, '_auropay_order_status', 'refund');
                    }
                    return 1;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } catch (Exception $e) {
            return new WP_Error('wc-order', __('System unable to Refund. Contact Auropay Support at support@auropay.net', AUROPAY_PLUGIN_NAME));
        }
        return 0;
    }

    /**
     * Used to get Auropay transaction order status
     * 
     * @param int $referenceNo reference number of order
     * 
     * @return bool
     */
    public static function apGetPaymentOrderStatusByReference($referenceNo)
    {
        $api = "api/payments/refno/" . $referenceNo;

        try {
            $response = self::apApiKeyRequest($api, 'GET');
            if ($response['response']['code'] == 200 || $response['response']['code'] == 201 || $response['response']['code'] == 204) {
                $response = json_decode($response['body'], true);
                return $response['transactionStatus'];
            } else {
                return -1;
            }
        } catch (Exception $e) {
            return -1;
        }
    }
}
