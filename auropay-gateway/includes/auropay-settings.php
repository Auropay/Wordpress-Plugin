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

define( 'AUROPAY_SETTING_PAGE', 'auropay-settings' );
define( 'AUROPAY_SETTING_SECTION', 'auropay_setting_section' );

/**
 * Create Settings Menu
 *
 * @return void
 */
if ( !function_exists( 'auropay_settings' ) ) {
	function auropay_settings() {
		add_menu_page(
			__( 'Auropay Settingss', 'auropay-gateway' ),
			__( 'Auropay Settings', 'auropay-gateway' ),
			'manage_options',
			'auropay-settings',
			'auropay_settings_callback',
			AUROPAY_PLUGIN_URL . '/assets/images/icons/auropay.png',
			null
		);
	}
}

add_action( 'admin_menu', 'auropay_settings' );

/**
 * Settings Template Page
 *
 * @return void
 */
if ( !function_exists( 'auropay_settings_callback' ) ) {
	function auropay_settings_callback() {
		?>
<div class="wrap">
	<form action="options.php" method="post">
		<?php
// security field
		settings_fields( AUROPAY_SETTING_PAGE );

		// output settings section here
		do_settings_sections( AUROPAY_SETTING_PAGE );

		// save settings button
		submit_button( 'Save Settings' );
		?>
	</form>
</div>
<?php
}
}

/**
 * Settings initiate
 *
 * @return void
 */
if ( !function_exists( 'auropay_settings_init' ) ) {
	function auropay_settings_init() {
		// Setup settings section
		add_settings_section(
			AUROPAY_SETTING_SECTION,
			'Auropay Settings',
			'',
			AUROPAY_SETTING_PAGE
		);
		auropay_register_general_settings();
		auropay_register_api_settings();
		auropay_register_additional_settings();
	}
}
add_action( 'admin_init', 'auropay_settings_init' );

/**
 * Register and initialize general settings.
 *
 * @return void
 */
function auropay_register_general_settings() {

	// Register title field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_title',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Add title fields
	add_settings_field(
		'auropay_title',
		__( 'Title', 'auropay-gateway' ),
		'auropay_title_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Register description field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_description',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'default' => '',
		)
	);

	// Add description fields
	add_settings_field(
		'auropay_description',
		__( 'Description', 'auropay-gateway' ),
		'auropay_description_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Add button text fields
	add_settings_field(
		'auropay_button_text',
		__( 'Button Text', 'auropay-gateway' ),
		'auropay_button_text_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Register button text field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_button_text',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Register payment mode field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_payment_mode',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => 5,
		)
	);

	// Add payment mode field
	add_settings_field(
		'auropay_payment_mode',
		__( 'Payment Mode', 'auropay-gateway' ),
		'auropay_payment_mode_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);
}

/**
 * Register and initialize API settings.
 *
 * @return void
 */
