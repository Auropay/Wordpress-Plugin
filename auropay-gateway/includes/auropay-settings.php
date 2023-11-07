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
if (!function_exists('arp_settings')) {
    function arp_settings()
    {
        add_menu_page(
            __('Auropay Settingss', AP_SETTING_PAGE),
            __('Auropay Settings', AP_SETTING_PAGE),
            'manage_options',
            'auropay-settings',
            'arp_settings_callback',
            ARP_PLUGIN_URL . '/assets/images/icons/auropay.png',
            null
        );
    }
}

add_action('admin_menu', 'arp_settings');

/**
 * Settings Template Page
 * 
 * @return void
 */
if (!function_exists('arp_settings_callback')) {
    function arp_settings_callback()
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
}

/**
 * Settings initiate
 * 
 * @return void
 */
if (!function_exists('arp_settings_init')) {
    function arp_settings_init()
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
            'arp_title_callback',
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
            'arp_description_callback',
            AP_SETTING_PAGE,
            AP_SETTING_SECTION
        );

        // Add button text fields
        add_settings_field(
            'ap_button_text',
            __('Button Text', 'auropay-gateway'),
            'arp_button_text_callback',
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
            'arp_payment_mode_callback',
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
            'arp_api_url_callback',
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
            'arp_access_key_callback',
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
            'arp_secret_key_callback',
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
            'arp_test_api_url_callback',
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
            'arp_test_access_key_callback',
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
            'arp_test_secret_key_callback',
            AP_SETTING_PAGE,
            AP_SETTING_SECTION
        );

        // Register Log field
        register_setting(
            AP_SETTING_PAGE,
            'ap_logging',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add Log field
        add_settings_field(
            'ap_logging',
            __('Logging', 'auropay-gateway'),
            'arp_logging_callback',
            AP_SETTING_PAGE,
            AP_SETTING_SECTION
        );

        // Register payment summary field
        register_setting(
            AP_SETTING_PAGE,
            'ap_payment',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add payment summary field
        add_settings_field(
            'ap_payment',
            __('Payment Summary', 'auropay-gateway'),
            'arp_payment_summary_callback',
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
            'arp_expiry_callback',
            AP_SETTING_PAGE,
            AP_SETTING_SECTION
        );
    }
}
add_action('admin_init', 'arp_settings_init');

/**
 * Title tempalte
 * 
 * @return void
 */
if (!function_exists('arp_title_callback')) {
    function arp_title_callback()
    {
        $ap_title = get_option('ap_title');
    ?>
        <input type="text" name="ap_title" class="regular-text" value="<?php echo isset($ap_title) ? esc_attr($ap_title) : ''; ?>" />
    <?php
    }
}

/**
 * Description tempalte
 * 
 * @return void
 */
if (!function_exists('arp_description_callback')) {
    function arp_description_callback()
    {
        $ap_description = get_option('ap_description');
    ?>
        <textarea name="ap_description" class="large-text" rows="2"><?php echo isset($ap_description) ? esc_textarea($ap_description) : ''; ?></textarea>
    <?php
    }
}

/**
 * Button text tempalte
 * 
 * @return void
 */
if (!function_exists('arp_button_text_callback')) {
    function arp_button_text_callback()
    {
        $ap_button_text = get_option('ap_button_text');
    ?>
        <input type="text" name="ap_button_text" class="regular-text" value="<?php echo isset($ap_button_text) ? esc_attr($ap_button_text) : ''; ?>" />
        <br /><br /><span>Example: Buy Now, Pay Now etc..</span>
    <?php
    }
}

/**
 * Payment mode tempalte
 * 
 * @return void
 */
if (!function_exists('arp_payment_mode_callback')) {
    function arp_payment_mode_callback()
    {
        $ap_payment_mode = get_option('ap_payment_mode');
    ?>
        <input type="checkbox" name="ap_payment_mode" class="regular-text" value="test_mode" <?php checked('test_mode', $ap_payment_mode); ?> /> Enable Test Mode
        <br /><br /><span>Place the payment gateway in test mode using test credentials..</span>

    <?php
    }
}

