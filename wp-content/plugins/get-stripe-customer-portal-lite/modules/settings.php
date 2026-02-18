<?php 


class GetStripeCustomerPortalSettingsLite {
    /* V1.1.0 */
    var $settings_parameters;
    var $settings_prefix;
    var $message;

    function __construct($prefix) {
        $this->settings_prefix = $prefix;
        
        if (isset($_POST[$this->settings_prefix.'save_settings_field'])) {
            if (wp_verify_nonce($_POST[$this->settings_prefix.'save_settings_field'], $this->settings_prefix.'save_settings_action')) {
                $options = array();

                foreach ($_POST as $key => $value) {
                    $options[$key] = $value;
                }

                update_option($this->settings_prefix.'_options', $options);
                $this->message = '<div class="alert alert-success">'.__('Settings saved', $this->settings_prefix).'</div>';
            }
        }
    }
    
    function get_setting($setting_name) {
        $inner_option = get_option($this->settings_prefix.'_options');
        return $inner_option[$setting_name];
    }
    
    function create_menu($parameters) {
        $this->settings_parameters = $parameters;
        add_action('admin_menu', array($this, 'add_menu_item'));
    }
    
    function add_menu_item() {
        $default_array = [
            'type' => '',
            'parent_slug' => '',
            'form_title' => '',
            'is_form' => '',
            'page_title' => '',
            'menu_title' => '',
            'capability' => '',
            'menu_slug' => '',
            'icon' => ''
        ];

        $this->settings_parameters = array_merge($default_array, $this->settings_parameters);

        foreach($this->settings_parameters as $single_option) {
            if ($single_option == '') { continue; }

            if ($single_option['type'] == 'menu') {
                add_menu_page(
                    $single_option['page_title'],
                    $single_option['menu_title'],
                    $single_option['capability'],
                    $this->settings_prefix.$single_option['menu_slug'],
                    array($this, 'show_settings'),
                    $single_option['icon']
                );
            }

            if ($single_option['type'] == 'submenu') {
                add_submenu_page(
                    $single_option['parent_slug'],
                    $single_option['page_title'],
                    $single_option['menu_title'],
                    $single_option['capability'],
                    $this->settings_prefix.$single_option['menu_slug'],
                    array($this, 'show_settings')
                );
            }

            if ($single_option['type'] == 'option') {
                add_options_page(
                    $single_option['page_title'],
                    $single_option['menu_title'],
                    $single_option['capability'],
                    $this->settings_prefix.$single_option['menu_slug'],
                    array($this, 'show_settings')
                );
            }
        }
    }
    
    function show_settings(){
        // hide output if its parent menu
        if (count($this->settings_parameters[0]['parameters']) == 0) { return false; }
        
?>
    <div class="wrap">
        <h2><?php echo $this->settings_parameters[0]['form_title']; ?></h2>
        <hr/>
<?php
        echo $this->message;
?>
    <?php if ($this->settings_parameters[0]['is_form']): ?>
        <form class="form-horizontal" method="post" action="">
    <?php endif; ?>

<?php 
        wp_nonce_field($this->settings_prefix.'save_settings_action', $this->settings_prefix.'save_settings_field' );  
        $config = get_option($this->settings_prefix.'_options'); 
         
?>  
            <fieldset>
<?php 
            foreach($this->settings_parameters as $single_page) {
                if ($single_page == '') { continue; }

                foreach($single_page['parameters'] as $key=>$value) {
                    $interface_element_value =  '';
                    if (isset($value['name'])) {
                        if (isset($config[$value['name']])) {
                            $interface_element_value =  $config[$value['name']];
                        }
                    }
                    $interface_element = new GetStripeCustomerPortalFormElementsLite($value['type'], $value, $interface_element_value);
                    echo $interface_element->get_code();     
                }
            }
?>
            </fieldset>  
        
    <?php if ($this->settings_parameters[0]['is_form']): ?>
        </form>
    <?php endif; ?>

    </div>
<?php
    }
}

add_action('init',  function() {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if (defined('DOING_AJAX') && DOING_AJAX) return;

    $locale = '';

    $config_big =
        array(
            array(
                'type' => 'option',
                //'parent_slug' => 'edit.php?post_type=post',
                'form_title' => __('Get Stripe Customer Portal Lite: Settings', $locale),
                'is_form' => true,
                'page_title' => __('Get Stripe Customer Portal Lite: Settings', $locale),
                'menu_title' => __('Stripe Portal Settings', $locale),
                'capability' => 'edit_published_posts',
                'menu_slug' => '_lite_main_settings',
                'parameters' => array(
                    array(
                        'type' => 'text',
                        'title' => __('Test secret key', $locale),
                        'description' => __('Stripe\'s secret key for test mode.', $locale),
                        'name' => 'test_sk',
                        'value' => '',
                        'style' => '',
                        'default' => '',
                        'id' => 'test_sk',
                        'class' => ' long-field'
                    ),

                    array(
                        'type' => 'text',
                        'title' => __('Live secret key', $locale),
                        'description' => __('Stripe\'s secret key for live mode.', $locale),
                        'name' => 'live_sk',
                        'value' => '',
                        'style' => '',
                        'default' => '',
                        'id' => 'live_sk',
                        'class' => ' long-field'
                    ),

                    array(
                        'type' => 'checkbox',
                        'title' => __('LIVE mode enabled', $locale),
                        'description' => __('If not checked, the plugin runs in test mode.', $locale),
                        'name' => 'live_mode_on',
                        'style' => ' ',
                        'id' => 'live_mode_on',
                        'class' => ''
                    ),

                    array(
                        'type' => 'checkbox',
                        'title' => __('Show error details', $locale),
                        'description' => __('This can potentially expose sensitive information. Use only for debugging if something is not working.', $locale),
                        'name' => 'show_error_details',
                        'style' => ' ',
                        'id' => 'show_error_details',
                        'class' => ''
                    ),

                    array(
                        'type' => 'checkbox',
                        'title' => __('Clear email on success', $locale),
                        'description' => __('If this option is checked then the Email input will be cleared after successful request.', $locale),
                        'name' => 'clear_email_on_success',
                        'style' => ' ',
                        'id' => 'clear_email_on_success',
                        'class' => ''
                    ),

                    array(
                        'type' => 'text',
                        'title' => __('Return URL', $locale),
                        'description' => __('Required by Stripe', $locale),
                        'name' => 'return_url',
                        'value' => '',
                        'style' => '',
                        'default' => get_site_url(),
                        'id' => 'return_url',
                        'class' => ' long-field'
                    ),

                    array(
                        'type' => 'text_out',
                        'description' => __('
                            <br/>
                            <p>Use shortcode [get_stripe_customer_portal] anywhere where shortcodes are supported, but do not use more than one shortcode on a single page.</p>
                            <p>On successful retrieval your clients would receive an email with the following text: "Link to customer portal: {link}".</p>
                            <p>If you would like to change labels, email subject and body, switch to <a href="https://netfoxsoftware.com/asp-products/get-stripe-customer-portal/">Pro</a> version.</p>
                            <br/>
                        ', $locale),
                        'style' => '',
                        'class' => ''
                    ),

                    array(
                        'type' => 'save',
                        'title' => __('Save', $locale),
                    ),
               )
           )
       );

    global $settings;

    $settings = new GetStripeCustomerPortalSettingsLite('gscp');
    $settings->create_menu($config_big);
});

?>
