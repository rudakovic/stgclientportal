<?php 
/**
 * Add Admin Menu for this plugin
 *
 * @package stripesubtowoo
 */

 namespace NUX\StripeSubToWooSub;

function register_nux_menu_page() {
	add_menu_page( 'Migrate Subs', 'Stripe Migration', 'manage_options', 'nux-migration', __NAMESPACE__ . '\nux_admin_page', 'dashicons-controls-repeat', 40 );
}

function add_nux_options() {
	add_option( 'nux_migration_staging', true);
	register_setting( 'nux_migration_options_group', 'nux_migration_staging' );

	add_option( 'stripe_api_secret_live', null);
	register_setting( 'nux_migration_options_group', 'stripe_api_secret_live', array(
            'type'              => 'string',
            'show_in_rest'      => true,
            //'sanitize_callback' => 'sanitize_text_field',
        ) );
	add_option( 'stripe_api_secret_test', null);
	register_setting( 'nux_migration_options_group', 'stripe_api_secret_test', 'string' );
}

add_action( 'admin_menu', __NAMESPACE__ . '\register_nux_menu_page' );
add_action( 'admin_init', __NAMESPACE__ . '\add_nux_options' );

function nux_admin_page() {
	?>
	<div>
	<h1>Stripe to WooCommerce Subscriptions Migration</h1>
    <p><a href="#settings">Jump to settings</a></p>
    <h2>Steps for Migration</h2>
    <style>
        ul.nux-migration pre {
            display:inline-block;
            text-indent:0;
            margin:0;
        }
        ul.nux-migration {
            padding-left:40px;
        }
        ul.nux-migration li {
            text-indent:-15px;
        }
    </style>
    <p>This plugin is designed to use WP-CLI commands to run the migration in stages between existing Stripe data, and WooCommerce Subscriptions. At each step in the process data is recorded and viewable on individual subs as well as queryable via meta keys.</p>
    <h3>Helpful Utility Commands</h3>
    <ul class="nux-migration">
        <li><strong><pre>$ wp nux ping</pre></strong><br/>This will verify that the wp cli connection is working for the plugin, and will output the line &nbsp;<strong><pre>$ pong</pre></strong></li>
        <li><strong><pre>$ wp nux verify_data_type</pre></strong><br/>This will output information about if Stripe is connected in test mode or live mode.</li>
        <li><strong><pre>$ wp option update stripe_api_secret_live <?php echo esc_html('<key>'); ?> </pre></strong><br/>This will set the live key</li>
        <li><strong><pre>$ wp option update stripe_api_secret_test <?php echo esc_html('<key>'); ?> </pre></strong><br/>This will set the test key</li>
    </ul>

    <h3>Step 1: Import any new Customers</h3>
    <p>Customer's are identified by email address and will only be imported once, ensuring now duplicate accounts are created. This command will output a line item for each row in the provided file detailing which customers were imported and which customers already existed.</p>
    <ul class="nux-migration">
        <li><strong><pre>$ wp nux import_stripe_customers <?php echo esc_html('<file> [--disable-notification[=<true>]]'); ?></pre></strong><br/>
        <strong><pre><?php echo esc_html('<file>'); ?></pre></strong><br/>The CSV file (full url path to file) of data from a Stripe Subscriptions Export. It should include these coloumns: 'Customer ID', 'Customer Email', 'Customer Name', 'Customer Description'</br>
        <strong><pre>--disable-notification</pre></strong> (optional)<br/>Defaults to true. If it is set to false, new customers will recieve emails about their new customer accounts. <strong>NOTE: This does not control the admin notification setting regarding new customer accounts, we highly encourage you to <a href="<?php site_url(); ?>/wp-admin/admin.php?page=wc-settings&tab=email&section=wc_email_new_order">disable this notification</a> while running the command to avoid an influx in admin emails.</strong></br>
        
        </li>
    </ul>

    <h3>Step 2: Create WooCommerce Subscriptions</h3>
    <p>This will create subscriptions for each row in the provided CSV file and link them to a WooCommerce Customer. Any corresponding Stripe data provided will be saved to the subscription for future use by this plugin. WooCommerce Subscriptions are imported with a status of "On Hold" and must be manually activated, or activated via CLI command to begin processing. This command will output a line item for each row in the file to log successful subscription creation.</p>
    <p><strong>NOTE:</strong> It is possible to duplicate subscriptions if this data is processed twice.</p>
    <ul class="nux-migration">
        <li><strong><pre>$ wp nux create_woo_subscriptions <?php echo esc_html('--stripe-subs=<file> --mapped-products=<file> [--mapped-key=<number>] [--disable-notification[=<true>]]'); ?></pre></strong><br/>
        <strong><pre><?php echo esc_html('--stripe-subs=<file>'); ?></pre></strong><br/>The CSV file (full url path to file) of data from a Stripe Subscriptions Export. It should include these coloumns: 'id', 'Customer ID', 'Customer Email', 'Customer Name', 'Customer Description', 'Plan', 'Quantity', 'Created (UTC)', 'Current Period End (UTC)'</br>
        <strong><pre><?php echo esc_html('--mapped-products=<file>'); ?></pre></strong><br/>The CSV file (full url path to file) of data to map existing Stripe Subscription Products to WooCommerce Subscription Product Ids. The Stripe Subscription Products should have a column heading of 'Plan'. The index number of the WooCommerce Product Ids column can be passed with:</br>
        <strong><pre><?php echo esc_html('[--mapped-key=<number>]'); ?></pre></strong> (optional)<br/>Defaults to 0 - or the first column in the file.</br>
        <strong><pre>--disable-notification</pre></strong> (optional)<br/>Defaults to true. If it is set to false, new customers will recieve emails about their new customer accounts. <strong>NOTE: This does not control the admin notification setting regarding new customer accounts, we highly encourage you to <a href="<?php site_url(); ?>/wp-admin/admin.php?page=wc-settings&tab=email&section=wc_email_new_order">disable this notification</a> while running the command to avoid an influx in admin emails.</strong></br>
        </li>
    </ul>

    <h3>Step 3: Link Newly Created WooCommerce Subscriptions to Stripe data if available.</h3>
    <p>This will connect Stripe payment data to the new subscription if it was present at time of creation. This will include auto payment vs send invoice settings, default payment for subscription if available and if not default payment for stripe customer.</p>
    <ul class="nux-migration">
        <li><strong><pre>$ wp nux link_subs_to_stripe_payments</pre></strong><br/>
        As this uses data saved to the WooCommerce subscription meta when the subscription was created no additional arguments are needed.
        </li>
    </ul>
    <h3>Steps 4 & 5: Activate WooCommerce Subscriptions and Disable Stripe Subscription processing.</h3>
    <p>These commands can be run in either order, but care should be taken to run them in close succession to prevent duplicate charges (one from each platform).</p>
    <p><strong>NOTE:</strong> Upon activation the WooCommerce next payment data will validate. If the saved data from import 'Current Period End (UTC)' is a date in the future it will remain when the next charge takes place. If this date is in the past, the next payment date will adjust to become 2 hours from time of activation.<p>
    <ul class="nux-migration">
        <li><strong><pre>$ wp nux activate_imported_subscriptions</pre></strong><br/>
        As this uses data saved to the WooCommerce subscription meta when the subscription was created no additional arguments are needed.
        </li>
        <li><strong><pre>$ wp nux cancel_legacy_stripe_subscription</pre></strong><br/>
        As this uses data saved to the WooCommerce subscription meta when the subscription was created no additional arguments are needed.
        </li>
    </ul>
	<form id="settings" method="post" action="options.php">
	<?php settings_fields( 'nux_migration_options_group' ); ?>
	<h2>Migration Settings</h2>
	<p>We need your Stripe API Keys to connect to existing Stripe data, this is a seperate setting than your Stripe connection with WooCommerce. Please edit those details on the <a href="<?php site_url(); ?>/wp-admin/admin.php?page=wc-settings&tab=checkout&section=stripe&panel=methods">WooCommerce Payment Settings</a> page.<br/>
	<label for="stripe_api_secret_live">Stripe Live API Key</label><br/>
	<?php $stripe_api_live = get_option('stripe_api_secret_live'); //error_log(print_r($stripe_api_live, true)); ?>
	<input type="text" id="stripe_api_secret_live" name="stripe_api_secret_live" value="<?php echo $stripe_api_live; ?>" /> <br/>
	<small>This should be the secret key to live Stripe data.</small></p>
	<p>
	<label for="stripe_api_secret_test">Stripe Test API Key</label><br/>

	<?php $stripe_api_test = get_option('stripe_api_secret_test'); //error_log(print_r($stripe_api_test, true)); ?>
	<input type="text" id="stripe_api_secret_test" name="stripe_api_secret_test" value="<?php echo $stripe_api_test; ?>" /> <br/>
	<small>This should be the secret key to test Stripe data.</small></p>
    <h2>Connection Type</h2>
	<p>Check the box if you'd like to use this plugin with a test connection to Stripe.</p>
	
	<label for="nux_migration_staging">Use Test Data</label>

	<input type="checkbox" id="nux_migration_staging" name="nux_migration_staging" value="1"<?php checked( 1, get_option('nux_migration_staging') ); ?> /> <br/>
	<small>If left unchecked live data will be used.</small>
	<?php submit_button(); ?>
	</form>
	
	</div>
	
<?php
}


