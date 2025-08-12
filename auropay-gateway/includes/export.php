<?php

use Fpdf\Fpdf;

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

require_once AUROPAY_PLUGIN_PATH . '/vendor/autoload.php';

/**
 * Export data in csv and pdf format
 *
 * @category Payment
 * @package  AuroPay_Gateway_For_Wordpress
 * @author   Akshita Minocha <akshita.minocha@aurionpro.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://auropay.net/
 */
if ( !class_exists( 'AUROPAY_PDF' ) ) {
	class AUROPAY_PDF extends Fpdf {
		/**
		 * Page Header
		 *
		 * @return void
		 */
		public function Header() {
			$this->setHeaders();

			if ( $this->PageNo() == 1 ) {
				$this->setReportNameAndLogo();
			}

			$this->setTableHeader();
		}

		private function setHeaders() {
			$date = gmdate( "d-m-y_H:i:s" );
			$filename = 'Transaction_List_AuroPay_Payments_' . $date . '.csv';
			header( 'Content-type: text/csv' );
			header( "Content-Disposition: attachment; filename={$filename}" );
		}

		private function setReportNameAndLogo() {
			$header_font_size = 9;
			$this->SetFont( 'Arial', 'B', $header_font_size );
			$this->SetTextColor( 34, 43, 154 );

			$this->Cell( 0, 10, 'Transaction List AuroPay Payments', 0, 0, 'L' );

			$pageWidth = $this->GetPageWidth();
			$imageWidth = 90;
			$xCoordinate = $pageWidth - ( 2 * 10 ) - $imageWidth;

			$this->Image( AUROPAY_PLUGIN_URL . '/assets/images/logo.png', $xCoordinate, 20, $imageWidth, 0 );
			$this->Ln( 20 );
		}

		private function setTableHeader() {
			$header = [
				'Order',
				'Date & Time (IST)',
				'Status',
				'Sale',
				'Refund',
				'Type',
				AUROPAY_PAYMENT_ID,
				'Method',
				AUROPAY_PAYMENT_DETAIL,
				'Auth Code',
			];

			$this->SetFont( 'Arial', 'B', 9 );
			$this->Ln();

			$this->SetFillColor( 244, 209, 82 );
			$this->SetTextColor( 34, 43, 154 );
			$this->SetDrawColor( 133, 160, 196 );
			$this->SetLineWidth( .3 );
			$this->SetFont( 'Arial', 'B', 9 );

			$individual_header_width = ( 612 / count( $header ) ) * 1.7;
			$w = $this->calculateColumnWidths( $header, $individual_header_width );

			$this->printTableHeader( $header, $w );
		}

		private function calculateColumnWidths( $header, $base_width ) {
			$w = [];
			foreach ( $header as $col ) {
				switch ( $col ) {
					case 'Order':
					case 'Date':
					case 'Status':
					case 'Method':
					case AUROPAY_PAYMENT_DETAIL:
						$w[] = $base_width * 1.2;
						break;
					case 'Type':
						$w[] = $base_width / 2;
						break;
					case AUROPAY_PAYMENT_ID:
						$w[] = $base_width * 1.8;
						break;
					default:
						$w[] = $base_width;
						break;
				}
			}
			return $w;
		}

		private function printTableHeader( $header, $widths ) {
			$height = 30;
			foreach ( $header as $i => $col ) {
				$this->Cell( $widths[$i], $height, $col, 1, 0, 'C', true );
			}
			$this->Ln();
		}

		/**
		 * Page Footer
		 *
		 * @return void
		 */
		public function Footer() {
			$footer_font_size = 9;
			// Position at 1.5 cm from bottom
			$this->SetY( -25 );
			$this->SetFont( 'Arial', '', $footer_font_size );
			$this->Cell( 0, 14, 'Transaction List AuroPay Payments', 0, 0, 'L' );
			$this->SetX( $this->lMargin );
			$this->Cell( 0, 14, 'Page ' . $this->PageNo(), 0, 0, 'C' );
			$this->SetX( $this->lMargin );
			$this->Cell( 0, 14, 'Powered by AuroPay ', 0, 0, 'R' );
		}
	}
}
/**
 * This export the data in file
 *
 * @param $export_type  type of file
 * @param $total_result data to export
 *
 * @return void
 */
function auropay_export_data( $export_type, $total_result ) {
	$header = auropay_get_header();

	$row_values = auropay_row_values( $total_result );

	if ( 'csv' == $export_type ) {
		auropay_export_csv( $header, $row_values );
	} else {
		auropay_export_pdf( $header, $row_values );
	}
}

function auropay_get_header() {
	return array(
		'Order',
		'Date & Time (IST)',
		'Status',
		'Sale(₹)',
		'Refund(₹)',
		'Type',
		AUROPAY_PAYMENT_ID,
		'Method',
		AUROPAY_PAYMENT_DETAIL,
		'Auth Code',
	);
}

