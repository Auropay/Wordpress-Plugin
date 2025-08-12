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

/**
 * Communicates with AuroPay payment API
 *
 * @category Payment
 * @package  AuroPay_Gateway_For_Wordpress
 * @author   Akshita Minocha <akshita.minocha@aurionpro.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://auropay.net/
 */
if ( !class_exists( 'AUROPAY_Payment_Api' ) ) {
	class AUROPAY_Payment_Api {
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
		public static function auropay_validate_api_key( $referenceNo, $accessKey, $secretKey, $apiUrl ) {
			$api = "api/payments/refno/" . $referenceNo;
			$headers = array( 'x-version' => '1.0', 'x-access-key' => $accessKey, 'x-secret-key' => $secretKey, 'content-type' => 'application/json' );
			$endpoint = $apiUrl . $api;

			try {
				$response = wp_remote_post(
					$endpoint,
					[
						'method' => 'GET',
						'headers' => $headers,
						'timeout' => 50,
					]
				);

				if ( 200 != $response['response']['code'] && 201 != $response['response']['code'] && 204 != $response['response']['code'] ) {
					AUROPAY_Custom_Log::log( 'api:' . $api . '###ERROR:' . $response['body'] );
				}

				return $response['response']['code'];
			} catch ( Exception $e ) {
				AUROPAY_Custom_Log::log( "called api:" . $api . "#response:error" );
				return array( 'error' => true );
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
		public static function auropay_api_key_request( $api, $orderId, $method = 'POST', $params = array() ) {
			AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_calling_api ' . $api );

			$headers = array( 'x-version' => '1.0', 'x-access-key' => AUROPAY_ACCESSKEY, 'x-secret-key' => AUROPAY_SECRETKEY, 'content-type' => 'application/json' );

			$endpoint = AUROPAY_APIURL . $api;

			try {
				if ( 'POST' == $method ) {
					$response = wp_remote_post(
						$endpoint,
						[
							'method' => $method,
							'headers' => $headers,
							'body' => wp_json_encode( $params ),
							'timeout' => 50,
						]
					);
				} else {
					$response = wp_remote_post(
						$endpoint,
						[
							'method' => $method,
							'headers' => $headers,
							'timeout' => 50,
						]
					);
				}

				if ( 200 != $response['response']['code'] && 201 != $response['response']['code'] && 204 != $response['response']['code'] ) {
					AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_api_params ' . wp_json_encode( $params ) );
					AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_api_error ' . wp_json_encode( $response['body'] ) );
				}
				AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_called_api ' . $api );
				return $response;
			} catch ( Exception $e ) {
				AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $orderId . '_called_api ' . $api . '_response_error ' );
				return array( 'error' => true );
			}
		}

		/**
		 * Used to get AuroPay payment form link
		 *
		 * @param array $params parameters
		 *
		 * @return array
		 */
		public static function auropay_get_payment_link( $params ) {
			//api call to get the transaction link
			$api = "api/paymentlinks";
			$order_id = isset( $params['invoiceNumber'] ) ? $params['invoiceNumber'] : 0;

			try {
				$response = self::auropay_api_key_request( $api, $order_id, 'POST', $params );

				$body_data = json_decode( $response['body'], true );
				if ( 200 == $response['response']['code'] || 201 == $response['response']['code'] || 204 == $response['response']['code'] ) {
					return $body_data;
				} else {
					return array( 'status_code' => $response['response']['code'], 'message' => $body_data['message'] );
				}
			} catch ( Exception $e ) {
				return 'There is a problem with your transaction (Error Code 20001). Please try later.';
			}
		}

		/**
		 * Used to get AuroPay transaction status
		 *
		 * @param string $transaction_id transaction id
		 * @param string $order_id       order id
		 *
		 * @return string
		 */
		public static function auropay_get_payment_status( $transaction_id, $order_id ) {
			define( 'AUROPAY_TRANSACTION_CARD_TYPE', '_auropay_transaction_card_type' );
			update_post_meta( $order_id, '_auropay_transaction_id', $transaction_id );

			//Make the API call to get transaction status
			$api = "api/payments/" . $transaction_id;

			try {
				$response = self::auropay_api_key_request( $api, $order_id, 'GET', array() );
				$arpStatusArr = self::auropay_status_mapping();

				if ( 200 == $response['response']['code'] || 201 == $response['response']['code'] || 204 == $response['response']['code'] ) {

					$response = json_decode( $response['body'], true );

					update_post_meta( $order_id, AUROPAY_TRANSACTION_CARD_TYPE, $response['tenderInfo']['cardType'] );
					update_post_meta( $order_id, '_auropay_transaction_auth_code', $response['transactionResult']['processorAuthCode'] );
					update_post_meta( $order_id, '_auropay_transaction_channel_type', $response['channelType'] );
					update_post_meta( $order_id, '_auropay_transaction_date', $response['transactionDate'] );
					update_post_meta( $order_id, '_auropay_order_status', $arpStatusArr[$response['transactionStatus']] );
					//set bankname for net banking
					if ( 7 == $response['channelType'] ) {
						update_post_meta( $order_id, AUROPAY_TRANSACTION_CARD_TYPE, $response['tenderInfo']['bankName'] );
					}

					//set upiid for UPI
					if ( 6 == $response['channelType'] ) {
						update_post_meta( $order_id, AUROPAY_TRANSACTION_CARD_TYPE, $response['tenderInfo']['upiId'] );
					}

					//set upiid for wallet
					if ( 8 == $response['channelType'] ) {
						update_post_meta( $order_id, AUROPAY_TRANSACTION_CARD_TYPE, $response['tenderInfo']['walletProvider'] );
					}

					if ( $response['transactionStatus'] ) {
						return $arpStatusArr[$response['transactionStatus']];
					}
				} else {
					return "Fail";
				}
			} catch ( Exception $e ) {
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
		public static function auropay_process_refund( $params, $order_id ) {
			$transaction_id = get_post_meta( $order_id, '_auropay_transaction_id', true );
			$refund_amount = get_post_meta( $order_id, '_refund_amount', true );
			$order_amount = get_post_meta( $order_id, '_amount', true );

			if ( !empty( $refund_amount ) ) {
				$void_amount = $refund_amount + $params['Amount'];
			} else {
				$void_amount = number_format( $params['Amount'], 2, '.', '' );
			}
			$void_amount = number_format( $void_amount, 2, '.', '' );

			$params['OrderId'] = $transaction_id;
			$api = "api/refunds";

			try {
				$response = self::auropay_api_key_request( $api, $order_id, 'POST', $params );
				$response_code = $response['response']['code'];
				if ( in_array( $response_code, array( 200, 201, 204 ) ) ) {
					$response = json_decode( $response['body'], true );
					if ( 2 == $response['transactionStatus'] || 18 == $response['transactionStatus'] ) {
						update_post_meta( $order_id, '_refund_amount', $void_amount );
						update_post_meta( $order_id, '_refund_reason', $params['Remarks'] );
						if ( $void_amount == $order_amount ) {
							update_post_meta( $order_id, '_auropay_order_status', 'Refunded' );
						}
						return true;
					}
				}
				return false;
			} catch ( Exception $e ) {
				AUROPAY_Custom_Log::log( AUROPAY_ORDER_ID . $order_id . 'refund_error ' . $e );
				return false;
			}
		}

		/**
		 * Used to get AuroPay transaction order status
		 *
		 * @param int $referenceNo reference number of order
		 *
		 * @return bool
		 */
		public static function auropay_get_payment_order_status_by_reference( $referenceNo, $orderId ) {
			$api = "api/payments/refno/" . $referenceNo;

			try {
				$response = self::auropay_api_key_request( $api, $orderId, 'GET', array() );
				if ( 200 == $response['response']['code'] || 201 == $response['response']['code'] || 204 == $response['response']['code'] ) {
					$response = json_decode( $response['body'], true );
					return $response;
				} else {
					return -1;
				}
			} catch ( Exception $e ) {
				return -1;
			}
		}

		public static function auropay_status_mapping() {
			return [
				0 => 'In Process',
				1 => 'In Process',
				2 => 'Authorized',
				4 => 'Cancelled',
				5 => 'Failed',
				9 => 'RefundAttempted',
				10 => 'Refunded',
				16 => 'Success',
				18 => 'Hold',
				19 => 'RefundFailed',
				20 => 'PartialRefundAttempted',
				21 => 'PartiallyRefunded',
				22 => 'UserCancelled',
				23 => 'Expired',
				24 => 'SettlementFailed',
				25 => 'Approved',
			];
		}
	}
}
