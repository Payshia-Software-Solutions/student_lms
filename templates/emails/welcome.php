<?php

/**
 * Welcome email template
 * Variables required:
 * - $userName (string) - Name of the user
 * - $companyName (string) - Name of the company
 * - $loginUrl (string) - URL for login
 */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?php echo htmlspecialchars($companyName); ?></title>
</head>

<body style="margin: 0; padding: 20px; font-family: Arial, sans-serif; line-height: 1.6; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 5px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <!-- Header with Logo Space -->
        <div style="text-align: center; padding: 20px 0;">
            <h1 style="color: #333; margin-bottom: 10px;">Welcome to <?php echo htmlspecialchars($companyName); ?>!</h1>
        </div>

        <!-- Main Content -->
        <div style="color: #555; font-size: 16px;">
            <p>Dear <?php echo htmlspecialchars($userName); ?>,</p>

            <p>We're excited to welcome you to <?php echo htmlspecialchars($companyName); ?>! Your account has been successfully created, and you're now ready to get started.</p>

            <!-- Login Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo htmlspecialchars($loginUrl); ?>" style="background-color: #007bff; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">Login to Your Account</a>
            </div>

            <p>With your account, you can:</p>
            <ul style="color: #666; margin-bottom: 25px;">
                <li>Access your dashboard</li>
                <li>Manage your profile</li>
                <li>View and track your activities</li>
                <li>Access all available features</li>
            </ul>

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

            <p style="margin-top: 25px;">Best regards,<br>The <?php echo htmlspecialchars($companyName); ?> Team</p>
        </div>

        <!-- Footer -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #999; font-size: 12px; text-align: center;">
            <p>This is an automated email from <?php echo htmlspecialchars($companyName); ?>.</p>
            <p>If you didn't create this account, please ignore this email or contact support.</p>
        </div>
    </div>
</body>

</html>