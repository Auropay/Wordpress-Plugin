(function ($) {
	// Function to handle refund amount
	function auropay_refund_amount() {
		const message = auropayRefundData.confirm_text;

		if (confirm(message)) {
			const refund_amount = $("#refund_amount").val();
			const order_id = auropayRefundData.order_id;
			const refund_reason = $("#refund_reason").val();

			if ($.trim(refund_amount) === "") {
				$("#refundError").html(auropayRefundData.error_empty);
				return false;
			} else if (parseFloat(refund_amount) < 1) {
				$("#refundError").html(auropayRefundData.error_invalid);
				return false;
			} else {
				$("#refundError").html("");
				$.ajax({
					type: "POST",
					url: auropayRefundData.ajax_url,
					dataType: "json",
					data: {
						action: "ajax_refund_order",
						refund_action: "refund_order",
						refundAmount: refund_amount,
						order_id: order_id,
						reason: refund_reason,
					},
					cache: false,
					success: function (result) {
						if (result === false) {
							alert(auropayRefundData.error_system);
						} else {
							location.reload();
						}
					},
					error: function (error) {
						console.log(error);
					},
				});
			}
		}
	}

	// Function to handle refund button text update
	function update_refund_button_text() {
		const refundValue = $("#refund_amount").val();
		$("#btnRefundAmount").html(
			"Refund ₹ " + refundValue + " via AuroPay Gateway"
		);
	}

	// Functions to show and hide refund sections
	function auropay_refund_btn() {
		$(".after-refund").show();
		$(".before-refund").hide();
	}

	function auropay_cancel_btn() {
		$(".after-refund").hide();
		$(".before-refund").show();
	}

	// Document ready event
	$(document).ready(function () {
		// Hide the "after-refund" section initially
		$(".after-refund").hide();

		// Bind the refund function to a button click
		$("#btnRefundAmount").on("click", auropay_refund_amount);

		// Bind the focusout event to update the refund button text
		$("#refund_amount").on("focusout", update_refund_button_text);

		// Expose global functions for refund button and cancel button
		window.auropay_refund_btn = auropay_refund_btn;
		window.auropay_cancel_btn = auropay_cancel_btn;
	});
})(jQuery);