function auropay_register_api_settings() {
	// Register api url field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_api_url',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Add api url fields
	add_settings_field(
		'auropay_api_url',
		__( 'API URL', 'auropay-gateway' ),
		'auropay_api_url_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Register access key field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_access_key',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Add access key fields
	add_settings_field(
		'auropay_access_key',
		__( 'Access Key', 'auropay-gateway' ),
		'auropay_access_key_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Register secret key field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_secret_key',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Add secret key fields
	add_settings_field(
		'auropay_secret_key',
		__( 'Secret Key', 'auropay-gateway' ),
		'auropay_secret_key_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Register test api url field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_test_api_url',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Add test api url fields
	add_settings_field(
		'auropay_test_api_url',
		__( 'Test API URL', 'auropay-gateway' ),
		'auropay_test_api_url_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Register test access key field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_test_access_key',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Add test access key fields
	add_settings_field(
		'auropay_test_access_key',
		__( 'Test Access Key', 'auropay-gateway' ),
		'auropay_test_access_key_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Register test secret key field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_test_secret_key',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Add test secret key fields
	add_settings_field(
		'auropay_test_secret_key',
		__( 'Test Secret Key', 'auropay-gateway' ),
		'auropay_test_secret_key_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);
}

/**
 * Register and initialize API settings.
 *
 * @return void
 */
function auropay_register_additional_settings() {
	// Register Log field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_logging',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Add Log field
	add_settings_field(
		'auropay_logging',
		__( 'Logging', 'auropay-gateway' ),
		'auropay_logging_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Register payment summary field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_payment',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		)
	);

	// Add payment summary field
	add_settings_field(
		'auropay_payment',
		__( 'Payment Summary', 'auropay-gateway' ),
		'auropay_payment_summary_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);

	// Register expiry field
	register_setting(
		AUROPAY_SETTING_PAGE,
		'auropay_expiry',
		array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => 5,
		)
	);

	// Add expiry field
	add_settings_field(
		'auropay_expiry',
		__( 'Checkout Timeout (Min)', 'auropay-gateway' ),
		'auropay_expiry_callback',
		AUROPAY_SETTING_PAGE,
		AUROPAY_SETTING_SECTION
	);
}

/**
 * Title tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_title_callback' ) ) {
	function auropay_title_callback() {
		$auropay_title = get_option( 'auropay_title' );
		?>
<input type="text" name="auropay_title" class="regular-text"
	value="<?php echo isset( $auropay_title ) ? esc_attr( $auropay_title ) : ''; ?>" />
<?php
}
}

/**
 * Description tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_description_callback' ) ) {
	function auropay_description_callback() {
		$auropay_description = get_option( 'auropay_description' );
		?>
<textarea name="auropay_description" class="large-text"
	rows="2"><?php echo isset( $auropay_description ) ? esc_textarea( $auropay_description ) : ''; ?></textarea>
<?php
}
}

/**
 * Button text tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_button_text_callback' ) ) {
	function auropay_button_text_callback() {
		$auropay_button_text = get_option( 'auropay_button_text' );
		?>
<input type="text" name="auropay_button_text" class="regular-text"
	value="<?php echo isset( $auropay_button_text ) ? esc_attr( $auropay_button_text ) : ''; ?>" />
<br /><br /><span>Example: Buy Now, Pay Now etc..</span>
<?php
}
}

/**
 * Payment mode tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_payment_mode_callback' ) ) {
	function auropay_payment_mode_callback() {
		$auropay_payment_mode = get_option( 'auropay_payment_mode' );
		?>
<input type="checkbox" name="auropay_payment_mode" class="regular-text" value="test_mode"
	<?php checked( 'test_mode', $auropay_payment_mode );?> /> Enable Test Mode
<br /><br /><span>Place the payment gateway in test mode using test credentials..</span>

<?php
}
}

/**
 * Api url tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_api_url_callback' ) ) {
	function auropay_api_url_callback() {
		$auropay_api_url = get_option( 'auropay_api_url' );
		?>
<input type="text" name="auropay_api_url" class="regular-text"
	value="<?php echo isset( $auropay_api_url ) ? esc_attr( $auropay_api_url ) : ''; ?>" />

<?php
}
}

/**
 * Access key tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_access_key_callback' ) ) {
	function auropay_access_key_callback() {
		$auropay_access_key = get_option( 'auropay_access_key' );
		?>
<input type="password" name="auropay_access_key" class="regular-text"
	value="<?php echo isset( $auropay_access_key ) ? esc_attr( $auropay_access_key ) : ''; ?>" />

<?php
}
}

/**
 * Secret key tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_secret_key_callback' ) ) {
	function auropay_secret_key_callback() {
		$auropay_secret_key = get_option( 'auropay_secret_key' );
		?>
<input type="password" name="auropay_secret_key" class="regular-text"
	value="<?php echo isset( $auropay_secret_key ) ? esc_attr( $auropay_secret_key ) : ''; ?>" />

<?php
}
}

/**
 * Test api url tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_test_api_url_callback' ) ) {
	function auropay_test_api_url_callback() {
		$auropay_test_api_url = get_option( 'auropay_test_api_url' );
		?>
<input type="text" name="auropay_test_api_url" class="regular-text"
	value="<?php echo isset( $auropay_test_api_url ) ? esc_attr( $auropay_test_api_url ) : ''; ?>" />

<?php
}
}

/**
 * Test access key tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_test_access_key_callback' ) ) {
	function auropay_test_access_key_callback() {
		$auropay_test_access_key = get_option( 'auropay_test_access_key' );
		?>
<input type="password" name="auropay_test_access_key" class="regular-text"
	value="<?php echo isset( $auropay_test_access_key ) ? esc_attr( $auropay_test_access_key ) : ''; ?>" />

<?php
}
}

/**
 * Test secret key tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_test_secret_key_callback' ) ) {
	function auropay_test_secret_key_callback() {
		$auropay_test_secret_key = get_option( 'auropay_test_secret_key' );
		?>
<input type="password" name="auropay_test_secret_key" class="regular-text"
	value="<?php echo isset( $auropay_test_secret_key ) ? esc_attr( $auropay_test_secret_key ) : ''; ?>" />

<?php
}
}

/**
 * Logging
 *
 * @return void
 */
if ( !function_exists( 'auropay_logging_callback' ) ) {
	function auropay_logging_callback() {
		$auropay_logging = get_option( 'auropay_logging' );
		?>
<input type="checkbox" name="auropay_logging" class="regular-text" value="logging"
	<?php checked( 'logging', $auropay_logging );?> /> Log debug messages
<br /><br /><span>Save debug messages into log.</span>

<?php
}
}

/**
 * Payment summary tempalte
 *
 * @return void
 */
if ( !function_exists( 'auropay_payment_summary_callback' ) ) {
	function auropay_payment_summary_callback() {
		$auropay_payment = get_option( 'auropay_payment' );
		?>
<input type="checkbox" name="auropay_payment" class="regular-text" value="payment"
	<?php checked( 'payment', $auropay_payment );?> /> Add payments overview page
<br /><br /><span>View your Payment Transactions done via Auropay Gateway.</span>

<?php
}
}

/**
 * Checkout expiry template
 *
 * @return void
 */
if ( !function_exists( 'auropay_expiry_callback' ) ) {
	function auropay_expiry_callback() {
		$auropay_expiry = get_option( 'auropay_expiry' );
		$options = array(
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6',
			'7' => '7',
			'8' => '8',
			'9' => '9',
			'10' => '10',
		)
		?>
<select name="auropay_expiry" id="auropay_expiry" class="regular-text">
	<?php
foreach ( $options as $value ) {
			?>
	<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $auropay_expiry )?>><?php echo esc_html( $value ); ?> Minutes
	</option>;
	<?php
}
		?>
</select>
<br /><br /><span>Define Expiry Time for Payment Form. Checkout form will be reloaded for customer if expiry time is
	reached.</span>

<?php
}
}