function auropay_row_values( $total_result ) {
	$row_values = [];
	foreach ( $total_result as $order_id ) {
		$type_array = array( '3' => 'Credit Card', '4' => 'Debit Card', '6' => 'UPI', '7' => 'NetBanking', '8' => 'Wallets' );
		$payment_method = get_post_meta( $order_id, '_auropay_transaction_channel_type', true );
		$payment_method = $type_array[$payment_method] ?? "";

		$auth_code = get_post_meta( $order_id, '_auropay_transaction_auth_code', true );
		$transaction_date = get_post_meta( $order_id, '_auropay_transaction_date', true );
		$card_type = get_post_meta( $order_id, '_auropay_transaction_card_type', true );
		$paymentId = get_post_meta( $order_id, '_auropay_transaction_id', true ) ?: '-';

		$order_status = get_post_meta( $order_id, '_auropay_order_status', true );
		$order_amount = number_format( (float) get_post_meta( $order_id, '_amount', true ), 2, '.', '' );
		$refund_amount = number_format( (float) get_post_meta( $order_id, '_refund_amount', true ), 2, '.', '' );

		$type = 'Refunded' == $order_status ? "Refund" : "Sale";

		$row_values[] = array(
			$order_id,
			gmdate( AUROPAY_DATE_FORMAT, strtotime( $transaction_date ) ),
			$order_status,
			$order_amount,
			$refund_amount,
			$type,
			$paymentId,
			$payment_method,
			$card_type,
			$auth_code,
		);
	}
	return $row_values;
}

function auropay_export_csv( $header, $row_values ) {
	$filename = auropay_get_filename( 'csv' );
	$fh = @fopen( 'php://output', 'w' );
	fprintf( $fh, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Content-Description: File Transfer' );
	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Expires: 0' );
	header( 'Pragma: public' );

	fputcsv( $fh, $header );

	foreach ( $row_values as $row ) {
		fputcsv( $fh, $row );
	}
	die();
}

function auropay_export_pdf( $header, $row_values ) {
	ob_end_clean();
	$pdf = new AUROPAY_PDF( 'L', 'pt', 'A3' );
	$pdf->AddFont( 'Arial', '', 'Arial.php' );
	$pdf->AddPage();

	auropay_set_pdf_table_style( $pdf );

	$widths = auropay_calculate_column_widths( $header, 612 );
	auropay_print_pdf_table_rows( $pdf, $row_values, $widths );

	$filename = auropay_get_filename( 'pdf' );
	$pdf->Output( $filename, 'D' );
	ob_end_flush();
}

function auropay_set_pdf_table_style( $pdf ) {
	$pdf->SetFillColor( 167, 191, 217 );
	$pdf->SetTextColor( 0 );
	$pdf->SetDrawColor( 133, 160, 196 );
	$pdf->SetLineWidth( .3 );
	$pdf->SetFont( 'Arial', '', 9 );
}

function auropay_calculate_column_widths( $header, $pageWidth ) {
	$base_width = ( $pageWidth / count( $header ) ) * 1.7;
	$widths = [];

	foreach ( $header as $col ) {
		switch ( $col ) {
			case 'Order':
			case 'Date':
			case 'Status':
			case 'Method':
			case AUROPAY_PAYMENT_DETAIL:
				$widths[] = $base_width * 1.2;
				break;
			case 'Type':
				$widths[] = $base_width / 2;
				break;
			case AUROPAY_PAYMENT_ID:
				$widths[] = $base_width * 1.8;
				break;
			default:
				$widths[] = $base_width;
		}
	}

	return $widths;
}

function auropay_print_pdf_table_rows( $pdf, $row_values, $widths ) {
	$fill = false;
	foreach ( $row_values as $row ) {
		$max_length = 25;
		$row[7] = mb_strimwidth( $row[7], 0, $max_length, '...' );
		$line_height = 30; // Define the line height for cells with wrapped text
		$max_lines = 3; // Maximum number of lines to display before truncating

		$start_x = $pdf->GetX();
		$max_cell_height = 30;

		// Check the height needed for wrapped text in the last column
		$wrapped_text = wordwrap( $row[8], 25, "\n", true );
		$lines = explode( "\n", $wrapped_text );
		$lines = array_slice( $lines, 0, $max_lines );
		$display_text = implode( "\n", array_slice( $lines, 0, 3 ) );
		if ( count( $lines ) > 3 ) {
			$display_text .= '...';
		}

		$cell_height = count( $lines ) * $line_height;
		if ( $cell_height > $max_cell_height ) {
			$max_cell_height = $cell_height;
		}
		foreach ( $row as $i => $cell ) {
			if ( 3 == $i || 4 == $i ) {
				$pdf->Cell( $widths[$i], $max_cell_height, chr( 0xA4 ) . $cell, 'LRB', 0, 'C', $fill );
			} else {
				if ( 8 == $i ) {
					$current_x = $pdf->GetX();
					$current_y = $pdf->GetY();
					$pdf->SetXY( $start_x + array_sum( array_slice( $widths, 0, $i ) ), $current_y );
					$pdf->MultiCell( $widths[$i], $line_height, $display_text, 'LRB', 'C', $fill );
					// Reset X position after MultiCell to avoid overlap in next iteration
					$pdf->SetXY( $current_x + $widths[$i], $current_y );
				} else {
					$pdf->Cell( $widths[$i], $max_cell_height, $cell, 'LRB', 0, 'C', $fill );
				}
			}
		}
		$pdf->Ln( $max_cell_height );
		$fill = !$fill;
	}
}

function auropay_get_filename( $type ) {
	$date = gmdate( 'd-m-Y_H:i:s', strtotime( gmdate( 'Y-m-d H:i:s' ) . ' + 5 hour + 30 minute' ) );
	return 'Transaction_List_AuroPay_Payments_' . $date . '.' . $type;
}
