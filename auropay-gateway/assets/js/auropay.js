var auropay_expiry_min = AUROPAY.EXPIRY;
var auropay_timeout_in_miliseconds = auropay_expiry_min * 60000;
var auropay_timeout_id;
var auropay_page_id = 0;

function auropay_hide_iframe() {
	jQuery("#auropay_iframe").hide();
	jQuery("#err-msg").hide();
	jQuery("#close_iframe").hide();
	jQuery("#c_step1").hide();
	jQuery(".submitBtn").show();
}

function auropay_start_timer() {
	auropay_timeout_id = window.setTimeout(
		auropay_do_inactive,
		auropay_timeout_in_miliseconds
	);
}

function auropay_do_inactive() {
	alert("Found no activity, so reloading checkout page again");
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
	window.clearTimeout(auropay_timeout_id);
	auropay_start_timer();
}

function auropay_pay_now() {
	auropay_page_id = jQuery("#auropay_page_id").val();
	jQuery(".form-div").css("display", "block");
	jQuery("#auropay_place_order").css("display", "none");
	jQuery("#c_step1").css("display", "none");
}

function auropay_mt_rand(min, max) {
	return Math.floor(Math.random() * (max - min)) + min;
}

function auropay_contact_form() {
	var orderId = auropay_mt_rand(0, Math.pow(2, 31));
	var reg = /^[\w]{1,}[\w.+\-]{0,}@[\w\-]{2,}(\.[\w\-]{2,})?\.[a-zA-Z]{2,4}$/;
	var phone_number_regex = /^[0-9-+]+$/;
	var amount_regex = /^(?!0$)([1-9]\d*)(\.\d{1,2})?$/;
	var name_regex = /^[a-zA-Z\s]{1,70}$/;
	var firstName = jQuery("#firstname").val();
	var lastName = jQuery("#lastname").val();
	var phoneNumber = jQuery("#phone").val();
	var email = jQuery("#email").val();
	var amount = jQuery("#amount").val();
	var delimg =
		"<img src='" +
		AUROPAY.AUROPAY_PLUGIN_URL +
		"/assets/images/close.png' width='20' height='20' onClick='auropay_hide_iframe()' style='cursor:pointer' >";

	jQuery("#errMsgFirstname").html("");
	jQuery("#errMsgLastname").html("");
	jQuery("#errMsgPhone").html("");
	jQuery("#errMsgEmail").html("");
	jQuery("#errMsgAmount").html("");
	if (firstName.trim() == "") {
		jQuery("#errMsgFirstname").html("Please enter first name.");
		jQuery("#firstname").focus();
		return false;
	} else if (firstName.trim() != "" && !name_regex.test(firstName)) {
		jQuery("#errMsgFirstname").html("Please enter valid first name.");
		jQuery("#firstname").focus();
		return false;
	} else if (lastName.trim() == "") {
		jQuery("#errMsgLastname").html("Please enter last name.");
		jQuery("#lastname").focus();
		return false;
	} else if (lastName.trim() != "" && !name_regex.test(lastName)) {
		jQuery("#errMsgLastname").html("Please enter valid last name.");
		jQuery("#lastname").focus();
		return false;
	} else if (phoneNumber.trim() == "") {
		jQuery("#errMsgPhone").html("Please enter your phone number.");
		jQuery("#phone").focus();
		return false;
	} else if (
		phoneNumber.trim() != "" &&
		!phone_number_regex.test(phoneNumber)
	) {
		jQuery("#errMsgPhone").html("Please enter valid phone number.");
		jQuery("#phone").focus();
		return false;
	} else if (phoneNumber.trim() != "" && phoneNumber.length != 10) {
		jQuery("#errMsgPhone").html(
			"Phone number is not valid. Enter 10 digit number."
		);
		jQuery("#phone").focus();
		return false;
	} else if (email.trim() == "") {
		jQuery("#errMsgEmail").html("Please enter your email.");
		jQuery("#email").focus();
		return false;
	} else if (email.trim() != "" && !reg.test(email)) {
		jQuery("#errMsgEmail").html("Please enter valid email.");
		jQuery("#email").focus();
		return false;
	} else if (email.length > 77) {
		jQuery("#errMsgEmail").html(
			"Email address cannot be longer than 77 characters."
		);
		jQuery("#email").focus();
		return false;
	} else if (amount.trim() == "") {
		jQuery("#errMsgAmount").html("Please enter amount.");
		jQuery("#amount").focus();
		return false;
	} else if (!amount_regex.test(amount)) {
		jQuery("#errMsgAmount").html("Please enter a valid amount.");
		jQuery("#amount").focus();
		return false;
	} else if (amount.length > 15) {
		jQuery("#errMsgAmount").html(
			"Amount is too long. Please enter a shorter amount."
		);
		jQuery("#amount").focus();
		return false;
	} else {
		jQuery("#c_step1").css("display", "block");
		var amountEncoded = btoa(amount)
		// AJAX call
		jQuery.ajax({
			type: "POST",
			url: AUROPAY.AJAX_URL,
			dataType: "json",
			cache: false,
			data: {
				action: "auropay_orders",
				order_id: orderId,
				order_action: "place_order",
				email: email,
				phoneNumber: phoneNumber,
				amount: amountEncoded,
				firstname: firstName,
				lastname: lastName,
				auropay_page_id: auropay_page_id,
				"auropay-checkout-pay-nonce": AUROPAY.NONCE,
			},
			success: function (result) {
				jQuery("#close_iframe").show();
				if (result.status_code != 400) {
					jQuery(".submitBtn").hide();
					jQuery("#c_step1").hide();
					if (jQuery("#auropay_iframe").length == 0) {
						jQuery(".pay-now-div").append(
							'<div class="del-img" id="close_iframe" style="display:block">' +
								delimg +
								"</div>"
						);
						jQuery(".pay-now-div").append(
							'<iframe src="' +
								result.paymentLink +
								'" name="auropay_iframe" id="auropay_iframe" scrolling="yes" frameborder=0 class="iframe-cs" style="display:block;"></iframe>'
						);
					} else {
						jQuery("#auropay_iframe").remove();
						jQuery(".pay-now-div").append(
							'<iframe src="' +
								result.paymentLink +
								'" name="auropay_iframe" id="auropay_iframe" scrolling="yes" frameborder=0 class="iframe-cs" style="display:block;"></iframe>'
						);
					}
				} else {
					jQuery("#close_iframe").hide();
					jQuery("#c_step1").css("display", "none");
					jQuery("#errMsgAmount").html(
						'<div id="err-msg">Error when loading the payment form, please contact support team!</div>'
					);
				}
				auropay_setup_timers();
			},
			error: function (error) {
				console.log("in error");
				console.log(error);
			},
		});
	}
}
