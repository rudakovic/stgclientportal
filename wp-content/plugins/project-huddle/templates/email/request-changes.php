<?php
global $ph_email_data;
// Extract variables for cleaner template code
$project_type = ucfirst($ph_email_data['project_model_type'] ?? 'Project');
$requested_by       = $ph_email_data['requested_by'];
$message = isset($ph_email_data['message']) && !empty($ph_email_data['message']) 
    ? $ph_email_data['message'] 
    : 'No additional comments provided.';
$project_status     = $ph_email_data['project_approved'] ?? false;
$project_link       = $ph_email_data['project_link'] ?? '';

// Load email header
ph_get_template('email/default-header.php');
?>

<!-- Full Width Email Start -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f0f2f5;">
  <tr>
    <td align="center" style="padding: 0;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff;">
        <tr>
          <td style="padding: 40px 20px; font-family: Arial, sans-serif; font-size: 15px; line-height: 1.7; color: #222;">
            <p style="margin: 0 0 18px;">
              Hello,
            </p>

            <p style="margin: 0 0 18px;">
              A change request has been submitted for your project. Please review the details below and take the necessary action to move the project forward.
            </p>

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #eaeaea; background: #f7fafd; border-radius: 6px; padding: 16px 20px; margin-bottom: 20px;">
              <tr>
                <td><strong>Project Type:</strong> <?php echo esc_html($project_type); ?></td>
              </tr>
              <tr>
                <td><strong>Requested By:</strong> <?php echo esc_html($requested_by); ?></td>
              </tr>
              <tr>
                <td><strong>Additional Comment:</strong> <?php echo esc_html($message); ?></td>
              </tr>
            </table>

            <p style="margin: 0 0 18px;">
              <strong>Project Status:</strong> <?php echo $project_status ? 'Approved' : 'Not Approved'; ?>
            </p>

            <?php if (!$project_status): ?>
              <p style="color: #4253ff; font-weight: bold; margin-bottom: 18px;">
                Please address the requested changes and resubmit for approval.
              </p>
            <?php endif; ?>

            <?php if ($project_link): ?>
              <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo esc_url($project_link); ?>"
                   style="display: inline-block; background-color: #4253ff; color: #ffffff; padding: 12px 28px; font-size: 15px; font-weight: bold; text-decoration: none; border-radius: 4px;">
                  View Project Details
                </a>
              </div>
            <?php endif; ?>

            <div style="margin-top: 30px; background: #e3ecf7; padding: 16px 18px; border-radius: 5px; color: #314050; font-size: 14px;">
              <strong>Reminder:</strong><br>
              Kindly respond to this request promptly to keep the project on track. If you need clarification, feel free to reach out to the requester or your project manager directly.
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<!-- Full Width Email End -->

<?php
// Load email footer
ph_get_template('email/default-footer.php');
?>