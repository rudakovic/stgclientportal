<?php
namespace PH\Listeners\Mail;

use PH\Support\Mail\ImmediateMail;
use PH\Controllers\Mail\Mailers\Mailer;

if (!defined('ABSPATH')) exit;

class SendRequestChangeMail extends ImmediateMail
{
    public function when()
    {
        return true;
    }

    public function handle()
    {
        // Get all arguments passed to the function
        $args = func_get_args();
        
        try {
            // Ensure we have at least the required arguments
            if (count($args) < 4) {
                throw new \Exception("Insufficient arguments provided");
            }

            $email = $args[0];
            $subject = $args[1];
            $message = $args[2];
            $requested_by = $args[3] ?? 'Unknown User';
            $project_approved = $args[4] ?? false;
            $project_link = $args[5] ?? '';
            $project_model_type = $args[6] ?? '';

            $template_path = ph_locate_template('email/request-changes.php');
            
            if (!file_exists($template_path)) {
                throw new \Exception("Email template not found: {$template_path}");
            }

            // Create mailer instance
            $mailer = new Mailer('request_change');
            
            // Set the basic properties
            $mailer->template($template_path)
                   ->to($email)
                   ->subject(
                       apply_filters(
                           'ph_request_change_email_subject',
                           sanitize_text_field($subject)
                       )
                   );

            global $ph_email_data;
            $ph_email_data = [
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'requested_by' => $requested_by,
                'project_approved' => $project_approved,
                'project_link' => $project_link,
                'project_model_type' => $project_model_type,
            ];

            // Send the email
            $mailer->send();

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}