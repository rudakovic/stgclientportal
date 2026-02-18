<?php

function gscp_lite_html_form_code() {
    $locale = '';
    $emailLabel = __('Email:', $locale);
    $buttonText = __('Send', $locale);
    $settings = get_option('gscp_options');
    $clear_email_on_success = isset($settings['clear_email_on_success']) ? $settings['clear_email_on_success'] : ''; 

    if ($clear_email_on_success=='on') {
        $gscp_email = '';
    } else {
        $gscp_email = isset( $_POST["gscp-email"] ) ? esc_attr( $_POST["gscp-email"] ) : '';
    }

    echo '<form class="gscp-form" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
    echo '<div>';
    echo '<label for="gscp-email">' . stripslashes($emailLabel) . '</label><br/>';
    echo '<input id="gscp-email" type="email" name="gscp-email" value="' .$gscp_email. '" />';
    echo '</div>';
    echo '<div><button type="submit" name="gscp-submitted">' . $buttonText . '</button></div>';
    echo '</form>';
}

function gscp_lite_deliver_mail() {

    // if the submit button is clicked, send the email
    if ( isset( $_POST['gscp-submitted'] ) ) {
        $settings = get_option('gscp_options');
        $locale = '';
        $isSent = false;

        $emailSubject = __('Link to customer portal', $locale);
        $emailMessage = __('Your customer portal link: {link}', $locale);
        $succeessMessage = __('The link has been sent to {client_email}. Don\'t forget to check your SPAM folder.', $locale);
        $errorMessage = __('We couldn\'t retrieve your customer portal link. Please send an email to {admin_email} and we will try to help.', $locale);

        $stripeKey = (isset($settings['live_mode_on']) && $settings['live_mode_on'] == 'on') ? $settings['live_sk'] : $settings['test_sk'];

        // sanitize form values
        $email   = sanitize_email( $_POST["gscp-email"] );

        // get the blog administrator's email address
        $admin_email = get_option( 'admin_email' );

        $htmlErrorMessage = stripslashes(
            str_replace(
                '{admin_email}',
                '<a href="mailto:' . $admin_email . '">'. $admin_email .'</a>', $errorMessage
            )
        );

        $headers = "From: no-reply <$admin_email>" . "\r\n";

        try {
            if (class_exists('\Stripe\StripeClient')) {
                $stripe = new \Stripe\StripeClient(
                    $stripeKey ? $stripeKey : ''
                );

                $customers = $stripe->customers->all([
                    'email' => $email,
                ]);

                if (count($customers->data) != 1) {
                    echo '<div class="gscp-error">';
                    echo '<p>' . $htmlErrorMessage . '</p>';
                    if (isset($settings['show_error_details']) && $settings['show_error_details'] == 'on') {
                        echo '<p class="gscp-error-hint">Error details: customer not found.</p>';
                    }
                    echo '</div>';
                } else {
                    $stripe_session = $stripe->billingPortal->sessions->create([
                        'customer' => $customers->data[0]->id,
                        'return_url' => $settings['return_url'] ? $settings['return_url'] : get_site_url(),
                    ]);

                    $parsedEmailMessage = stripslashes(
                        str_replace(
                            '{link}',
                            $stripe_session->url,
                            $emailMessage
                        )
                    );

                    $parsedSucceessMessage = stripslashes(
                        str_replace(
                            '{client_email}',
                            $email,
                            $succeessMessage
                        )
                    );

                    // If email has been process for sending, display a success message
                    if ( wp_mail( $email, $emailSubject, $parsedEmailMessage, $headers ) ) {
                        echo '<div class="gscp-success">';
                        echo '<p>' . $parsedSucceessMessage . '</p>';
                        echo '</div>';
                        $isSent = true;
                    } else {
                        echo '<div class="gscp-error">';
                        echo '<p>' . $htmlErrorMessage . '</p>';
                        if (isset($settings['show_error_details']) && $settings['show_error_details'] == 'on') {
                            echo '<p class="gscp-error-hint">Error details: email can not be sent.</p>';
                        }
                        echo '</div>';
                    }
                }
            } else {
                echo '<div class="gscp-error">';
                echo '<p>' . $htmlErrorMessage . '</p>';
                if (isset($settings['show_error_details']) && $settings['show_error_details'] == 'on') {
                    echo '<p class="gscp-error-hint">Error details: incompatible with some other Stripe plugin.</p>';
                }
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="gscp-error">';
            echo '<p>' . $htmlErrorMessage . '</p>';
            if (isset($settings['show_error_details']) && $settings['show_error_details'] == 'on') {
                echo '<p class="gscp-error-hint">Error details: ' . $e->getMessage() . '</p>';
            }
            echo '</div>';
        }

        $clear_email_on_success = ($settings['clear_email_on_success']) ? $settings['clear_email_on_success'] : ''; 
        if ($isSent && $clear_email_on_success == 'on') {
            echo '<script>document.getElementById("gscp-email").value="";</script>';
        }
        exit();
    }
}

function gscp_lite_shortcode() {
    ob_start();
    gscp_lite_deliver_mail();
    gscp_lite_html_form_code();
    return ob_get_clean();
}

add_shortcode('get_stripe_customer_portal', 'gscp_lite_shortcode');
add_action('wp_ajax_gscp-request', 'gscp_lite_deliver_mail');
add_action('wp_ajax_nopriv_gscp-request', 'gscp_lite_deliver_mail');

?>
