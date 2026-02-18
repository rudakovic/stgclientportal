<?php

class GetStripeCustomerPortalStylesLite {
    protected $plugin_prefix;
    protected $plugin_version;
    protected $files_list;

    public function __construct($prefix, $parameters) {
        $this->files_list = $parameters;
        $this->plugin_prefix = $prefix;
        $this->plugin_version = '1.0';

        add_action('wp_print_scripts', array($this, 'add_script_fn'));
    }

    public function add_script_fn() {
        foreach ($this->files_list as $key => $value) {
            if ($key == 'common') {
                foreach ($value as $single_line) {
                    $this->process_enq_line($single_line);
                }
            }

            if ($key == 'admin' && is_admin()) {
                foreach ($value as $single_line) {
                    $this->process_enq_line($single_line);
                }
            }

            if ($key == 'front' && !is_admin()) {
                foreach($value as $single_line) {
                    $this->process_enq_line($single_line);
                }
            }
        }
    }

    public function process_enq_line($line) {
        $default_array = [
            'type' => '',
            'url' => '',
            'enq' => '',
            'localization' => '',
        ];

        $line = array_merge($default_array, $line);

        $custom_id  = rand(1000, 9999).basename($line['url']);

        if ($line['type'] == 'style') {
            wp_enqueue_style($this->plugin_prefix.$custom_id, $line['url']);
        }

        if ($line['type'] == 'script') {

            $rand_prefix = rand(1000, 9999);

            if (isset($line['id'])) {
                $script_prefix = $line['id'];
            } else {
                $script_prefix = $this->plugin_prefix.$custom_id.$rand_prefix;
            }
            
            wp_register_script($script_prefix, $line['url'], $line['enq']);

            if ($line['localization']) {
                wp_localize_script(
                    $script_prefix,
                    $this->plugin_prefix . '_local_data',
                    $line['localization']
                );
            }

            wp_localize_script(
                $script_prefix,
                'wp_ajax',
                array( 
                    'ajaxurl'   => admin_url('admin-ajax.php'),
                    'ajaxnonce' => wp_create_nonce('ajax_post_validation')
                )
            );

            wp_enqueue_script($script_prefix);
        }
    }
}
 
add_action('init', function() {
    $scripts_list = array(
        'common' => array(),
        'admin' => array(
            array('type' => 'style', 'url' => plugins_url('/css/admin.css', __FILE__)),
        ),
        'front' => array(
            array('type' => 'style', 'url' => plugins_url('/css/front.css', __FILE__)),
            array('type' => 'script', 'url' => plugins_url('/js/ajax.js', __FILE__)),
        ),
    );

    $insert_script = new GetStripeCustomerPortalStylesLite('gscp' , $scripts_list);
}, 1000);

?>
