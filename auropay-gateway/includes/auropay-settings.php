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

define('AP_SETTING_PAGE', 'auropay-settings');
define('AP_SETTING_SECTION', 'auropay_setting_section');

/**
 * Create Settings Menu
 * 
 * @return void
 */
function auropaySettings()
{
    add_menu_page(
        __('Auropay Settingss', AP_SETTING_PAGE),
        __('Auropay Settings', AP_SETTING_PAGE),
        'manage_options',
        'auropay-settings',
        'auropaySettingsCallback',
        AUROPAY_PLUGIN_URL . '/assets/images/icons/auropay.png',
        null
    );
}

add_action('admin_menu', 'auropaySettings');

/**
 * Settings Template Page
 * 
 * @return void
 */
function auropaySettingsCallback()
{
    ?>
    <div class="wrap">
        <form action="options.php" method="post">
            <?php
            // security field
            settings_fields(AP_SETTING_PAGE);

            // output settings section here
            do_settings_sections(AP_SETTING_PAGE);

            // save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

/**
 * Settings initiate
 * 
 * @return void
 */
function apSettingsInit()
{
    // Setup settings section
    add_settings_section(
        AP_SETTING_SECTION,
        'Auropay Settings',
        '',
        AP_SETTING_PAGE
    );

    // Register title field
    register_setting(
        AP_SETTING_PAGE,
        'ap_title',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add title fields
    add_settings_field(
        'ap_title',
        __('Title', 'auropay-gateway'),
        'apTitleCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register description field
    register_setting(
        AP_SETTING_PAGE,
        'ap_description',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => ''
        )
    );

    // Add description fields
    add_settings_field(
        'ap_description',
        __('Description', 'auropay-gateway'),
        'apDescriptionCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Add button text fields
    add_settings_field(
        'ap_button_text',
        __('Button Text', 'auropay-gateway'),
        'apButtonTextCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register button text field
    register_setting(
        AP_SETTING_PAGE,
        'ap_button_text',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Register payment mode field
    register_setting(
        AP_SETTING_PAGE,
        'ap_payment_mode',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 5
        )
    );

    // Add expiry field
    add_settings_field(
        'ap_payment_mode',
        __('Payment Mode', 'auropay-gateway'),
        'apPaymentModeCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register api url field
    register_setting(
        AP_SETTING_PAGE,
        'ap_api_url',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add api url fields
    add_settings_field(
        'ap_api_url',
        __('API URL', 'auropay-gateway'),
        'apApiUrlCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register access key field
    register_setting(
        AP_SETTING_PAGE,
        'ap_access_key',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add access key fields
    add_settings_field(
        'ap_access_key',
        __('Access Key', 'auropay-gateway'),
        'apAccessKeyCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register secret key field
    register_setting(
        AP_SETTING_PAGE,
        'ap_secret_key',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add secret key fields
    add_settings_field(
        'ap_secret_key',
        __('Secret Key', 'auropay-gateway'),
        'apSecretKeyCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register api url field
    register_setting(
        AP_SETTING_PAGE,
        'ap_test_api_url',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add api url fields
    add_settings_field(
        'ap_test_api_url',
        __('Test API URL', 'auropay-gateway'),
        'apTestApiUrlCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register access key field
    register_setting(
        AP_SETTING_PAGE,
        'ap_test_access_key',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add access key fields
    add_settings_field(
        'ap_test_access_key',
        __('Test Access Key', 'auropay-gateway'),
        'apTestAccessKeyCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register secret key field
    register_setting(
        AP_SETTING_PAGE,
        'ap_test_secret_key',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add secret key fields
    add_settings_field(
        'ap_test_secret_key',
        __('Test Secret Key', 'auropay-gateway'),
        'apTestSecretKeyCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register Log field
    register_setting(
        AP_SETTING_PAGE,
        'ap_payment',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    // Add Log field
    add_settings_field(
        'ap_payment',
        __('Payment Summary', 'auropay-gateway'),
        'apPaymentSummaryCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );

    // Register expiry field
    register_setting(
        AP_SETTING_PAGE,
        'ap_expiry',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 5
        )
    );

    // Add expiry field
    add_settings_field(
        'ap_expiry',
        __('Checkout Timeout (Min)', 'auropay-gateway'),
        'apExpiryCallback',
        AP_SETTING_PAGE,
        AP_SETTING_SECTION
    );
}
add_action('admin_init', 'apSettingsInit');

/**
 * Title tempalte
 * 
 * @return void
 */
function apTitleCallback()
{
    $ap_title = get_option('ap_title');
    ?>
    <input type="text" name="ap_title" class="regular-text" value="<?php echo isset($ap_title) ? esc_attr($ap_title) : ''; ?>" />
    <?php
}

/**
 * Description tempalte
 * 
 * @return void
 */
function apDescriptionCallback()
{
    $ap_description = get_option('ap_description');
    ?>
    <textarea name="ap_description" class="large-text" rows="2"><?php echo isset($ap_description) ? esc_textarea($ap_description) : ''; ?></textarea>
    <?php
}

/**
 * Button text tempalte
 * 
 * @return void
 */
function apButtonTextCallback()
{
    $ap_button_text = get_option('ap_button_text');
    ?>
    <input type="text" name="ap_button_text" class="regular-text" value="<?php echo isset($ap_button_text) ? esc_attr($ap_button_text) : ''; ?>" />
    <br /><br /><span>Example: Buy Now, Pay Now etc..</span>
    <?php
}

/**
 * Payment mode tempalte
 * 
 * @return void
 */
function apPaymentModeCallback()
{
    $ap_payment_mode = get_option('ap_payment_mode');
    ?>
    <input type="checkbox" name="ap_payment_mode" class="regular-text" value="test_mode" <?php checked('test_mode', $ap_payment_mode); ?> /> Enable Test Mode
    <br /><br /><span>Place the payment gateway in test mode using test credentials..</span>

    <?php
}

/**
 * Api url tempalte
 * 
 * @return void
 */
function apApiUrlCallback()
{
    $ap_api_url = get_option('ap_api_url');
    ?>
    <input type="text" name="ap_api_url" class="regular-text" value="<?php echo isset($ap_api_url) ? esc_attr($ap_api_url) : ''; ?>" />

    <?php
}

/**
 * Access key tempalte
 * 
 * @return void
 */
function apAccessKeyCallback()
{
    $ap_access_key = get_option('ap_access_key');
    ?>
    <input type="password" name="ap_access_key" class="regular-text" value="<?php echo isset($ap_access_key) ? esc_attr($ap_access_key) : ''; ?>" />

    <?php
}

/**
 * Secret key tempalte
 * 
 * @return void
 */
function apSecretKeyCallback()
{
    $ap_secret_key = get_option('ap_secret_key');
    ?>
    <input type="password" name="ap_secret_key" class="regular-text" value="<?php echo isset($ap_secret_key) ? esc_attr($ap_secret_key) : ''; ?>" />

    <?php
}

/**
 * Test api url tempalte
 * 
 * @return void
 */
function apTestApiUrlCallback()
{
    $ap_test_api_url = get_option('ap_test_api_url');
    ?>
    <input type="text" name="ap_test_api_url" class="regular-text" value="<?php echo isset($ap_test_api_url) ? esc_attr($ap_test_api_url) : ''; ?>" />

    <?php
}

/**
 * Test access key tempalte
 * 
 * @return void
 */
function apTestAccessKeyCallback()
{
    $ap_test_access_key = get_option('ap_test_access_key');
    ?>
    <input type="password" name="ap_test_access_key" class="regular-text" value="<?php echo isset($ap_test_access_key) ? esc_attr($ap_test_access_key) : ''; ?>" />

    <?php
}

/**
 * Test secret key tempalte
 * 
 * @return void
 */
function apTestSecretKeyCallback()
{
    $ap_test_secret_key = get_option('ap_test_secret_key');
    ?>
    <input type="password" name="ap_test_secret_key" class="regular-text" value="<?php echo isset($ap_test_secret_key) ? esc_attr($ap_test_secret_key) : ''; ?>" />

    <?php
}

/**
 * Payment summary tempalte
 * 
 * @return void
 */
function apPaymentSummaryCallback()
{
    $ap_payment = get_option('ap_payment');
    ?>
    <input type="checkbox" name="ap_payment" class="regular-text" value="payment" <?php checked('payment', $ap_payment); ?> /> Add payments overview page
    <br /><br /><span>View your Payment Transactions done via Auropay Gateway.</span>

    <?php
}

/**
 * Checkout expiry template
 * 
 * @return void
 */
function apExpiryCallback()
{
    $ap_expiry = get_option('ap_expiry');
    $options   = array(
        '3'  => '3',
        '4'  => '4',
        '5'  => '5',
        '6'  => '6',
        '7'  => '7',
        '8'  => '8',
        '9'  => '9',
        '10' => '10',
    )
    ?>
    <select name="ap_expiry" id="ap_expiry" class="regular-text">
        <?php
        foreach ($options as $key => $value) {
            ?>
            <option value="<?php echo $value ?>" <?php selected($value, $ap_expiry) ?>><?php echo $value ?> Minutes</option>;
            <?php
        }
        ?>
    </select>
    <br /><br /><span>Define Expiry Time for Payment Form. Checkout form will be reloaded for customer if expiry time is reached.</span>

    <?php
}