/**
 * Api url tempalte
 * 
 * @return void
 */
if (!function_exists('arp_api_url_callback')) {
    function arp_api_url_callback()
    {
        $ap_api_url = get_option('ap_api_url');
    ?>
        <input type="text" name="ap_api_url" class="regular-text" value="<?php echo isset($ap_api_url) ? esc_attr($ap_api_url) : ''; ?>" />

    <?php
    }
}

/**
 * Access key tempalte
 * 
 * @return void
 */
if (!function_exists('arp_access_key_callback')) {
    function arp_access_key_callback()
    {
        $ap_access_key = get_option('ap_access_key');
    ?>
        <input type="password" name="ap_access_key" class="regular-text" value="<?php echo isset($ap_access_key) ? esc_attr($ap_access_key) : ''; ?>" />

    <?php
    }
}

/**
 * Secret key tempalte
 * 
 * @return void
 */
if (!function_exists('arp_secret_key_callback')) {
    function arp_secret_key_callback()
    {
        $ap_secret_key = get_option('ap_secret_key');
    ?>
        <input type="password" name="ap_secret_key" class="regular-text" value="<?php echo isset($ap_secret_key) ? esc_attr($ap_secret_key) : ''; ?>" />

    <?php
    }
}

/**
 * Test api url tempalte
 * 
 * @return void
 */
if (!function_exists('arp_test_api_url_callback')) {
    function arp_test_api_url_callback()
    {
        $ap_test_api_url = get_option('ap_test_api_url');
    ?>
        <input type="text" name="ap_test_api_url" class="regular-text" value="<?php echo isset($ap_test_api_url) ? esc_attr($ap_test_api_url) : ''; ?>" />

    <?php
    }
}

/**
 * Test access key tempalte
 * 
 * @return void
 */
if (!function_exists('arp_test_access_key_callback')) {
    function arp_test_access_key_callback()
    {
        $ap_test_access_key = get_option('ap_test_access_key');
    ?>
        <input type="password" name="ap_test_access_key" class="regular-text" value="<?php echo isset($ap_test_access_key) ? esc_attr($ap_test_access_key) : ''; ?>" />

    <?php
    }
}

/**
 * Test secret key tempalte
 * 
 * @return void
 */
if (!function_exists('arp_test_secret_key_callback')) {
    function arp_test_secret_key_callback()
    {
        $ap_test_secret_key = get_option('ap_test_secret_key');
    ?>
        <input type="password" name="ap_test_secret_key" class="regular-text" value="<?php echo isset($ap_test_secret_key) ? esc_attr($ap_test_secret_key) : ''; ?>" />

    <?php
    }
}

/**
 * Logging
 * 
 * @return void
 */
if (!function_exists('arp_logging_callback')) {
    function arp_logging_callback()
    {
        $ap_logging = get_option('ap_logging');
    ?>
        <input type="checkbox" name="ap_logging" class="regular-text" value="logging" <?php checked('logging', $ap_logging); ?> /> Log debug messages
        <br /><br /><span>Save debug messages into log.</span>

    <?php
    }
}

/**
 * Payment summary tempalte
 * 
 * @return void
 */
if (!function_exists('arp_payment_summary_callback')) {
    function arp_payment_summary_callback()
    {
        $ap_payment = get_option('ap_payment');
    ?>
        <input type="checkbox" name="ap_payment" class="regular-text" value="payment" <?php checked('payment', $ap_payment); ?> /> Add payments overview page
        <br /><br /><span>View your Payment Transactions done via Auropay Gateway.</span>

    <?php
    }
}

/**
 * Checkout expiry template
 * 
 * @return void
 */
if (!function_exists('arp_expiry_callback')) {
    function arp_expiry_callback()
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
        <br /><br /><span>Define Expiry Time for Payment Form. Checkout form will be reloaded for customer if expiry time is
            reached.</span>

<?php
    }
}
