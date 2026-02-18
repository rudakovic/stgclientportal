<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class PH_Plugin_Check
{
    protected $messages = [];
    protected $article_id = "";

    public function check()
    {
        $this->check_plugins();

        return  $this->messages;
    }

    public function check_plugins()
    {
        // wordfence
        if (class_exists('wfConfig') && method_exists('wfConfig', 'get')) {
            if (wfConfig::get('loginSec_disableAuthorScan')) {
                $this->messages[] = [
                    'title' => 'WordFence Setting Conflict',
                    'message' => __('You need to uncheck "Prevent discovery of usernames through \'/?author=N\' scans. This doesn\'t allow our REST API to send authoring information with comments.', 'project-huddle'),
                    'article_id' => '',
                    'type' => 'error'
                ];
            }
        }

        if (class_exists('ITSEC_Modules') && method_exists('ITSEC_Modules', 'get_settings')) {
            $settings = ITSEC_Modules::get_settings('wordpress-tweaks');
            if (!in_array($settings['rest_api'], array('enable', 'default-access'))) {
                $this->messages[] = [
                    'title' => 'iThemes Setting Conflict',
                    'message' => __('You need to enable default access to the REST API or SureFeedback\'s roles won\'t be able to access the REST API endpoint.', 'project-huddle'),
                    'article_id' => '',
                    'type' => 'error'
                ];
            }
        }

        // really simple ssl
        $options = get_option('rlrsssl_options', false);
        if ($options && is_array($options)) {
            if (isset($options['javascript_redirect']) && $options['javascript_redirect']) {
                $this->messages[] = [
                    'title' => 'Really Simple SSL Setting Conflict',
                    'message' => __('You must turn off Really Simple SSL\'s javascript redirection feature or SureFeedback won\'t work on connected non-ssl sites.', 'project-huddle'),
                    'article_id' => '',
                    'type' => 'warning'
                ];
            }
        }

        // disable comments
        if (is_plugin_active('disable-comments/disable-comments.php')) {
            $this->messages[] = [
                'title' => 'Comments Disabled',
                'message' => __('You must turn off your Disable Comments plugin or SureFeedback won\'t be able to save any comments! If you use this plugin anyway, then make sure comments are not disabled for SureFeedback\'s post types and via Rest API.', 'project-huddle'),
                'article_id' => '',
                'type' => 'error'
            ];
        }

        // disable comments rb
        if (is_plugin_active('disable-comments-rb/disable-comments-rb.php')) {
            $this->messages[] = [
                'title' => 'Comments Disabled',
                'message' => __('You must turn off your Disable Comments plugin or SureFeedback won\'t be able to save any comments!', 'project-huddle'),
                'article_id' => '',
                'type' => 'error'
            ];
        }

        // roots soil
        global $_wp_theme_features;
        if (isset($_wp_theme_features['soil-disable-rest-api']) && $_wp_theme_features['soil-disable-rest-api']) {
            $this->messages[] = [
                'title' => 'REST API Disabled',
                'message' => __('The Roots soil plugins is disabling the REST API. You need to remove this functionality.', 'project-huddle'),
                'article_id' => '',
                'type' => 'warning'
            ];
        }
        if (isset($_wp_theme_features['soil-relative-urls']) && $_wp_theme_features['soil-relative-urls']) {
            $this->messages[] = [
                'title' => 'Relative URLS detected',
                'message' => __('The Roots soil plugins is changing to relative urls. You need to remove this functionality or SureFeedback will not work on client sites.', 'project-huddle'),
                'article_id' => '',
                'type' => 'warning'
            ];
        }

        // TODO: 
        // 404 redirect to homepage
        // WP Cerber Security 
        // WebARX
        // WP Secure Hide WordPress Plugin

        // All In One WP Security
        if (is_plugin_active('all-in-one-wp-security-and-firewall/wp-security.php')) {
            $this->messages[] = [
                'title' => '"All In One WP Security" plugin detected.',
                'message' => __('This plugin has the ability to disable WordPress XMLRPC functionality. This functionality is need for SureFeedback to work properly. Navigate and check here: WP Dashboard -> WP Security -> Firewall -> Basic Firewall Rules -> Completely Block Access To XMLRPC', 'project-huddle'),
                'article_id' => '',
                'type' => 'warning'
            ];
        }

        // Defender
        if (is_plugin_active('defender-security/wp-defender.php')) {
            $this->messages[] = [
                'title' => '"Defender" plugin detected.',
                'message' => __('This plugin has the ability to disable WordPress XMLRPC functionality. This functionality is need for SureFeedback to work properly. Navigate and check here: WP Dashboard -> Defender -> Recommendations -> Disable-XML-RPC', 'project-huddle'),
                'article_id' => '',
                'type' => 'warning'
            ];
        }

        // iThemes Security
        if (is_plugin_active('better-wp-security/better-wp-security.php')) {
            $this->messages[] = [
                'title' => '"iThemes Security" plugin detected.',
                'message' => __('This plugin has the ability to disable WordPress XMLRPC functionality. This functionality is need for SureFeedback to work properly. Navigate and check here: WP Dashboard -> Security -> Settings -> WordPress Tweaks -> API Access -> XML-RPC', 'project-huddle'),
                'article_id' => '',
                'type' => 'warning'
            ];
        }

        // NinjaFirewall
        if (is_plugin_active('ninjafirewall/ninjafirewall.php')) {
            $this->messages[] = [
                'title' => '"NinjaFirewall" plugin detected.',
                'message' => __('This plugin has the ability to disable WordPress XMLRPC functionality. This functionality is need for SureFeedback to work properly. Navigate and check here: WP Dashboard -> NinjaFirewall -> Firewall Policies -> WordPress XML-RPC API', 'project-huddle'),
                'article_id' => '',
                'type' => 'warning'
            ];
        }

        // WP Cerber Security, Anti-spam & Malware Scan
        if (is_plugin_active('wp-cerber/wp-cerber.php')) {
            $this->messages[] = [
                'title' => '"WP Cerber Security, Anti-spam & Malware Scan" plugin detected.',
                'message' => __('This plugin has the ability to disable WordPress XMLRPC functionality. This functionality is need for SureFeedback to work properly. Navigate and check here: WP Dashboard -> WP Cerber -> Dashboard -> Hardening -> Disable XML-RPC', 'project-huddle'),
                'article_id' => '',
                'type' => 'warning'
            ];
        }

        // Shield Security
        if (is_plugin_active('wp-simple-firewall/icwp-wpsf.php')) {
            $this->messages[] = [
                'title' => '"Shield Security" plugin detected.',
                'message' => __('This plugin has the ability to disable WordPress XMLRPC functionality. This functionality is need for SureFeedback to work properly. Navigate and check here: WP Dashboard -> WP Hardening -> Security Fixers -> Admin & API Security -> Disable XMLRPC', 'project-huddle'),
                'article_id' => '',
                'type' => 'warning'
            ];
        }
    }
}
